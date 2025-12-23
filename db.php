<?php
$host = "localhost";
$user = "root";
$pass = "";
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}
?>
