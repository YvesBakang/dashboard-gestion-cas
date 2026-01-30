<?php
include 'config.php';

/* ==========================
   Enregistrement manuel + compteur
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $types_cas   = $_POST['types_cas'];
    $region      = $_POST['region'];
    $id_centre   = (int)$_POST['centre'];
    $date_du_cas = $_POST['date_du_cas'];
    $immat       = $_POST['immatriculation'];

    // Compteurs
    $inserted = 0;
    $ignored  = 0;

    /* ==========================
       PDF
    ========================== */
    $pdf = '';
    if (!empty($_FILES['pdf']['name'])) {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        $pdf = time().'_'.$_FILES['pdf']['name'];
        move_uploaded_file($_FILES['pdf']['tmp_name'], 'uploads/'.$pdf);
    }

    /* ==========================
       Vérifier doublon
    ========================== */
    $stmt = $conn->prepare(
        "SELECT id FROM tables_cas
         WHERE immatriculation = ?
         AND date_du_cas = ?
         AND id_centre = ?"
    );
    $stmt->bind_param("ssi", $immat, $date_du_cas, $id_centre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {

        /* ==========================
           Insertion
        ========================== */
        $insert = $conn->prepare(
            "INSERT INTO tables_cas
            (types_cas, region, date_du_cas, immatriculation, id_centre, pdf, date_cas)
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        $insert->bind_param(
            "ssssss",
            $types_cas,
            $region,
            $date_du_cas,
            $immat,
            $id_centre,
            $pdf
        );

        if ($insert->execute()) {
            $inserted++;
        }

        $insert->close();

    } else {
        $ignored++;
    }

    $stmt->close();

    /* ==========================
       Redirection avec résultat
    ========================== */
    header("Location: form-cas.php?success=1&inserted=$inserted&ignored=$ignored");
    exit;
}

