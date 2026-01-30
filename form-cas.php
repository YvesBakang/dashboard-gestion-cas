<?php
session_start();
require_once 'config.php';

// Types de cas
$types_query = $conn->query("SELECT DISTINCT types_cas FROM tables_cas ORDER BY types_cas ASC");
$types = [];
while ($row = $types_query->fetch_assoc()) {
    $types[] = $row['types_cas'];
}

// Régions
$regions_query = $conn->query("SELECT DISTINCT region FROM centres ORDER BY region ASC");
$regions = [];
while ($row = $regions_query->fetch_assoc()) {
    $regions[] = $row['region'];
}

// Messages
$inserted = $_GET['inserted'] ?? null;
$ignored  = $_GET['ignored'] ?? null;
$success  = isset($_GET['success']);
$error    = isset($_GET['error']);

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement d'un cas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="form-cas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- 🔥 Bouton retour moderne -->
<div class="container mt-3">
    <a href="types_cas.php" class="btn btn-outline-light btn-lg shadow-sm">
        ← Retour à l’accueil
    </a>
</div>

<div class="form-container">
    <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" class="logo">
    <h2 class="form-title">Enregistrement d'un cas</h2>

    <?php if ($inserted !== null): ?>
        <div class="alert alert-success">
            ✅ Import Excel : <strong><?= (int)$inserted ?></strong> inséré(s),
            <strong><?= (int)$ignored ?></strong> ignoré(s)
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ Cas enregistré avec succès</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">❌ Erreur lors de l’enregistrement</div>
    <?php endif; ?>

    <!-- Import Excel -->
    <form method="POST" action="import-excel.php" enctype="multipart/form-data" class="mb-4">
        <label>Importer fichier Excel</label>
        <input type="file" name="excel_file" class="form-control" required>
        <button class="btn btn-secondary mt-2 w-100">Importer</button>
    </form>

    <hr>

    <!-- Enregistrement manuel -->
    <form method="POST" action="traitement-cas.php" enctype="multipart/form-data">

        <label>Type de cas existant</label>
        <select name="types_cas" class="form-control">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= ($old['types_cas'] ?? '') === $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Nouveau type (optionnel)</label>
        <input type="text" name="nouveau_type" class="form-control" value="<?= htmlspecialchars($old['nouveau_type'] ?? '') ?>">

        <label>Région</label>
        <select name="region" id="region" class="form-control" required>
            <option value="">-- Région --</option>
            <?php foreach ($regions as $r): ?>
                <option value="<?= htmlspecialchars($r) ?>" <?= ($old['region'] ?? '') === $r ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Centre</label>
        <select name="centre" id="centre" class="form-control" required>
            <option value="">-- Centre --</option>
        </select>

        <label>Date d’enregistrement</label>
        <input type="date" name="date_cas" class="form-control" required value="<?= $old['date_cas'] ?? '' ?>">

        <label>Date de l’inspection</label>
        <input type="date" name="date_du_cas" class="form-control" required value="<?= $old['date_du_cas'] ?? '' ?>">

        <label>Immatriculation</label>
        <input type="text" name="immatriculation" class="form-control" required value="<?= $old['immatriculation'] ?? '' ?>">

        <label>PDF (optionnel)</label>
        <input type="file" name="pdf" class="form-control">

        <button class="btn btn-primary w-100 mt-3">Enregistrer</button>
    </form>
</div>

<script>
$('#region').on('change', function () {
    const region = $(this).val();
    $('#centre').load('get_centres.php?region=' + region);
});
</script>

</body>
</html>



