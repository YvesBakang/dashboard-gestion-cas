<<<<<<< HEAD
<?php
$host = "localhost";
$user = "root";
$password = ""; // vide par défaut sur XAMPP
$dbname = "gestion-cas"; // nom de ta base

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
=======
<?php
$host = "localhost";
$user = "root";
$password = ""; // vide par défaut sur XAMPP
$dbname = "gestion-cas"; // nom de ta base

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
>>>>>>> cae26eb8cd1aa78f464dbed20270946d17518866
?>