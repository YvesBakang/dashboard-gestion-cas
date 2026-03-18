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
?>

