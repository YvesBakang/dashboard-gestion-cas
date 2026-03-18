<?php
session_start();

$host = "localhost";
$user = "root";
$password = ""; // vide par défaut sur XAMPP
$dbname = "gestion-cas"; // nom de ta base

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Protection des pages — redirige vers index.php si non connecté
$page_actuelle = basename($_SERVER['PHP_SELF']);
$pages_publiques = ['index.php']; // seul index.php est accessible sans connexion

if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    if (!in_array($page_actuelle, $pages_publiques)) {
        header("Location: index.php"); 
        exit();
    }
}


// ===== WhatsApp gateway (Node.js + Baileys) =====
// Le projet PHP enverra le message vers ce gateway via HTTP.
// Exemple: si ton serveur Node tourne sur le port 3001
$WHATSAPP_GATEWAY_URL = getenv('WHATSAPP_GATEWAY_URL') ?: 'http://127.0.0.1:3001';

// Numéros WhatsApp prédéfinis (destinataires “autorisés”).
// Format: n'importe quelle chaîne avec chiffres (ex: "+2376XXXXXXXX" ou "2376XXXXXXXX").
$WHATSAPP_PREDEFINED_RECIPIENTS = [
    // TODO: mets ici tes numéros
    // '+237612345678',
    // '+237698765432',
];
?>

