<?php
// Archivo: views/admin/historial.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Capturar filtros
$id_grupo = $_GET['id_grupo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Por defecto inicio de mes
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');      // Por defecto hoy

$resultados = [];

try {
    // 1. Obtener grupos del entrenador
    $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
    $stmt_g->execute([':id' => $id_usuario]);
    $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

    // 2. Si hay grupo, calcular estadísticas matemáticas
    if ($id_grupo) {
        $sql = "SELECT 
                    a.id_alumno, a.nombre_alumno, a.apellido_p_a,
                    COUNT(da.id_detalle) as total_pases,
                    COUNT(CASE WHEN da.estado_asistencia = 'Presente' THEN 1 END) as asistencias
                FROM ControlSport.alumno a
                JOIN ControlSport.pase_lista pl ON pl.id_grupo = a.id_grupo
                LEFT JOIN ControlSport.detalle_asistencia da ON da.id_pase = pl.id_pase AND da.id_alumno = a.id_alumno
                WHERE a.id_grupo = :id_grupo 
                  AND pl.fecha BETWEEN :f_inicio AND :f_fin
                GROUP BY a.id_alumno, a.nombre_alumno, a.apellido_p_a
                ORDER BY a.nombre_alumno ASC";
        
        $stmt_res = $conexion->prepare($sql);
        $stmt_res->execute([
            ':id_grupo' => $id_grupo,
            ':f_inicio' => $fecha_inicio,
            ':f_fin' => $fecha_fin
        ]);
        $resultados = $stmt_res->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error en historial: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<!-- TRUCO ANTI-CACHÉ: Obliga al navegador a cargar el CSS nuevo -->
<link rel="stylesheet" href="../../assets/css/historial.css?v=<?php echo time(); ?>">

<div class="page-wrapper">
    <div class="header-historial">
        <h2>Historial de Asistencias</h2>
    </div>

    <!-- Tarjeta de Filtros (Estilo Rendimiento) -->
    <div class="selection-card-full">
        <form action="" method="GET" id="formHistorial" class="filter-row">
            <div class="filter-group" style="flex: 2;">
                <label>Seleccionar Grupo</label>
                <select name="id_grupo" id="id_grupo" class="input-modern select-modern" required>
                    <option value="">Selecciona un grupo</option>
                    <?php foreach ($grupos as $g): ?>
                        <option value="<?php echo $g['id_grupo']; ?>" <?php echo ($id_grupo == $g['id_grupo']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['nombre_grupo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="input-modern" value="<?php echo $fecha_inicio; ?>" required>
            </div>
            
            <div class="filter-group">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="input-modern" value="<?php echo $fecha_fin; ?>" required>
            </div>
            
            <div class="filter-group btn-group">
                <button type="submit" class="btn-consultar" onclick="return validarFechas()">
                    <i class="ph ph-magnifying-glass"></i> Consultar
                </button>
            </div>
        </form>
    </div>

    <!-- Área de Resultados -->
    <?php if ($id_grupo): ?>
        <div class="history-results">
            <?php if (count($resultados) > 0): ?>
                <div class="history-header-labels">
                    <span>Nombre</span>
                    <span>Asistencias</span>
                    <span>Faltas</span>
                    <span>%</span>
                    <span>Estado</span>
                </div>

                <?php foreach ($resultados as $res): 
                    $faltas = $res['total_pases'] - $res['asistencias'];
                    $porcentaje = ($res['total_pases'] > 0) ? round(($res['asistencias'] / $res['total_pases']) * 100) : 0;
                    
                    // Lógica del semáforo visual (Solo punto)
                    $dot = 'dot-red-solid'; 
                    if ($porcentaje >= 90) { $dot = 'dot-green'; }
                    elseif ($porcentaje >= 80) { $dot = 'dot-yellow'; }
                    
                    $iniciales = strtoupper(substr($res['nombre_alumno'], 0, 1) . substr($res['apellido_p_a'], 0, 1));
                ?>
                <div class="history-item">
                    <div class="alumno-meta">
                        <div class="avatar-history"><?php echo $iniciales; ?></div>
                        <span class="name-text"><?php echo htmlspecialchars($res['nombre_alumno'] . ' ' . $res['apellido_p_a']); ?></span>
                    </div>
                    
                    <!-- Datos coloreados -->
                    <span class="stat-val text-green"><?php echo $res['asistencias']; ?></span>
                    <span class="stat-val text-red"><?php echo $faltas; ?></span>
                    <span class="stat-val text-dark"><?php echo $porcentaje; ?>%</span>
                    
                    <!-- Punto de Estado -->
                    <div>
                        <span class="dot-status <?php echo $dot; ?>"></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-card">
                    <div class="icon-circle"><i class="ph ph-calendar-x"></i></div>
                    <p>No se encontraron pases de lista en este grupo para las fechas seleccionadas.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Estado Vacío Inicial (Idéntico a Figma d31d9c / eb3d0d) -->
        <div class="empty-state-card">
            <div class="icon-circle"><i class="ph ph-info"></i></div>
            <p>Selecciona un grupo y un rango de fechas para generar el reporte de asistencia.</p>
        </div>
    <?php endif; ?>

</div>

<!-- Script de Validación Integrado -->
<script>
    function validarFechas() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        
        if (fechaInicio && fechaFin) {
            if (new Date(fechaInicio) > new Date(fechaFin)) {
                if(typeof mostrarToastGlobal === 'function') {
                    mostrarToastGlobal('Error: La fecha de inicio no puede ser posterior a la fecha final.');
                } else {
                    alert("La fecha de inicio no puede ser posterior a la fecha de fin.");
                }
                return false;
            }
        }
        return true;
    }
</script>

<?php require_once '../templates/footer.php'; ?>