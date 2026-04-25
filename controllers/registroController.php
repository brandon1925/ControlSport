<?php
// Archivo: controllers/registroController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibir datos básicos del Alumno
    $id_entrenador = (int)$_POST['id_entrenador'];
    $edad = (int)$_POST['edad'];
    $curp = trim(strtoupper($_POST['curp']));
    $peso = (float)$_POST['peso'];
    $estatura = (float)$_POST['estatura'];
    $institucion_medica = trim($_POST['institucion_medica']);
    $no_afiliacion = trim($_POST['no_afiliacion']);
    $domicilio = trim($_POST['domicilio']);
    
    // Recibir datos dinámicos del Tutor
    $id_tutor_post = isset($_POST['id_tutor']) ? (int)$_POST['id_tutor'] : 0;
    $nombre_tutor_post = trim($_POST['nombre_tutor'] ?? '');
    $telefono_tutor_post = trim($_POST['telefono_tutor'] ?? '');

    function dividirNombre($nombre_completo) {
        $partes = explode(' ', trim($nombre_completo));
        $nombre = $partes[0];
        $apPaterno = isset($partes[1]) ? $partes[1] : ' ';
        $apMaterno = isset($partes[2]) ? implode(' ', array_slice($partes, 2)) : ' ';
        return [$nombre, $apPaterno, $apMaterno];
    }

    list($nombre_a, $apP_a, $apM_a) = dividirNombre($_POST['nombre_completo']);

    try {
        // Validación NSS duplicado
        if ($institucion_medica !== 'Ninguno' && $no_afiliacion !== 'N/A') {
            $stmt_nss = $conexion->prepare("SELECT id_alumno FROM ControlSport.alumno WHERE num_afiliacion = :nss LIMIT 1");
            $stmt_nss->execute([':nss' => $no_afiliacion]);
            if ($stmt_nss->rowCount() > 0) {
                header("Location: ../views/public/registro.php?ref=$id_entrenador&error=nss");
                exit;
            }
        }

        $conexion->beginTransaction();
        $id_tutor = 0;

        // 1. LÓGICA INTELIGENTE DEL TUTOR
        if ($id_tutor_post > 0) {
            // El usuario seleccionó un tutor existente del buscador
            $id_tutor = $id_tutor_post;
        } else {
            // El usuario no existía y llenó el Modal, insertamos uno nuevo
            list($nombre_t, $apP_t, $apM_t) = dividirNombre($nombre_tutor_post);
            
            $sql_tutor = "INSERT INTO ControlSport.tutor (nombre_tutor, apellido_P_t, apellido_M_t, telefono_tutor) 
                          VALUES (:nombre, :apP, :apM, :telefono) RETURNING id_tutor";
            $stmt_tutor = $conexion->prepare($sql_tutor);
            $stmt_tutor->execute([
                ':nombre' => $nombre_t,
                ':apP' => $apP_t,
                ':apM' => $apM_t,
                ':telefono' => $telefono_tutor_post
            ]);
            $id_tutor = $stmt_tutor->fetchColumn();
        }

        // 2. Insertar el Alumno vinculado al Tutor final
        $sql_alumno = "INSERT INTO ControlSport.alumno 
            (id_entrenador, id_tutor, curp, nombre_alumno, apellido_P_a, apellido_M_a, edad, peso, estatura, institucion_medica, num_afiliacion, domicilio, estado) 
            VALUES (:id_entrenador, :id_tutor, :curp, :nombre_a, :apP_a, :apM_a, :edad, :peso, :estatura, :inst_medica, :num_afil, :domicilio, 'Pendiente')";
        
        $stmt_alumno = $conexion->prepare($sql_alumno);
        $stmt_alumno->execute([
            ':id_entrenador' => $id_entrenador,
            ':id_tutor' => $id_tutor,
            ':curp' => $curp,
            ':nombre_a' => $nombre_a,
            ':apP_a' => $apP_a,
            ':apM_a' => $apM_a,
            ':edad' => $edad,
            ':peso' => $peso,
            ':estatura' => $estatura,
            ':inst_medica' => $institucion_medica,
            ':num_afil' => $no_afiliacion,
            ':domicilio' => $domicilio
        ]);

        $conexion->commit();
        header("Location: ../views/public/registro.php?ref=$id_entrenador&msg=success");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack(); 
        if ($e->getCode() == '23505') { 
            header("Location: ../views/public/registro.php?ref=$id_entrenador&error=curp");
        } else {
            header("Location: ../views/public/registro.php?ref=$id_entrenador&error=db");
        }
        exit;
    }
} else {
    header("Location: ../views/auth/login.php");
    exit;
}
?>