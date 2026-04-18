<?php
// Archivo: controllers/alumnoController.php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

// 1. ACTUALIZAR ALUMNO Y TUTOR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    
    $id_alumno = (int)$_POST['id_alumno'];
    $id_tutor = (int)$_POST['id_tutor'];
    
    $edad = (int)$_POST['edad'];
    $institucion_medica = trim($_POST['institucion_medica']);
    $telefono_tutor = trim($_POST['telefono_tutor']);

    function dividirNombreActualizar($nombre_completo) {
        $partes = explode(' ', trim($nombre_completo));
        $nombre = $partes[0];
        $apPaterno = isset($partes[1]) ? $partes[1] : ' ';
        $apMaterno = isset($partes[2]) ? implode(' ', array_slice($partes, 2)) : ' ';
        return [$nombre, $apPaterno, $apMaterno];
    }

    list($nombre_a, $apP_a, $apM_a) = dividirNombreActualizar($_POST['nombre_alumno']);
    list($nombre_t, $apP_t, $apM_t) = dividirNombreActualizar($_POST['nombre_tutor']);

    try {
        $conexion->beginTransaction();

        // Actualizar Alumno
        $sql_alumno = "UPDATE ControlSport.alumno 
                       SET nombre_alumno = :nombre, apellido_p_a = :apP, apellido_m_a = :apM, 
                           edad = :edad, institucion_medica = :inst
                       WHERE id_alumno = :id";
        $stmt_a = $conexion->prepare($sql_alumno);
        $stmt_a->execute([
            ':nombre' => $nombre_a, ':apP' => $apP_a, ':apM' => $apM_a,
            ':edad' => $edad, ':inst' => $institucion_medica, ':id' => $id_alumno
        ]);

        // Actualizar Tutor
        $sql_tutor = "UPDATE ControlSport.tutor 
                      SET nombre_tutor = :nombre, apellido_p_t = :apP, apellido_m_t = :apM, telefono_tutor = :telefono
                      WHERE id_tutor = :id";
        $stmt_t = $conexion->prepare($sql_tutor);
        $stmt_t->execute([
            ':nombre' => $nombre_t, ':apP' => $apP_t, ':apM' => $apM_t,
            ':telefono' => $telefono_tutor, ':id' => $id_tutor
        ]);

        $conexion->commit();
        header("Location: ../views/admin/alumnos.php?msg=actualizado");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        header("Location: ../views/admin/alumnos.php?error=db");
        exit;
    }
}

// 2. DAR DE BAJA
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'baja' && isset($_GET['id'])) {
    
    $id_alumno = (int)$_GET['id'];

    try {
        $conexion->beginTransaction();

        // Obtener a qué grupo pertenece el alumno antes de darlo de baja
        $sql_grupo = "SELECT id_grupo FROM ControlSport.alumno WHERE id_alumno = :id";
        $stmt_grupo = $conexion->prepare($sql_grupo);
        $stmt_grupo->execute([':id' => $id_alumno]);
        $id_grupo = $stmt_grupo->fetchColumn();

        // Pasar alumno a Inactivo
        $sql_baja = "UPDATE ControlSport.alumno SET estado = 'Inactivo' WHERE id_alumno = :id";
        $stmt_baja = $conexion->prepare($sql_baja);
        $stmt_baja->execute([':id' => $id_alumno]);

        // Liberar un cupo en su grupo
        if ($id_grupo) {
            $sql_cupo = "UPDATE ControlSport.grupo SET cupo_actual = cupo_actual - 1 WHERE id_grupo = :id_grupo";
            $stmt_cupo = $conexion->prepare($sql_cupo);
            $stmt_cupo->execute([':id_grupo' => $id_grupo]);
        }

        $conexion->commit();
        header("Location: ../views/admin/alumnos.php?msg=baja");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        header("Location: ../views/admin/alumnos.php?error=db");
        exit;
    }
}
?>