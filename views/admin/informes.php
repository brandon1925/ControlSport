<?php
// Archivo: views/admin/informes.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol_usuario'];
$id_grupo_seleccionado = $_GET['id_grupo'] ?? '';

$ranking = [];
$evaluaciones_completas = [];

try {
    // 1. Obtener grupos para el selector
    $sql_grupos = "SELECT id_grupo, nombre_grupo FROM ControlSport.grupo " . ($rol === 'Entrenador' ? "WHERE id_entrenador = $id_usuario" : "") . " ORDER BY nombre_grupo ASC";
    $grupos = $conexion->query($sql_grupos)->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay grupo seleccionado por GET, tomar el primero por defecto
    if (empty($id_grupo_seleccionado) && count($grupos) > 0) {
        $id_grupo_seleccionado = $grupos[0]['id_grupo'];
    }

    if (!empty($id_grupo_seleccionado)) {
        // 2. Obtener Alumnos del grupo
        $stmt_alumnos = $conexion->prepare("SELECT id_alumno, nombre_alumno, apellido_p_a FROM ControlSport.alumno WHERE id_grupo = :id_g AND estado = 'Inscrito'");
        $stmt_alumnos->execute([':id_g' => $id_grupo_seleccionado]);
        $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

        // 3. Obtener TODAS las evaluaciones del grupo (Para gráfica y cálculo de PTS)
        $stmt_eval = $conexion->prepare("SELECT er.* FROM ControlSport.evaluacion_rendimiento er JOIN ControlSport.alumno a ON er.id_alumno = a.id_alumno WHERE a.id_grupo = :id_g");
        $stmt_eval->execute([':id_g' => $id_grupo_seleccionado]);
        $evaluaciones_completas = $stmt_eval->fetchAll(PDO::FETCH_ASSOC);

        // 4. Obtener TODAS las asistencias del grupo
        $stmt_asist = $conexion->prepare("SELECT da.id_alumno, da.estado_asistencia FROM ControlSport.detalle_asistencia da JOIN ControlSport.alumno a ON da.id_alumno = a.id_alumno WHERE a.id_grupo = :id_g");
        $stmt_asist->execute([':id_g' => $id_grupo_seleccionado]);
        $asistencias_crudas = $stmt_asist->fetchAll(PDO::FETCH_ASSOC);

        // --- PROCESAR RANKING EN PHP ---
        foreach ($alumnos as $al) {
            $id = $al['id_alumno'];
            
            // Filtrar evals de este alumno
            $evals_alumno = array_filter($evaluaciones_completas, function($e) use ($id) { return $e['id_alumno'] == $id; });
            
            // Calcular Puntos (PTS) = Suma de todas las métricas * 10 para dar números grandes (ej. 960)
            $total_pts = 0;
            $sumas_habilidades = ['velocidad'=>0, 'fuerza'=>0, 'resistencia'=>0, 'agilidad'=>0, 'coordinacion'=>0, 'flexibilidad'=>0];
            
            foreach ($evals_alumno as $e) {
                $suma_eval = $e['velocidad'] + $e['fuerza'] + $e['resistencia'] + $e['agilidad'] + $e['coordinacion'] + $e['flexibilidad'];
                $total_pts += ($suma_eval * 10); // Multiplicador para gamificación

                // Acumular para saber su mejor habilidad
                foreach($sumas_habilidades as $key => $val) { $sumas_habilidades[$key] += $e[$key]; }
            }

            // Determinar mejor habilidad
            $mejor_habilidad = "Ninguna";
            if (count($evals_alumno) > 0) {
                $max_val = max($sumas_habilidades);
                $key_mejor = array_search($max_val, $sumas_habilidades);
                $nombres_hab = ['velocidad'=>'Velocidad', 'fuerza'=>'Fuerza', 'resistencia'=>'Resistencia', 'agilidad'=>'Agilidad', 'coordinacion'=>'Coordinación', 'flexibilidad'=>'Flexibilidad'];
                $mejor_habilidad = $nombres_hab[$key_mejor];
            }

            // Calcular Asistencia
            $asist_alumno = array_filter($asistencias_crudas, function($a) use ($id) { return $a['id_alumno'] == $id; });
            $total_clases = count($asist_alumno);
            $presentes = count(array_filter($asist_alumno, function($a) { return $a['estado_asistencia'] == 'Presente'; }));
            $pct_asistencia = ($total_clases > 0) ? round(($presentes / $total_clases) * 100) : 0;

            $ranking[] = [
                'id_alumno' => $id,
                'nombre_completo' => $al['nombre_alumno'] . ' ' . $al['apellido_p_a'],
                'iniciales' => strtoupper(substr($al['nombre_alumno'],0,1) . substr($al['apellido_p_a'],0,1)),
                'ejercicios' => count($evals_alumno),
                'total_pts' => $total_pts,
                'pct_asistencia' => $pct_asistencia,
                'mejor_habilidad' => $mejor_habilidad
            ];
        }

        // Ordenar Ranking de mayor a menor PTS
        usort($ranking, function($a, $b) { return $b['total_pts'] <=> $a['total_pts']; });
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<!-- Librería Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../../assets/css/informes.css">

<!-- Pasar datos de evaluaciones a JS para evitar llamadas AJAX lentas -->
<script>
    const datosEvaluacionesGlobal = <?php echo json_encode(array_values($evaluaciones_completas)); ?>;
</script>

<div class="page-wrapper">
    
    <div class="header-informes">
        <h2><i class="ph-fill ph-trophy"></i> Estadísticas y Reportes</h2>
        <form id="formFiltro" method="GET">
            <select name="id_grupo" class="select-filtro-grupo" onchange="cambiarGrupo()" style="min-width: 280px;">
                <option value="">Seleccionar Grupo</option>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?php echo $g['id_grupo']; ?>" <?php echo ($id_grupo_seleccionado == $g['id_grupo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g['nombre_grupo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- ================= VISTA MAESTRA (RANKING) ================= -->
    <div id="rankingView">
        <?php if (!empty($id_grupo_seleccionado) && count($ranking) > 0): ?>
            <p class="subtitle-ranking"><?php echo count($ranking); ?> alumnos en el grupo — Haz clic en un alumno para ver sus estadísticas detalladas</p>
            
            <div class="ranking-list">
                <?php foreach ($ranking as $index => $al): 
                    // Asignar medallas (Oro: 0, Plata: 1, Bronce: 2)
                    $medal_circle = 'medal-none-circle'; $medal_icon = 'ph-medal'; $badge_html = '';
                    if ($index === 0) { $medal_circle = 'medal-gold-circle'; $badge_html = '<span class="badge-medal badge-gold">Oro</span>'; }
                    elseif ($index === 1) { $medal_circle = 'medal-silver-circle'; $badge_html = '<span class="badge-medal badge-silver">Plata</span>'; }
                    elseif ($index === 2) { $medal_circle = 'medal-bronze-circle'; $badge_html = '<span class="badge-medal badge-bronze">Bronce</span>'; }
                ?>
                <div class="ranking-card" onclick="verDetalleAlumno(<?php echo $al['id_alumno']; ?>, '<?php echo htmlspecialchars($al['nombre_completo']); ?>')">
                    <div class="rank-info-left">
                        <div class="medal-circle <?php echo $medal_circle; ?>"><i class="ph-fill <?php echo $medal_icon; ?>"></i></div>
                        <div class="avatar-rank"><?php echo $al['iniciales']; ?></div>
                        <div>
                            <div class="student-details">
                                <h4><?php echo htmlspecialchars($al['nombre_completo']); ?></h4>
                                <?php echo $badge_html; ?>
                            </div>
                            <div class="student-stats">
                                <span><?php echo $al['ejercicios']; ?> evaluaciones</span>
                                <span>Asistencia: <?php echo $al['pct_asistencia']; ?>%</span>
                                <span>Mejor: <?php echo $al['mejor_habilidad']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="rank-info-right">
                        <div class="pts-number"><?php echo number_format($al['total_pts']); ?></div>
                        <div class="pts-label">Totales de PTS</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: white; padding: 50px; text-align: center; border-radius: 12px; color: #94A3B8;">
                <i class="ph ph-folder-open" style="font-size: 48px; margin-bottom: 10px;"></i>
                <p>No hay alumnos o datos suficientes en este grupo.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================= VISTA DETALLE (GRÁFICA Y NOTAS) ================= -->
    <div id="detalleAlumnoView">
        <button class="btn-back" onclick="volverRanking()"><i class="ph ph-arrow-left"></i> Volver al Ranking</button>
        
        <h3 style="color: #1E293B; font-size: 22px; margin-bottom: 20px;">Evolución de Rendimiento — <span id="nombreDetalle" style="color: #0047AB;"></span></h3>

        <div class="detail-grid">
            <!-- Columna Izquierda: Gráfica -->
            <div class="detail-card">
                <div class="chart-filters">
                    <button class="filter-btn active" data-skill="todas">Todas</button>
                    <button class="filter-btn" data-skill="velocidad">Velocidad</button>
                    <button class="filter-btn" data-skill="fuerza">Fuerza</button>
                    <button class="filter-btn" data-skill="resistencia">Resistencia</button>
                    <button class="filter-btn" data-skill="agilidad">Agilidad</button>
                    <button class="filter-btn" data-skill="coordinacion">Coordinación</button>
                    <button class="filter-btn" data-skill="flexibilidad">Flexibilidad</button>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartEvolucion"></canvas>
                </div>
            </div>

            <!-- Columna Derecha: Observaciones -->
            <div class="detail-card">
                <h3><i class="ph-fill ph-chat-teardrop-text"></i> Observaciones del Entrenador</h3>
                <div class="obs-list" id="obsList">
                    <!-- Rellenado por JS -->
                </div>
            </div>
        </div>
    </div>

</div>

<script src="../../assets/js/informes.js"></script>

<?php require_once '../templates/footer.php'; ?>