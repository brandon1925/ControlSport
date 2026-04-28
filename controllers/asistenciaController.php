<?php
// Archivo: controllers/asistenciaController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_grupo = (int)$_POST['id_grupo'];
    $asistencias_post = $_POST['asistencia'] ?? []; // Array de [id_alumno => 'Presente']
    $fecha_actual = date('Y-m-d');

    try {
        $conexion->beginTransaction();

        // 1. Verificamos si ya existe el pase de lista de HOY
        $stmt_check = $conexion->prepare("SELECT id_pase FROM ControlSport.pase_lista WHERE id_grupo = :id_grupo AND fecha = :fecha");
        $stmt_check->execute([':id_grupo' => $id_grupo, ':fecha' => $fecha_actual]);
        $id_pase_existente = $stmt_check->fetchColumn();

        // Obtenemos los alumnos actuales del grupo
        $stmt_alumnos = $conexion->prepare("SELECT id_alumno FROM ControlSport.alumno WHERE id_grupo = :id_grupo AND estado = 'Inscrito'");
        $stmt_alumnos->execute([':id_grupo' => $id_grupo]);
        $alumnos_grupo = $stmt_alumnos->fetchAll(PDO::FETCH_COLUMN);

        $total = count($alumnos_grupo);
        $presentes = 0;

        if ($id_pase_existente) {
            // ================= MODO EDICIÓN =================
            $id_pase = $id_pase_existente;
            
            // Actualizamos el estado de cada alumno
            $stmt_update = $conexion->prepare("UPDATE ControlSport.detalle_asistencia SET estado_asistencia = :estado WHERE id_pase = :id_pase AND id_alumno = :id_alumno");

            foreach ($alumnos_grupo as $id_alumno) {
                $estado = isset($asistencias_post[$id_alumno]) ? 'Presente' : 'Ausente';
                if ($estado === 'Presente') $presentes++;

                $stmt_update->execute([
                    ':id_pase' => $id_pase,
                    ':id_alumno' => $id_alumno,
                    ':estado' => $estado
                ]);
            }
            $tipo_msg = 'updated';

        } else {
            // ================= MODO NUEVO REGISTRO =================
            $sql_pase = "INSERT INTO ControlSport.pase_lista (id_grupo, fecha) VALUES (:id_grupo, :fecha) RETURNING id_pase";
            $stmt_pase = $conexion->prepare($sql_pase);
            $stmt_pase->execute([':id_grupo' => $id_grupo, ':fecha' => $fecha_actual]);
            $id_pase = $stmt_pase->fetchColumn();

            $sql_detalle = "INSERT INTO ControlSport.detalle_asistencia (id_pase, id_alumno, estado_asistencia) VALUES (:id_pase, :id_alumno, :estado)";
            $stmt_detalle = $conexion->prepare($sql_detalle);

            foreach ($alumnos_grupo as $id_alumno) {
                $estado = isset($asistencias_post[$id_alumno]) ? 'Presente' : 'Ausente';
                if ($estado === 'Presente') $presentes++;

                $stmt_detalle->execute([
                    ':id_pase' => $id_pase,
                    ':id_alumno' => $id_alumno,
                    ':estado' => $estado
                ]);
            }
            $tipo_msg = 'success';
        }

        $conexion->commit();
        
        // Redirigimos mandando el tipo de mensaje y las estadísticas
        header("Location: ../views/admin/asistencias.php?msg=$tipo_msg&p=$presentes&t=$total");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        // MEJORA: En lugar de morir en un pantallazo blanco, te devolvemos con el error
        $error_detalle = urlencode($e->getMessage());
        header("Location: ../views/admin/asistencias.php?error=db&detalle=$error_detalle");
        exit;
    }
}
?>