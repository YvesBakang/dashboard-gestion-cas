<?php
include 'config.php';
session_start();

function formatDateDMY($dateStr) {
    if (empty($dateStr)) return $dateStr;
    $ts = strtotime($dateStr);
    return $ts ? date('d/m/Y', $ts) : $dateStr;
}

function cleanPhoneNumber($raw) {
    return preg_replace('/[^0-9]/', '', (string)$raw);
}

function sendWhatsAppViaGateway($gatewayUrl, $recipientDigits, $messageText) {
    $ch = curl_init();
    if ($ch === false) {
        throw new Exception('cURL non disponible sur ce serveur PHP.');
    }

    $payload = json_encode([
        'phoneNumber' => $recipientDigits,
        'message' => $messageText
    ], JSON_UNESCAPED_UNICODE);

    curl_setopt_array($ch, [
        CURLOPT_URL => rtrim($gatewayUrl, '/') . '/send',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 20,
    ]);

    $resp = curl_exec($ch);
    $errNo = curl_errno($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        throw new Exception("Erreur cURL ($errNo): $err");
    }

    $data = json_decode($resp, true);
    if (!is_array($data) || empty($data['success'])) {
        $reason = $data['error'] ?? $resp;
        throw new Exception(is_string($reason) ? $reason : 'Erreur inconnue');
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage strict des entrées
    $types_cas  = trim($_POST['types_cas'] ?? '');
    $nouveau_t  = trim($_POST['nouveau_type'] ?? '');
    $types_cas  = !empty($nouveau_t) ? $nouveau_t : $types_cas;

    $region      = trim($_POST['region'] ?? '');
    $id_centre   = (int)($_POST['centre'] ?? 0);
    $date_cas    = trim($_POST['date_cas'] ?? '');
    $date_du_cas = trim($_POST['date_du_cas'] ?? '');
    $immat       = strtoupper(str_replace(' ', '', trim($_POST['immatriculation'] ?? '')));

    // Option WhatsApp (facultatif)
    $send_whatsapp = isset($_POST['send_whatsapp']) && $_POST['send_whatsapp'] === '1';
    $whatsapp_recipients = $_POST['whatsapp_recipients'] ?? [];
    if (!is_array($whatsapp_recipients)) {
        $whatsapp_recipients = [];
    }

    // Validation
    if (empty($types_cas) || $id_centre <= 0 || empty($date_du_cas) || empty($immat)) {
        $_SESSION['old'] = $_POST;
        header("Location: form-cas.php?error=missing");
        exit;
    }

    // ✅ Insertion du nouveau type dans types_cas
    if (!empty($nouveau_t)) {
        $check_type = $conn->prepare("SELECT id FROM types_cas WHERE type_name = ?");
        $check_type->bind_param("s", $nouveau_t);
        $check_type->execute();
        if ($check_type->get_result()->num_rows === 0) {
            $ins_t = $conn->prepare("INSERT INTO types_cas (type_name) VALUES (?)");
            $ins_t->bind_param("s", $nouveau_t);
            $ins_t->execute();
            $ins_t->close();
        }
        $check_type->close();
    }

    // Gestion PDF
    $pdf_names = [];
    if (isset($_FILES['pdf']) && !empty($_FILES['pdf']['name'][0])) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        foreach ($_FILES['pdf']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['pdf']['error'][$key] === UPLOAD_ERR_OK) {
                $fname = time() . '_' . $key . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['pdf']['name'][$key]);
                if (move_uploaded_file($tmp_name, 'uploads/' . $fname)) $pdf_names[] = $fname;
            }
        }
    }
    $pdf_string = implode(',', $pdf_names);
    $pdf_count = count($pdf_names);

    // Recherche avec nettoyage SQL
    // $isNewCase sert à décider si on envoie le message WhatsApp
    $isNewCase = false;
    $wa_trigger = false; // vrai si un nouveau véhicule (immatriculation) est réellement ajouté
    $immats_added_for_message = '';
    $stmt = $conn->prepare("SELECT id, immatriculation, pdf FROM tables_cas 
                            WHERE TRIM(date_du_cas) = TRIM(?) 
                            AND id_centre = ? 
                            AND TRIM(types_cas) = TRIM(?)");
    $stmt->bind_param("sis", $date_du_cas, $id_centre, $types_cas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cas existant : ajouter l'immatriculation
        $isNewCase = false;
        $existing = $result->fetch_assoc();
        // Normalisation simple pour détecter correctement un doublon
        $immats_array = array_map(function ($x) {
            return strtoupper(str_replace(' ', '', trim($x)));
        }, explode(',', $existing['immatriculation']));
        
        if (!in_array($immat, $immats_array)) {
            $new_immat_list = $existing['immatriculation'] . ',' . $immat;
            
            $final_pdfs = $existing['pdf'];
            if (!empty($pdf_string)) {
                $final_pdfs = !empty($existing['pdf']) ? $existing['pdf'] . ',' . $pdf_string : $pdf_string;
            }

            $upd = $conn->prepare("UPDATE tables_cas SET immatriculation = ?, pdf = ? WHERE id = ?");
            $upd->bind_param("ssi", $new_immat_list, $final_pdfs, $existing['id']);
            $upd->execute();
            $upd->close();

            $wa_trigger = true;
            $immats_added_for_message = $immat;
        }
    } else {
        // Nouveau cas : insertion
        $isNewCase = true;
        $wa_trigger = true;
        $immats_added_for_message = $immat;
        $ins = $conn->prepare("INSERT INTO tables_cas (types_cas, region, date_cas, date_du_cas, immatriculation, id_centre, pdf) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssis", $types_cas, $region, $date_cas, $date_du_cas, $immat, $id_centre, $pdf_string);
        $ins->execute();
        $ins->close();
    }

    // WhatsApp: envoyer le message seulement pour un nouveau cas
    $wa_sent = false;
    $wa_error = '';
    if ($send_whatsapp && $wa_trigger) {
        if (empty($whatsapp_recipients)) {
            $wa_error = "Aucun numéro WhatsApp sélectionné.";
        } else {
            $nom_centre = '';
            $stmtCentre = $conn->prepare("SELECT nom_centre FROM centres WHERE id = ?");
            $stmtCentre->bind_param('i', $id_centre);
            $stmtCentre->execute();
            $centreRes = $stmtCentre->get_result();
            if ($centreRow = $centreRes->fetch_assoc()) {
                $nom_centre = $centreRow['nom_centre'] ?? '';
            }
            $stmtCentre->close();

            $wa_gateway_url = $WHATSAPP_GATEWAY_URL ?? 'http://127.0.0.1:3001';
            $messageText =
                "NOUVEAU CAS ENREGISTRE\n\n" .
                "Type de cas : {$types_cas}\n" .
                "Centre : {$nom_centre}\n" .
                "Region : {$region}\n" .
                "Date enregistrement : " . formatDateDMY($date_cas) . "\n" .
                "Date du cas : " . formatDateDMY($date_du_cas) . "\n" .
                "Immatriculation(s) : {$immats_added_for_message}\n" .
                "PDF : {$pdf_count} fichier(s) joint(s)";

            $wa_errors = [];
            foreach ($whatsapp_recipients as $recipientRaw) {
                $recipientDigits = cleanPhoneNumber($recipientRaw);
                if (empty($recipientDigits)) {
                    $wa_errors[] = "Numéro invalide : {$recipientRaw}";
                    continue;
                }

                try {
                    sendWhatsAppViaGateway($wa_gateway_url, $recipientDigits, $messageText);
                    $wa_sent = true;
                } catch (Exception $e) {
                    $wa_errors[] = $e->getMessage();
                }
            }

            if (!$wa_sent) {
                $wa_error = !empty($wa_errors) ? implode(' | ', $wa_errors) : "Erreur d'envoi WhatsApp";
            }
        }
    }

    $stmt->close();
    unset($_SESSION['old']);

    $query = "success=1";
    if ($send_whatsapp) {
        if ($wa_sent) {
            $query .= "&wa_sent=1";
        } else {
            $query .= "&wa_error=1";
            if (!empty($wa_error)) {
                $query .= "&wa_error_msg=" . urlencode($wa_error);
            }
        }
    }

    header("Location: form-cas.php?$query");
    exit;
}
?>