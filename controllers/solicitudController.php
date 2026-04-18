<?php
// Archivo: controllers/solicitudController.php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

// 1. ACCIÓN: APROBAR (Viene por POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'aprobar') {
    
    $id_alumno = (int)$_POST['id_alumno'];
    $id_grupo = (int)$_POST['id_grupo'];
    $nombre_alumno = trim($_POST['nombre_alumno']);

    try {
        $conexion->beginTransaction();

        // Actualizamos estado y asignamos grupo
        $sql_update = "UPDATE ControlSport.alumno SET estado = 'Inscrito', id_grupo = :id_grupo WHERE id_alumno = :id_alumno";
        $stmt = $conexion->prepare($sql_update);
        $stmt->execute([':id_grupo' => $id_grupo, ':id_alumno' => $id_alumno]);

        // Sumamos +1 al cupo_actual del grupo
        $sql_cupo = "UPDATE ControlSport.grupo SET cupo_actual = cupo_actual + 1 WHERE id_grupo = :id_grupo";
        $stmt_cupo = $conexion->prepare($sql_cupo);
        $stmt_cupo->execute([':id_grupo' => $id_grupo]);

        $conexion->commit();

        header("Location: ../views/admin/solicitudes.php?msg=aprobado&nombre=" . urlencode($nombre_alumno));
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        die("Error al aprobar solicitud: " . $e->getMessage());
    }
}

// 2. ACCIÓN: RECHAZAR (Viene por GET)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'rechazar' && isset($_GET['id'])) {
    
    $id_alumno = (int)$_GET['id'];
    $nombre_alumno = trim($_GET['nombre']);

    try {
        // En lugar de borrarlo, lo marcamos como Rechazado (Mejor práctica para historial)
        $sql_reject = "UPDATE ControlSport.alumno SET estado = 'Rechazado' WHERE id_alumno = :id_alumno";
        $stmt = $conexion->prepare($sql_reject);
        $stmt->execute([':id_alumno' => $id_alumno]);

        header("Location: ../views/admin/solicitudes.php?msg=rechazado&nombre=" . urlencode($nombre_alumno));
        exit;

    } catch (PDOException $e) {
        die("Error al rechazar solicitud: " . $e->getMessage());
    }
}

// Si entran directo al controlador, los regresamos
header("Location: ../views/admin/solicitudes.php");
exit;
?>