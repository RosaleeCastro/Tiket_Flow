<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "ticketpro_db";
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>