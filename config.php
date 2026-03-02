<?php
$host = "localhost";
$user = "root";
$password = ""; // vide par défaut sur XAMPP
$dbname = "proof1343861_9l33vg"; // nom de ta base

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>
