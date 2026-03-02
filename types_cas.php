<?php 
include "config.php";

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$years = range(2029, 2020);

// Année sélectionnée (null = dashboard)
$selected_year = isset($_GET['annee']) && $_GET['annee'] !== '' ? (int)$_GET['annee'] : null;

// --- 1. STATS GLOBALES (Dashboard) ---
// On compte les combinaisons uniques : Centre + Date + Type = 1 Cas
$stats = ['total_cas' => 0, 'total_centres' => 0, 'total_types' => 0];
$stats_query = $conn->query("
    SELECT 
        COUNT(DISTINCT id_centre, date_du_cas, types_cas) as nb_cas,
        COUNT(DISTINCT id_centre) as nb_centres,
        COUNT(DISTINCT types_cas) as nb_types
    FROM tables_cas
");
if ($row = $stats_query->fetch_assoc()) {
    $stats = $row;
}

// --- 2. DONNÉES DU TABLEAU (Par année) ---
$types_data = [];
if ($selected_year !== null) {
    $stmt = $conn->prepare("
        SELECT 
            tc.type_name,
            ? as annee,
            COUNT(DISTINCT t.id_centre, t.date_du_cas) as nb_cas, 
            COUNT(DISTINCT t.id_centre) as nb_centres
        FROM types_cas tc
        LEFT JOIN tables_cas t ON tc.type_name = t.types_cas AND YEAR(t.date_cas) = ?
        GROUP BY tc.type_name
        ORDER BY tc.type_name ASC
    ");
    $stmt->bind_param("ii", $selected_year, $selected_year);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $type = $row['type_name'];
        // On n'ajoute au tableau que si des cas existent pour cette année
        if ($row['nb_cas'] > 0) {
            $types_data[$type][$row['annee']] = [
                'nb_cas' => $row['nb_cas'],
                'nb_centres' => $row['nb_centres']
            ];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Types de Cas - CATIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="types_cas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Styles de base */
        .dashboard-welcome { background: rgba(255, 255, 255, 0.95); border-radius: 12px; padding: 30px; margin: 20px auto 30px; max-width: 1400px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .dashboard-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-box { background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-box .number { font-size: 2rem; font-weight: 700; color: #1e40af; margin: 8px 0; }
        .stat-box .label { font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .welcome-text { text-align: center; padding: 15px; background: linear-gradient(135deg, #3b82f6, #06b6d4); color: white; border-radius: 8px; font-size: 1rem; margin: 10px; }
        .table-container { background: rgba(255, 255, 255, 0.95); border-radius: 16px; padding: 30px; margin: 30px auto; max-width: 1400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .filter-group { margin-bottom: 20px; }
        .filter-group select { width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #cbd5e1; }
        
        /* Conteneur responsive pour le scroll du tableau sur mobile */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        
        /* Ajustements du tableau */
        .data-table { width: 100%; min-width: 700px; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .data-table thead { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; }
        .data-table th, .data-table td { padding: 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .year-badge { background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
        
        /* Correction de l'affichage des boutons Action */
        .action-btn { 
            display: inline-block; /* Permet un padding correct */
            white-space: nowrap; /* Empêche le texte de se couper sur 2 lignes */
            text-align: center;
            background: linear-gradient(135deg, #0ea5e9, #06b6d4); 
            color: white; 
            padding: 10px 18px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            transition: all 0.3s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }

        .btn-add { display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; white-space: nowrap; transition: all 0.3s;}
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }

        /* Média Queries intégrées pour une adaptation parfaite sur mobile */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .btn-add {
                width: 100%;
                text-align: center;
                padding: 14px 24px;
            }
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            .table-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>

<header>
    <img src="image/logo1Catis.jpg" alt="Logo Prooftag CATIS" class="logo" style="max-height: 60px;">
</header>

<div class="dashboard-bg">
    
    <?php if ($selected_year === null): ?>
        <div class="welcome-text">
            <strong>👋 Bienvenue sur CATIS</strong> — Sélectionnez une année ci-dessous pour consulter les types de cas
        </div>
        <div class="dashboard-welcome">
            <div class="dashboard-stats">
                <div class="stat-box">
                    <div class="label">📊 Total Cas (Événements)</div>
                    <div class="number"><?= number_format($stats['nb_cas'] ?? 0) ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">🏢 Centres Actifs</div>
                    <div class="number"><?= number_format($stats['nb_centres'] ?? 0) ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">📋 Catégories de cas</div>
                    <div class="number"><?= number_format($stats['nb_types'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <div class="header-top">
            <h2>Types de Cas<?= $selected_year ? " ({$selected_year})" : "" ?></h2>
            <a href="form-cas.php" class="btn-add">+ Enregistrer un cas</a>
        </div>

        <form method="GET" action="types_cas.php">
            <div class="filter-group">
                <label>📅 Filtrer par année</label>
                <select name="annee" id="filterYear" onchange="this.form.submit()">
                    <option value="">-- Sélectionner une année --</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= $selected_year === $year ? 'selected' : '' ?>>
                            <?= $year ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selected_year !== null): ?>
            <!-- Ajout de la div .table-responsive ici -->
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type de Cas</th>
                            <th>Nombre de cas (Groupés)</th>
                            <th>Centres concernés</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($types_data)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">
                                    Aucun cas enregistré pour <?= $selected_year ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($types_data as $type => $years_stats): ?>
                                <?php foreach ($years_stats as $year => $data): ?>
                                    <tr>
                                        <td class="type-name"><strong><?= h($type) ?></strong></td>
                                        <td><b style="color:#1e40af"><?= $data['nb_cas'] ?></b> cas réel(s)</td>
                                        <td><?= $data['nb_centres'] ?> centre(s)</td>
                                        <td>
                                            <a href="details_cas.php?type=<?= urlencode($type) ?>&annee=<?= $year ?>" class="action-btn">
                                                Voir les centres →
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <p>Veuillez choisir une année pour afficher les statistiques détaillées.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>