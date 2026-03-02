<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

session_start();

function cleanDate($valeur) {
    if (empty($valeur) || $valeur === 'Decembre 2023') return null;
    if (strpos($valeur, ',') !== false) {
        $parts = explode(',', $valeur);
        $valeur = trim($parts[0]);
    }
    if (is_numeric($valeur)) {
        return Date::excelToDateTimeObject($valeur)->format('Y-m-d');
    }
    $valeurNettoyee = str_replace('/', '-', $valeur);
    $timestamp = strtotime($valeurNettoyee);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    header("Location: form-cas.php?error=1");
    exit;
}

$inserted = 0;
$ignored  = 0;

try {
    $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    foreach ($rows as $i => $row) {
        if ($i === 0) continue; 

        $type_cas   = trim($row[0] ?? ''); 
        $nom_centre = trim($row[1] ?? ''); 
        $immat_cell = strtoupper(str_replace(' ', '', trim($row[4] ?? ''))); 

        if (empty($type_cas) || empty($nom_centre)) continue;

        $date_cas = cleanDate($row[2] ?? '');
        $date_du_cas = cleanDate($row[3] ?? '');

        if (empty($date_cas)) $date_cas = $date_du_cas;
        if (empty($date_du_cas)) $date_du_cas = date('Y-m-d'); 

        // Gestion Type de Cas
        $stmt_type = $conn->prepare("SELECT id FROM types_cas WHERE TRIM(type_name) = TRIM(?)");
        $stmt_type->bind_param("s", $type_cas);
        $stmt_type->execute();
        if ($stmt_type->get_result()->num_rows === 0) {
            $ins_type = $conn->prepare("INSERT INTO types_cas (type_name) VALUES (?)");
            $ins_type->bind_param("s", $type_cas);
            $ins_type->execute();
        }
        $stmt_type->close();

        // Gestion Centre
        $stmt = $conn->prepare("SELECT id, region FROM centres WHERE TRIM(nom_centre) = TRIM(?)");
        $stmt->bind_param("s", $nom_centre);
        $stmt->execute();
        $centre = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$centre) {
            $reg_defaut = "NON DEFINIE";
            $insC = $conn->prepare("INSERT INTO centres (nom_centre, region) VALUES (?, ?)");
            $insC->bind_param("ss", $nom_centre, $reg_defaut);
            $insC->execute();
            $id_centre = $insC->insert_id;
            $region = $reg_defaut;
        } else {
            $id_centre = $centre['id'];
            $region = $centre['region'];
        }

        // --- LOGIQUE DE REGROUPEMENT ---
        $immats_array = array_unique(explode(',', $immat_cell));

        foreach ($immats_array as $immat) {
            $immat = trim($immat);
            if (empty($immat)) continue;

            $chk = $conn->prepare("SELECT id, immatriculation FROM tables_cas WHERE TRIM(date_du_cas) = TRIM(?) AND id_centre = ? AND TRIM(types_cas) = TRIM(?)");
            $chk->bind_param("sis", $date_du_cas, $id_centre, $type_cas);
            $chk->execute();
            $res = $chk->get_result();

            if ($res->num_rows > 0) {
                $row_exist = $res->fetch_assoc();
                $current_immats = explode(',', $row_exist['immatriculation']);

                if (!in_array($immat, $current_immats)) {
                    $new_list = $row_exist['immatriculation'] . ',' . $immat;
                    $upd = $conn->prepare("UPDATE tables_cas SET immatriculation = ? WHERE id = ?");
                    $upd->bind_param("si", $new_list, $row_exist['id']);
                    $upd->execute();
                    $inserted++;
                } else {
                    $ignored++;
                }
            } else {
                $ins = $conn->prepare("INSERT INTO tables_cas (types_cas, region, date_cas, date_du_cas, immatriculation, id_centre) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->bind_param("sssssi", $type_cas, $region, $date_cas, $date_du_cas, $immat, $id_centre);
                if ($ins->execute()) $inserted++;
            }
            $chk->close();
        }
    }
    header("Location: form-cas.php?inserted=$inserted&ignored=$ignored");
    exit;
} catch (Exception $e) {
    header("Location: form-cas.php?error=" . urlencode($e->getMessage()));
    exit;
}