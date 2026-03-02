<?php
include 'config.php';
session_start();

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

    // Recherche avec nettoyage SQL
    $stmt = $conn->prepare("SELECT id, immatriculation, pdf FROM tables_cas 
                            WHERE TRIM(date_du_cas) = TRIM(?) 
                            AND id_centre = ? 
                            AND TRIM(types_cas) = TRIM(?)");
    $stmt->bind_param("sis", $date_du_cas, $id_centre, $types_cas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cas existant : ajouter l'immatriculation
        $existing = $result->fetch_assoc();
        $immats_array = explode(',', $existing['immatriculation']);
        
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
        }
    } else {
        // Nouveau cas : insertion
        $ins = $conn->prepare("INSERT INTO tables_cas (types_cas, region, date_cas, date_du_cas, immatriculation, id_centre, pdf) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssis", $types_cas, $region, $date_cas, $date_du_cas, $immat, $id_centre, $pdf_string);
        $ins->execute();
        $ins->close();
    }

    $stmt->close();
    unset($_SESSION['old']);
    header("Location: form-cas.php?success=1");
    exit;
}
?>