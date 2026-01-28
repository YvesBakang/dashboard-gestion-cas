<?php
include 'config.php';

$type   = $_GET['type']   ?? '';
$centre = $_GET['centre'] ?? '';
$annee  = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;

if ($type === '' || $centre === '' || $annee < 2020 || $annee > 2026) {
    die("Paramètres invalides. <a href='types_cas.php'>Retour</a>");
}

$stmt = $conn->prepare("
    SELECT 
        t.region,
        t.date_cas,
        t.date_du_cas,
        t.immatriculation,
        t.pdf,
        c.nom_centre
    FROM tables_cas t
    INNER JOIN centres c ON t.id_centre = c.id
    WHERE t.types_cas = ?
      AND c.id = ?
      AND YEAR(t.date_du_cas) = ?
    ORDER BY t.date_du_cas DESC
");
$stmt->bind_param('sii', $type, $centre, $annee);
$stmt->execute();
$result = $stmt->get_result();

// Récupérer le nom du centre
$first = $result->fetch_assoc();
$nomCentre = $first['nom_centre'] ?? '';
$result->data_seek(0);

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo h($nomCentre); ?></title>
    <link rel="stylesheet" href="details_cas.css">
</head>
<body>

<header>
    <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" class="logo">
    <h1><?php echo h($nomCentre); ?> (Année <?php echo $annee; ?>)</h1>
    <a href="details_cas.php?type=<?php echo urlencode($type); ?>&annee=<?php echo $annee; ?>" class="btn-custom btn-green">
        Retour aux centres
    </a>
</header>

<div class="details-table-container">
    <table>
        <thead>
            <tr>
                <th>Région</th>
                <th>Date d'enregistrement</th>
                <th>Date du cas</th>
                <th>Immatriculation</th>
                <th>PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo h($r['region']); ?></td>
                    <td><?php echo h($r['date_cas']); ?></td>
                    <td><?php echo h($r['date_du_cas']); ?></td>
                    <td><?php echo h($r['immatriculation']); ?></td>
                    <td>
                        <?php if ($r['pdf']): ?>
                            <a href="uploads/<?php echo h($r['pdf']); ?>" target="_blank">Voir PDF</a>
                        <?php else: ?>
                            Aucun fichier
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

