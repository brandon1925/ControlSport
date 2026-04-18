<?php
// Archivo: controllers/staffController.php
session_start();
require_once '../config/conexion.php';

// Si la petición es para REGISTRAR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'registrar') {
    
    $nombre = trim($_POST['nombre_completo']);
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    $fecha_alta = date("Y-m-d");

    try {
        // CORRECCIÓN APLICADA: ControlSport.entrenador
        $sql = "INSERT INTO ControlSport.entrenador (nombre_completo, usuario, contrasena, estado, fecha_alta) 
                VALUES (:nombre, :usuario, :contrasena, 'Activo', :fecha)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->bindParam(':fecha', $fecha_alta);
        $stmt->execute();
        
        header("Location: ../views/admin/gestion.php?msg=creado");
        exit;

    } catch (PDOException $e) {
        die("Error al registrar entrenador: " . $e->getMessage());
    }
}

// Si la petición es para DAR DE BAJA
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'baja' && isset($_GET['id'])) {
    
    $id_entrenador = $_GET['id'];

    try {
        // CORRECCIÓN APLICADA: ControlSport.entrenador
        $sql = "UPDATE ControlSport.entrenador SET estado = 'Inactivo' WHERE id_entrenador = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id_entrenador);
        $stmt->execute();

        header("Location: ../views/admin/gestion.php?msg=baja");
        exit;

    } catch (PDOException $e) {
        die("Error al dar de baja: " . $e->getMessage());
    }
}
?>