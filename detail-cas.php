<?php
include 'config.php';

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
if ($type === '') {
    header('Location: types_cas.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT DISTINCT c.id, c.nom_centre
    FROM tables_cas t
    INNER JOIN centres c ON t.id_centre = c.id
    WHERE t.types_cas = ?
    ORDER BY c.nom_centre ASC
");
$stmt->bind_param('s', $type);
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

    <!-- ⚠️ CSS CONSERVÉ -->
    <link rel="stylesheet" href="details_cas.css">
</head>
<body>

<header>
    <img src="image/logo-prooftag-securite-tracabilite-bouteilles-vins.jpg" class="logo">
    <h1>Type de cas : <?php echo h($type); ?></h1>
    <a href="types_cas.php" class="btn-custom btn-green">Retour</a>
</header>

<div class="details-table-container">
    <table>
        <thead>
            <tr>
                <th>Centre</th>
                <th>Voir les cas</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($c = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo h($c['nom_centre']); ?></td>
                    <td>
                        <a class="btn-custom btn-blue"
                           href="centre_cas.php?type=<?php echo urlencode($type); ?>&centre=<?php echo $c['id']; ?>">
                           Ouvrir
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
