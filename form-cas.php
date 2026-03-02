<?php
session_start();
require_once 'config.php';

// Types de cas depuis la table types_cas
$types_query = $conn->query("SELECT id, type_name FROM types_cas ORDER BY type_name ASC");
$types = [];
while ($row = $types_query->fetch_assoc()) {
    $types[] = $row;
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
$error    = $_GET['error'] ?? null;

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

<div class="container mt-3">
    <a href="types_cas.php" class="btn btn-outline-light btn-lg shadow-sm">← Retour à l'accueil</a>
</div>

<div class="form-container">
    <img src="image/logo1Catis.jpg" class="logo">
    <h2 class="form-title">Enregistrement d'un cas</h2>

    <?php if ($inserted !== null): ?>
        <div class="alert alert-success alert-modern animate-in">
            ✅ Import Excel : <strong><?= (int)$inserted ?></strong> inséré(s),
            <strong><?= (int)$ignored ?></strong> ignoré(s)
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-modern animate-in">
            ✅ Cas enregistré avec succès !
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-modern animate-in">
            ❌ <?php 
                if($error == 'duplicate') echo "Ce cas existe déjà (même immatriculation + date + centre).";
                else if($error == 'sql') echo "Erreur de base de données.";
                else if($error == 'missing') echo "Veuillez remplir tous les champs obligatoires.";
                else echo "Erreur lors de l'enregistrement.";
            ?>
        </div>
    <?php endif; ?>

    <div class="import-section mb-4">
        <h5 class="text-white mb-3"> Importer un fichier Excel</h5>
        <form method="POST" action="import-excel.php" enctype="multipart/form-data">
            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
            <button class="btn btn-secondary mt-2 w-100">📥 Lancer l'importation</button>
        </form>
    </div>

    <hr class="text-white my-4">

    <h5 class="text-white mb-3"> Saisie Manuelle</h5>
    <form method="POST" action="traitement-cas.php" enctype="multipart/form-data">

        <label class="text-white">Type de cas</label>
        <select name="types_cas" id="selectTypeCas" class="form-control">
            <option value="">-- Sélectionner un type --</option>
            <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t['type_name']) ?>" <?= ($old['types_cas'] ?? '') === $t['type_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['type_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="mt-3">
            <label class="text-white">Ou créer un nouveau type de cas</label>
            <input 
                type="text" 
                name="nouveau_type" 
                id="nouveauType"
                class="form-control" 
                placeholder="Ex: Inspection frauduleuse, Document manquant..."
                value="<?= htmlspecialchars($old['nouveau_type'] ?? '') ?>">
            <small class="text-warning d-block mt-1">
                ⚠️ Si vous remplissez ce champ, un nouveau type cas sera créé automatiquement
            </small>
        </div>

        <label class="text-white mt-3">Région</label>
        <select name="region" id="region" class="form-control" required>
            <option value="">-- Région --</option>
            <?php foreach ($regions as $r): ?>
                <option value="<?= htmlspecialchars($r) ?>" <?= ($old['region'] ?? '') === $r ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="text-white mt-2">Centre</label>
        <select name="centre" id="centre" class="form-control" required>
            <option value="">-- Centre --</option>
        </select>

        <label class="text-white mt-2">Date d'enregistrement</label>
        <input type="date" name="date_cas" class="form-control" required value="<?= $old['date_cas'] ?? '' ?>">

        <label class="text-white mt-2">Date de l'inspection</label>
        <input type="date" name="date_du_cas" class="form-control" required value="<?= $old['date_du_cas'] ?? '' ?>">

        <label class="text-white mt-2">Immatriculation(s)</label>
        <textarea 
            name="immatriculation" 
            class="form-control" 
            rows="4" 
            required 
            placeholder="Ex: CE171JY, LT829IP, LT959LG, CE242LH..."
            style="resize: vertical; font-family: 'Courier New', monospace; font-size: 0.95rem; line-height: 1.6;"><?= htmlspecialchars($old['immatriculation'] ?? '') ?></textarea>
        <small class="text-info d-block mt-1">
            💡 Séparez les immatriculations par des virgules. Vous pouvez les écrire sur plusieurs lignes.
        </small>

        <label class="text-white mt-3">Documents PDF (plusieurs possibles)</label>
        <input type="file" name="pdf[]" class="form-control" accept=".pdf" multiple>
        <small class="text-info d-block mt-1">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs fichiers PDF.</small>

        <button class="btn btn-primary w-100 mt-4 shadow"> Enregistrer le cas</button>
    </form>
</div>

<script>
// Chargement dynamique des centres
$('#region').on('change', function () {
    const region = $(this).val();
    $('#centre').load('get_centres.php?region=' + region);
});

// Gestion intelligente Type existant vs Nouveau type
$('#nouveauType').on('input', function() {
    if($(this).val().trim() !== '') {
        $('#selectTypeCas').prop('required', false).val('').css('opacity', '0.5');
    } else {
        $('#selectTypeCas').prop('required', true).css('opacity', '1');
    }
});

$('#selectTypeCas').on('change', function() {
    if($(this).val() !== '') {
        $('#nouveauType').val('').prop('required', false);
    }
});

// Auto-masquer les alertes après 5 secondes
document.querySelectorAll('.alert-modern.animate-in').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.style.display = 'none', 500);
    }, 5000);
});
</script>

</body>
</html>