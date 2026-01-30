<?php
include 'config.php';

$type  = isset($_GET['type']) ? trim($_GET['type']) : '';
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;

if ($type === '' || $annee < 2020 || $annee > 2026) {
    die("Type ou année invalide. <a href='types_cas.php'>Retour</a>");
}

// Récupérer les centres ayant ce type de cas pour cette année
$stmt = $conn->prepare("
    SELECT DISTINCT c.id, c.nom_centre
    FROM tables_cas t
    INNER JOIN centres c ON t.id_centre = c.id
    WHERE t.types_cas = ? AND YEAR(t.date_du_cas) = ?
    ORDER BY c.nom_centre ASC
");
$stmt->bind_param('si', $type, $annee);
$stmt->execute();
$result = $stmt->get_result();

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Centres - <?php echo h($type); ?></title>
    <link rel="stylesheet" href="details_cas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<header>
    <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" class="logo">
    <h1>Type de cas : <?php echo h($type); ?> (Année <?php echo $annee; ?>)</h1>
    <a href="types_cas.php" class="btn-custom btn-green">Retour</a>
</header>

<div class="details-table-container">

    <!-- Barre de recherche -->
    <div style="margin-bottom: 15px;">
        <input type="text" id="searchCentre" class="form-control" placeholder="🔍 Rechercher un centre par son nom">
    </div>

    <table id="centresTable">
        <thead>
            <tr>
                <th>Centre</th>
                <th>Voir les cas</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($c = $result->fetch_assoc()): ?>
                <tr>
                    <td class="centre-nom"><?php echo h($c['nom_centre']); ?></td>
                    <td>
                        <a class="btn-custom btn-blue"
                           href="centre_cas.php?type=<?php echo urlencode($type); ?>&centre=<?php echo $c['id']; ?>&annee=<?php echo $annee; ?>">
                           Ouvrir
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// Filtrage dynamique des centres
$(document).ready(function() {
    $('#searchCentre').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#centresTable tbody tr').filter(function() {
            $(this).toggle($(this).find('.centre-nom').text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>

</body>
</html>


