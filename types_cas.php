<?php 
include "config.php";

// Fonction pour sécuriser l'affichage
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Définir les années de filtrage de 2020 à 2026
$years = range(2026, 2020); // Descendant : 2026,2025,...,2020

// Récupérer tous les types de cas
$types_query = $conn->query("SELECT DISTINCT types_cas FROM tables_cas ORDER BY types_cas ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Types de Cas</title>
    <link rel="stylesheet" href="types_cas.css">
</head>
<body>
    <header>
        <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" alt="Logo Prooftag CATIS" class="logo">
    </header>

    <div class="dashboard-bg">
        <a href="form-cas.php" class="btn-custom btn-green">Enregistrer un nouveau cas</a>
        <h1 class="dashboard-title">Types de Cas</h1>
        <div class="buttons-container">
            <?php while($row = $types_query->fetch_assoc()): ?>
                <form method="GET" action="details_cas.php" class="type-button">
                    <input type="hidden" name="type" value="<?php echo h($row['types_cas']); ?>">

                    <select name="annee" required>
                        <option value="">Année</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit"><?php echo h($row['types_cas']); ?></button>
                </form>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

