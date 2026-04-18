<?php
// Archivo: controllers/rendimientoController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_alumno = (int)$_POST['id_alumno'];
    $vel = (int)$_POST['velocidad'];
    $fue = (int)$_POST['fuerza'];
    $res = (int)$_POST['resistencia'];
    $agi = (int)$_POST['agilidad'];
    $coo = (int)$_POST['coordinacion'];
    $fle = (int)$_POST['flexibilidad'];
    $notas = trim($_POST['notas']);
    $fecha = date('Y-m-d');

    try {
        $sql = "INSERT INTO ControlSport.evaluacion_rendimiento 
                (id_alumno, fecha_evaluacion, velocidad, fuerza, resistencia, agilidad, coordinacion, flexibilidad, notas_adicionales) 
                VALUES (:id, :fecha, :v, :f, :r, :a, :c, :fl, :n)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id' => $id_alumno,
            ':fecha' => $fecha,
            ':v' => $vel,
            ':f' => $fue,
            ':r' => $res,
            ':a' => $agi,
            ':c' => $coo,
            ':fl' => $fle,
            ':n' => $notas
        ]);

        // Redirigir con éxito
        header("Location: ../views/admin/inicio.php?msg=eval_success");
        exit;

    } catch (PDOException $e) {
        die("Error al guardar evaluación: " . $e->getMessage());
    }
}