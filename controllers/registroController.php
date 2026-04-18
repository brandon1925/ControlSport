<?php
// Archivo: controllers/registroController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibir datos básicos
    $id_entrenador = (int)$_POST['id_entrenador'];
    $edad = (int)$_POST['edad'];
    $curp = trim(strtoupper($_POST['curp']));
    $peso = (float)$_POST['peso'];
    $estatura = (float)$_POST['estatura'];
    $institucion_medica = trim($_POST['institucion_medica']);
    $no_afiliacion = trim($_POST['no_afiliacion']);
    $domicilio = trim($_POST['domicilio']);
    $telefono_tutor = trim($_POST['telefono_tutor']);

    // Función simple para dividir el nombre de un solo input en 3 variables para la base de datos
    function dividirNombre($nombre_completo) {
        $partes = explode(' ', trim($nombre_completo));
        $nombre = $partes[0];
        $apPaterno = isset($partes[1]) ? $partes[1] : ' ';
        $apMaterno = isset($partes[2]) ? implode(' ', array_slice($partes, 2)) : ' ';
        return [$nombre, $apPaterno, $apMaterno];
    }

    list($nombre_a, $apP_a, $apM_a) = dividirNombre($_POST['nombre_completo']);
    list($nombre_t, $apP_t, $apM_t) = dividirNombre($_POST['nombre_tutor']);

    try {
        // Iniciamos la transacción (Si falla el alumno, no se guarda el tutor)
        $conexion->beginTransaction();

        // 1. Insertar el Tutor y obtener su ID generado usando RETURNING (Específico de PostgreSQL)
        $sql_tutor = "INSERT INTO ControlSport.tutor (nombre_tutor, apellido_P_t, apellido_M_t, telefono_tutor) 
                      VALUES (:nombre, :apP, :apM, :telefono) RETURNING id_tutor";
        $stmt_tutor = $conexion->prepare($sql_tutor);
        $stmt_tutor->execute([
            ':nombre' => $nombre_t,
            ':apP' => $apP_t,
            ':apM' => $apM_t,
            ':telefono' => $telefono_tutor
        ]);
        $id_tutor = $stmt_tutor->fetchColumn();

        // 2. Insertar el Alumno (Estado 'Pendiente' por defecto en la BD)
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

        // Guardamos todos los cambios
        $conexion->commit();

        // Redirigimos de vuelta enviando "msg=success"
        header("Location: ../views/public/registro.php?ref=$id_entrenador&msg=success");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack(); // Cancelamos todo si hay error
        
        // Verificar si fue error de CURP duplicada (código SQLSTATE 23505)
        if ($e->getCode() == '23505') { 
            // Redirige mandando la variable de error específica del CURP
            header("Location: ../views/public/registro.php?ref=$id_entrenador&error=curp");
        } else {
            // Para cualquier otro error genérico de base de datos
            header("Location: ../views/public/registro.php?ref=$id_entrenador&error=db");
        }
        exit;
    }
} else {
    header("Location: ../views/auth/login.php");
    exit;
}
?>