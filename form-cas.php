<<<<<<< HEAD
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



=======
<?php
session_start(); // ⚠️ IMPORTANT pour utiliser $_SESSION
require_once 'config.php';

// Récupérer tous les types de cas existants
$types_query = $conn->query("SELECT DISTINCT types_cas FROM tables_cas ORDER BY types_cas ASC");
$types = [];
while ($row = $types_query->fetch_assoc()) {
    $types[] = $row['types_cas'];
}

// Récupérer toutes les régions
$regions_query = $conn->query("SELECT DISTINCT region FROM centres ORDER BY region ASC");
$regions = [];
while ($row = $regions_query->fetch_assoc()) {
    $regions[] = $row['region'];
}

// Messages de feedback
$inserted = $_GET['inserted'] ?? null;
$ignored  = $_GET['ignored'] ?? null;
$success  = isset($_GET['success']);
$error    = isset($_GET['error']);

// Garder les valeurs saisies après soumission
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement d'un cas</title>
    <a href="types_cas.php">← Retour à l'accueil</a>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="form-cas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<header class="header text-center mb-4"></header>
<div class="form-container">
    <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" alt="Logo entreprise" class="logo mb-2">
    <h2 class="form-title">Enregistrement d'un cas</h2>

<?php if ($inserted !== null): ?>
<div class="alert alert-success">
    ✅ Import Excel terminé : <strong><?= (int)$inserted ?></strong> cas inséré(s), 
    <strong><?= (int)$ignored ?></strong> ignoré(s)
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success">✅ Cas enregistré manuellement avec succès</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">❌ Erreur lors de l'import Excel</div>
<?php endif; ?>

<!-- IMPORT EXCEL -->
<form method="POST" action="import-excel.php" enctype="multipart/form-data" class="mb-4">
    <label class="fw-bold">Importer fichier Excel</label>
    <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx" required>
    <button class="btn btn-secondary mt-2">Importer</button>
</form>

<hr>

<!-- ENREGISTREMENT MANUEL -->
<form method="POST" action="traitement-cas.php" enctype="multipart/form-data">

    <!-- Type de cas -->
    <label>Type de cas existant :</label>
    <select name="types_cas" class="form-control mb-2">
        <option value="">-- Sélectionner un type --</option>
        <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= (isset($old['types_cas']) && $old['types_cas']==$t)?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Ou créer un nouveau type :</label>
    <input type="text" name="nouveau_type" class="form-control mb-2" placeholder="Nouveau type de cas" value="<?= htmlspecialchars($old['nouveau_type'] ?? '') ?>">

    <!-- Région et Centre -->
    <label>Région :</label>
    <select name="region" id="region" class="form-control mb-2" required>
        <option value="">-- Sélectionner une région --</option>
        <?php foreach ($regions as $r): ?>
            <option value="<?= htmlspecialchars($r) ?>" <?= (isset($old['region']) && $old['region']==$r)?'selected':'' ?>><?= htmlspecialchars($r) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Centre :</label>
    <select name="centre" id="centre" class="form-control mb-2" required>
        <option value="">-- Sélectionner un centre --</option>
        <?php 
        if(isset($old['region']) && $old['region']){
            $stmt = $conn->prepare("SELECT id, nom_centre FROM centres WHERE region=? ORDER BY nom_centre ASC");
            $stmt->bind_param('s', $old['region']);
            $stmt->execute();
            $result = $stmt->get_result();
            while($c = $result->fetch_assoc()){
                $selected = (isset($old['centre']) && $old['centre']==$c['id']) ? 'selected' : '';
                echo '<option value="'.htmlspecialchars($c['id']).'" '.$selected.'>'.htmlspecialchars($c['nom_centre']).'</option>';
            }
        }
        ?>
    </select>

    <!-- Dates -->
    <label>Date d'enregistrement :</label>
    <input type="date" name="date_cas" class="form-control mb-2" required value="<?= htmlspecialchars($old['date_cas'] ?? '') ?>">

    <label>Date de l'inspection :</label>
    <input type="date" name="date_du_cas" class="form-control mb-2" required value="<?= htmlspecialchars($old['date_du_cas'] ?? '') ?>">

    <!-- Autres champs -->
    <label>Immatriculation :</label>
    <input type="text" name="immatriculation" class="form-control mb-2" placeholder="Immatriculation" required value="<?= htmlspecialchars($old['immatriculation'] ?? '') ?>">

    <label>PDF (optionnel) :</label>
    <input type="file" name="pdf" class="form-control mb-2">

    <button class="btn btn-primary">Enregistrer</button>
</form>
</div>

<script>
$(document).ready(function() {
    $('#region').change(function() {
        var region = $(this).val();
        if(region) {
            $.ajax({
                url: 'get_centres.php',
                type: 'GET',
                data: { region: region },
                success: function(data) {
                    $('#centre').html(data);
                }
            });
        } else {
            $('#centre').html('<option value="">-- Sélectionner un centre --</option>');
        }
    });
});
</script>

</body>
</html>


>>>>>>> cae26eb8cd1aa78f464dbed20270946d17518866
