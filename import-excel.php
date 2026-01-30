<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

session_start(); // nécessaire pour envoyer le rapport d'import

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_report'] = [
        'inserted' => 0,
        'ignored' => 0,
        'details' => ["Aucun fichier sélectionné ou erreur d'upload"]
    ];
    header("Location: form-cas.php");
    exit;
}

$inserted = 0;
$ignored  = 0;
$ignored_details = [];

$spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
$rows = $spreadsheet->getActiveSheet()->toArray();

foreach ($rows as $i => $row) {

    if ($i === 0) continue; // ignorer l’en-tête

    // Colonnes B → F
    $type_cas      = trim($row[1] ?? '');
    $nom_centre    = trim($row[2] ?? '');
    $date_cas_xl   = $row[4] ?? '';
    $immat_cell    = trim($row[5] ?? '');

    // Vérification ligne vide ou en-tête
    if (!$type_cas || !$nom_centre || !$date_cas_xl || !$immat_cell ||
        strtolower($type_cas) === 'type de cas' ||
        strtolower($nom_centre) === 'nom cct' ||
        strtolower($immat_cell) === 'immatriculation'
    ) {
        $ignored++;
        $ignored_details[] = "Ligne " . ($i+1) . " ignorée : ligne vide ou en-tête détectée";
        continue;
    }

    // Date du cas
    $date_du_cas = is_numeric($date_cas_xl)
        ? Date::excelToDateTimeObject($date_cas_xl)->format('Y-m-d')
        : date('Y-m-d', strtotime($date_cas_xl));

    // Date d'enregistrement fixée pour tous
    $date_cas = '2023-12-01';

    /* ==========================
       CENTRE : récupération ou création
    ========================== */
    $stmt = $conn->prepare("SELECT id, region FROM centres WHERE nom_centre = ?");
    $stmt->bind_param("s", $nom_centre);
    $stmt->execute();
    $centre = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$centre) {
        $region = 'NON DEFINIE';
        $insertCentre = $conn->prepare("INSERT INTO centres (nom_centre, region) VALUES (?, ?)");
        $insertCentre->bind_param("ss", $nom_centre, $region);
        $insertCentre->execute();
        $id_centre = $insertCentre->insert_id;
        $insertCentre->close();
    } else {
        $id_centre = $centre['id'];
        $region    = $centre['region'];
    }

    /* ==========================
       IMMATRICULATIONS multiples
    ========================== */
    $immats = preg_split('/[,=]/', $immat_cell);

    foreach ($immats as $immat) {
        $immat = trim($immat);
        if (!$immat) continue;

        // Vérification doublon
        $check = $conn->prepare("SELECT id FROM tables_cas WHERE immatriculation = ? AND date_du_cas = ?");
        $check->bind_param("ss", $immat, $date_du_cas);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $ignored++;
            $ignored_details[] = "Ligne " . ($i+1) . " ignorée : doublon pour immatriculation '$immat' et date '$date_du_cas'";
            $check->close();
            continue;
        }
        $check->close();

        // Insertion
        $insert = $conn->prepare("
            INSERT INTO tables_cas (types_cas, region, date_du_cas, immatriculation, id_centre, date_cas)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("ssssss", $type_cas, $region, $date_du_cas, $immat, $id_centre, $date_cas);

        if ($insert->execute()) {
            $inserted++;
        } else {
            $ignored++;
            $ignored_details[] = "Ligne " . ($i+1) . " non insérée : erreur SQL pour immatriculation '$immat'";
        }

        $insert->close();
    }
}

// Stockage du rapport pour affichage
$_SESSION['import_report'] = [
    'inserted' => $inserted,
    'ignored' => $ignored,
    'details' => $ignored_details
];

header("Location: form-cas.php");
exit;
?>
