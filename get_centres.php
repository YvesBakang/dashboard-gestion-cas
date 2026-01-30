<<<<<<< HEAD
<?php
include 'config.php';

$region = $_GET['region'] ?? '';

if ($region) {
    $stmt = $conn->prepare("SELECT id, nom_centre FROM centres WHERE region = ? ORDER BY nom_centre ASC");
    $stmt->bind_param('s', $region);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">-- Sélectionner un centre --</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.htmlspecialchars($row['id']).'">'.htmlspecialchars($row['nom_centre']).'</option>';
    }
}
?>
=======
<?php
include 'config.php';

$region = $_GET['region'] ?? '';

if ($region) {
    $stmt = $conn->prepare("SELECT id, nom_centre FROM centres WHERE region = ? ORDER BY nom_centre ASC");
    $stmt->bind_param('s', $region);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">-- Sélectionner un centre --</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.htmlspecialchars($row['id']).'">'.htmlspecialchars($row['nom_centre']).'</option>';
    }
}
?>
>>>>>>> cae26eb8cd1aa78f464dbed20270946d17518866
