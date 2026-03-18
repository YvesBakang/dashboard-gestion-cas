<?php
$host = "localhost";
$user = "root";
$password = ""; // vide par défaut sur XAMPP
$dbname = "proof1343861_9l33vg"; // nom de ta base

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
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
