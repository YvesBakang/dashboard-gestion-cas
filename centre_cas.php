<?php
require_once 'config.php';

$type   = $_GET['type'] ?? '';
$centre = isset($_GET['centre']) ? (int)$_GET['centre'] : 0;
$annee  = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;

if ($type === '' || $centre <= 0 || $annee < 2020 || $annee > 2029) {
    die("Paramètres invalides. <a href='types_cas.php'>Retour</a>");
}

// Utilisation de GROUP_CONCAT pour fusionner les lignes ayant la même date du cas
$stmt = $conn->prepare("
    SELECT 
        t.region,
        t.date_cas,
        t.date_du_cas,
        GROUP_CONCAT(DISTINCT t.immatriculation SEPARATOR ', ') AS immatriculations,
        GROUP_CONCAT(DISTINCT t.pdf SEPARATOR ',') AS tous_pdfs,
        c.nom_centre
    FROM tables_cas t
    INNER JOIN centres c ON t.id_centre = c.id
    WHERE t.types_cas = ?
      AND c.id = ?
      AND YEAR(t.date_cas) = ?
    GROUP BY t.date_du_cas, t.region, t.date_cas, c.nom_centre
    ORDER BY t.date_du_cas DESC
");
$stmt->bind_param('sii', $type, $centre, $annee);
$stmt->execute();
$result = $stmt->get_result();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="details_cas.css">
</head>
<body>

<header>
    <img src="image/logo1Catis.jpg" class="logo" alt="Logo">
    <h2 style="color: white; font-size: 1.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 1px;">
        <?php echo h($type); ?> (<?php echo $annee; ?>)
    </h2>
    <h1 style="margin-top: 0;"><?php echo h($nomCentre); ?></h1>
    <a href="details_cas.php?type=<?php echo urlencode($type); ?>&annee=<?php echo $annee; ?>" class="btn-custom btn-green">
        Retour aux centres
    </a>
</header>

<div class="details-table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Région</th>
                    <th>Date enregistrement</th>
                    <th>Date du cas</th>
                    <th>Immatriculation(s)</th>
                    <th>PDF(s)</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($result->num_rows === 0): ?>
        <tr>
            <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                Aucun cas trouvé pour ce centre en <?= $annee ?>
            </td>
        </tr>
    <?php else: ?>
        <?php while ($r = $result->fetch_assoc()): ?> 
        <tr>
            <td data-label="RÉGION"><?php echo h($r['region']); ?></td>
            <td data-label="DATE ENREGISTREMENT"><?php echo h($r['date_cas']); ?></td>
            <td data-label="DATE DU CAS"><?php echo h($r['date_du_cas']); ?></td>
            <td data-label="IMMATRICULATION(S)">
                <div style="word-break: break-all;">
                    <?php echo h($r['immatriculations']); ?>
                </div>
            </td>
            <td data-label="PDF">
                <?php 
                if (!empty($r['tous_pdfs'])) {
                    $liste_pdfs = array_unique(explode(',', $r['tous_pdfs']));
                    $count = 1;
                    foreach ($liste_pdfs as $nom_pdf) {
                        $nom_pdf = trim($nom_pdf);
                        if (!empty($nom_pdf)) {
                            echo '<a class="btn-custom btn-blue" 
                                     style="margin: 2px; padding: 5px 10px; font-size: 11px; display: inline-block;" 
                                     href="view-pdf.php?file='.urlencode($nom_pdf).'" 
                                     target="_blank">
                                     📄 PDF '.$count.'
                                  </a>';
                            $count++;
                        }
                    }
                } else {
                    echo '<span style="color: #94a3b8;">Aucun fichier</span>';
                }
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>

</body>
</html>