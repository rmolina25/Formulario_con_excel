<?php
$host = 'localhost';
$dbname = 'correos';
$username = 'root';
$password = 'root'; // Cambia si tienes contraseña en MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
