<?php
// Archivo: controllers/buscarTutor.php
require_once '../config/conexion.php';

// Indicamos que responderemos en formato JSON
header('Content-Type: application/json');

// Recibimos el texto enviado por el buscador de JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$query = isset($data['q']) ? trim($data['q']) : '';

// Si escriben menos de 2 letras, no buscamos para ahorrar recursos
if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $busqueda = "%{$query}%";
    // Buscamos coincidencias en nombres o apellidos (Límite de 5 para no saturar la pantalla)
    $stmt = $conexion->prepare("
        SELECT id_tutor, nombre_tutor, apellido_p_t, apellido_m_t, telefono_tutor 
        FROM ControlSport.tutor 
        WHERE nombre_tutor ILIKE :q OR apellido_p_t ILIKE :q OR apellido_m_t ILIKE :q 
        LIMIT 5
    ");
    $stmt->execute([':q' => $busqueda]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tutores = [];
    foreach ($resultados as $row) {
        $tutores[] = [
            'id_tutor' => $row['id_tutor'],
            'nombre_completo' => trim($row['nombre_tutor'] . ' ' . $row['apellido_p_t'] . ' ' . $row['apellido_m_t']),
            'telefono' => $row['telefono_tutor']
        ];
    }

    echo json_encode(['success' => true, 'data' => $tutores]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'msg' => 'Error de base de datos']);
}
?>