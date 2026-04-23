<?php
// Archivo: controllers/solicitudController.php
session_start();
require_once '../config/conexion.php';

// 1. ACCIÓN: APROBAR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'aprobar') {
    $id_alumno = (int)$_POST['id_alumno'];
    $id_grupo = (int)$_POST['id_grupo'];
    $nombre_alumno = trim($_POST['nombre_alumno']);

    try {
        $conexion->beginTransaction();
        
        // Cambiamos el estado a Inscrito y le asignamos el grupo
        $sql = "UPDATE ControlSport.alumno SET estado = 'Inscrito', id_grupo = :id_grupo WHERE id_alumno = :id_alumno";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id_grupo' => $id_grupo, ':id_alumno' => $id_alumno]);

        // Aumentamos el cupo del grupo
        $sql_cupo = "UPDATE ControlSport.grupo SET cupo_actual = cupo_actual + 1 WHERE id_grupo = :id_grupo";
        $stmt_cupo = $conexion->prepare($sql_cupo);
        $stmt_cupo->execute([':id_grupo' => $id_grupo]);

        $conexion->commit();
        header("Location: ../views/admin/solicitudes.php?msg=aprobado&nombre=" . urlencode($nombre_alumno));
        exit;
    } catch (PDOException $e) {
        $conexion->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// 2. ACCIÓN: RECHAZAR (Baja Lógica / Soft Delete)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'rechazar' && isset($_GET['id'])) {
    
    $id_alumno = (int)$_GET['id'];
    $nombre_alumno = trim($_GET['nombre']);

    try {
        // En lugar de borrarlo, actualizamos su estado para que el sistema "tenga memoria" de él
        $sql_update = "UPDATE ControlSport.alumno SET estado = 'Rechazado' WHERE id_alumno = :id_alumno";
        $stmt = $conexion->prepare($sql_update);
        $stmt->execute([':id_alumno' => $id_alumno]);

        header("Location: ../views/admin/solicitudes.php?msg=rechazado&nombre=" . urlencode($nombre_alumno));
        exit;

    } catch (PDOException $e) {
        die("Error al rechazar la solicitud: " . $e->getMessage());
    }
}

// Si entran directo al controlador, los regresamos
header("Location: ../views/admin/solicitudes.php");
exit;
?>