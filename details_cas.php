<?php
require_once 'config.php';
require_once 'navbar.php';

$type  = isset($_GET['type']) ? trim($_GET['type']) : '';
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;

if ($type === '' || $annee < 2020 || $annee > 2029) {
    die("Type ou année invalide. <a href='types_cas.php'>Retour</a>");
}

$stmt = $conn->prepare("
    SELECT DISTINCT c.id, c.nom_centre
    FROM tables_cas t
    INNER JOIN centres c ON t.id_centre = c.id
    WHERE t.types_cas = ?
     AND YEAR(t.date_cas) = ?
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
    <title>Centres – <?php echo h($type); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="details_cas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php require_once 'navbar.php'; ?>

<div class="page-header">
    <h2>Type de cas : <?php echo h($type); ?> (<?php echo $annee; ?>)</h2>
</div>


    <div class="table-wrapper">
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
</div>

<script>
$('#searchCentre').on('keyup', function () {
    const value = $(this).val().toLowerCase();
    $('#centresTable tbody tr').each(function () {
        $(this).toggle($(this).find('.centre-nom').text().toLowerCase().includes(value));
    });
});
</script>

</body>
</html>