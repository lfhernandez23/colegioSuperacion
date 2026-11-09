<?php
// Conexión a la BD colegio_superacion
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "colegio_superacion";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
