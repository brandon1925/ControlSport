<?php
// Archivo: controllers/consultaEstatus.php
require_once '../config/conexion.php';

// Le decimos al navegador que responderemos en formato JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibimos los datos enviados por JavaScript (Fetch API)
    $data = json_decode(file_get_contents('php://input'), true);
    $curp = isset($data['curp']) ? strtoupper(trim($data['curp'])) : '';

    if (empty($curp)) {
        echo json_encode(['success' => false, 'msg' => 'CURP vacía']);
        exit;
    }

    try {
        // Buscamos la CURP en la tabla de alumnos
        $stmt = $conexion->prepare("SELECT estado, nombre_alumno FROM ControlSport.alumno WHERE curp = :curp LIMIT 1");
        $stmt->execute([':curp' => $curp]);
        
        if ($stmt->rowCount() > 0) {
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
            // Si lo encuentra, enviamos el estado real
            echo json_encode([
                'success' => true, 
                'estado' => $alumno['estado'],
                'nombre' => $alumno['nombre_alumno']
            ]);
        } else {
            // Si no lo encuentra (no existe o fue rechazado y borrado)
            echo json_encode(['success' => false, 'msg' => 'no_encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'msg' => 'error_bd']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Metodo no permitido']);
}
?>