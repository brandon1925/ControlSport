<?php
// Archivo: config/conexion.php

// 1. Forzar la hora de México para todo PHP
date_default_timezone_set('America/Mexico_City');

$host = 'localhost';
$dbname = 'ControlSport'; 
$user = 'postgres';       // Usuario de PostgreSQL
$password = '12345';      // Tu contraseña de PostgreSQL
$port = '5432';           // Puerto de PostgreSQL

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $conexion = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // 2. Forzar la hora de México para todo PostgreSQL
    $conexion->exec("SET TIME ZONE 'America/Mexico_City'");

} catch (PDOException $e) {
    die("Error crítico de conexión a PostgreSQL: " . $e->getMessage());
}
?>