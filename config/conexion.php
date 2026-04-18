<?php
// Archivo: config/conexion.php

$host = 'localhost';
$dbname = 'ControlSport'; 
$user = 'postgres';       // Usuario de PostgreSQL
$password = '12345';      // Tu contraseña de PostgreSQL (Asegúrate de que sea la correcta)
$port = '5432';           // Puerto de PostgreSQL

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $conexion = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error crítico de conexión a PostgreSQL: " . $e->getMessage());
}
?>