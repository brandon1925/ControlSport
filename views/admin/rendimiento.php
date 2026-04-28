<?php
// Archivo: views/admin/rendimiento.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Calculamos la fecha exacta hoy usando PHP (que ya está configurado en America/Mexico_City)
$fecha_hoy = date('Y-m-d');

try {
    $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
    $stmt_g->execute([':id' => $id_usuario]);
    $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

    // NUEVA LÓGICA: Consultar específicamente el estado de asistencia con la fecha de PHP, no la de SQL
    $stmt_a = $conexion->prepare("
        SELECT a.id_alumno, a.nombre_alumno, a.apellido_p_a, a.id_grupo,
               (SELECT da.estado_asistencia 
                FROM ControlSport.detalle_asistencia da 
                JOIN ControlSport.pase_lista pl ON da.id_pase = pl.id_pase 
                WHERE da.id_alumno = a.id_alumno AND pl.fecha = :fecha_hoy 
                LIMIT 1) as asistencia_hoy,
               (SELECT COUNT(id_evaluacion) FROM ControlSport.evaluacion_rendimiento er WHERE er.id_alumno = a.id_alumno AND er.fecha_evaluacion = :fecha_hoy) as evaluado_hoy
        FROM ControlSport.alumno a 
        WHERE a.estado = 'Inscrito' AND a.id_entrenador = :id
    ");
    $stmt_a->execute([':id' => $id_usuario, ':fecha_hoy' => $fecha_hoy]);
    $alumnos_total = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<link rel="stylesheet" href="../../assets/css/rendimiento.css">

<div class="page-wrapper">
    <div class="header-rendimiento">
        <h2>Evaluación de Rendimiento</h2>
    </div>

    <form action="../../controllers/rendimientoController.php" method="POST">
        <!-- Tarjeta de Selección Principal -->
        <div class="selection-card-full">
            <div class="selection-group">
                <label>Seleccionar Grupo *</label>
                <select id="selectGrupo" class="select-filtro-grupo">
                    <option value="">Selecciona un grupo</option>
                    <?php foreach ($grupos as $g): ?>
                        <option value="<?php echo $g['id_grupo']; ?>"><?php echo htmlspecialchars($g['nombre_grupo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="selection-group">
                <label>Seleccionar Alumno *</label>
                <select id="selectAlumno" name="id_alumno" class="select-filtro-grupo" disabled required>
                    <option value="">Selecciona un grupo primero</option>
                </select>
            </div>
        </div>

        <!-- Alerta 1: Aún no se ha pasado lista HOY -->
        <div id="alertaNoLista" class="banner-warning banner-red" style="display: none;">
            <i class="ph-fill ph-warning-circle"></i>
            <div>
                <h4>Evaluación Bloqueada</h4>
                <p>Aún no se ha pasado lista el día de hoy para este grupo o alumno.</p>
            </div>
        </div>

        <!-- Alerta 2: El alumno tiene falta HOY (Requerimiento 1 adaptado) -->
        <div id="alertaAsistencia" class="banner-warning banner-red" style="display: none;">
            <i class="ph-fill ph-warning-circle"></i>
            <div>
                <h4>Evaluación Bloqueada</h4>
                <p>El alumno no tiene asistencias registradas el día de hoy (Ausente).</p>
            </div>
        </div>
        
        <!-- Alerta 3: Ya fue evaluado HOY -->
        <div id="alertaEvaluado" class="banner-warning banner-red" style="display: none;">
            <i class="ph-fill ph-check-circle" style="color: #10B981;"></i>
            <div>
                <h4 style="color: #065F46;">Evaluación Completada</h4>
                <p style="color: #047857;">Este alumno ya ha sido evaluado el día de hoy.</p>
            </div>
        </div>

        <!-- Sección de Habilidades -->
        <div id="evalSection" style="display: none;">
            
            <!-- Alerta de Bajo Rendimiento (Requerimiento 2) -->
            <div id="alertaRendimiento" class="banner-warning banner-yellow" style="display: none;">
                <i class="ph-fill ph-warning-circle"></i>
                <div>
                    <h4>Alerta de Rendimiento</h4>
                    <p>Alumno con bajo rendimiento</p>
                </div>
            </div>

            <h3 class="section-title-alt">Habilidades Atléticas</h3>
            <p class="section-subtitle">Rango: 1 (mínimo) — 10 (máximo)</p>

            <div class="metrics-grid">
                <?php 
                $metricas = [
                    'velocidad' => 'Velocidad',
                    'fuerza' => 'Fuerza',
                    'resistencia' => 'Resistencia',
                    'agilidad' => 'Agilidad',
                    'coordinacion' => 'Coordinación',
                    'flexibilidad' => 'Flexibilidad'
                ];
                foreach ($metricas as $key => $label): ?>
                <div class="metric-card">
                    <div class="metric-header">
                        <span><?php echo $label; ?></span>
                        <span class="score-display" id="val_<?php echo $key; ?>">5</span>
                    </div>
                    <input type="range" name="<?php echo $key; ?>" class="range-slider" min="1" max="10" value="5" data-metric="<?php echo $key; ?>">
                    <!-- Números de guía 1 - 10 -->
                    <div class="slider-labels">
                        <span>1</span>
                        <span>10</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="notes-container">
                <label>Notas Adicionales</label>
                <textarea name="notas" class="notes-textarea" rows="4" placeholder="Observaciones adicionales..."></textarea>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-save-eval">Guardar Evaluación</button>
            </div>
        </div>
    </form>

    <!-- Opciones ocultas para JS -->
    <div id="temp-alumnos" style="display: none;">
        <?php foreach ($alumnos_total as $al): ?>
            <!-- Inyectamos los atributos de asistencia_hoy y evaluado_hoy -->
            <option class="src-alumno" value="<?php echo $al['id_alumno']; ?>" data-grupo="<?php echo $al['id_grupo']; ?>" data-asistencia-hoy="<?php echo $al['asistencia_hoy'] ?? ''; ?>" data-evaluado="<?php echo $al['evaluado_hoy']; ?>">
                <?php echo htmlspecialchars($al['nombre_alumno'] . ' ' . $al['apellido_p_a']); ?>
            </option>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function pintarFondoSlider(slider) {
        const min = slider.min || 1;
        const max = slider.max || 10;
        const valor = parseInt(slider.value);
        const porcentaje = ((valor - min) / (max - min)) * 100;
        
        let bg;
        if (valor <= 3) {
            bg = `linear-gradient(to right, #D69E85 ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        } else if (valor <= 7) {
            bg = `linear-gradient(to right, #FF5A00 ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        } else {
            bg = `linear-gradient(to right, #FF5A00 0%, #0047AB ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        }
        slider.style.background = bg;
    }

    function calcularPromedio() {
        const sliders = document.querySelectorAll('.range-slider');
        if (sliders.length === 0) return;
        let suma = 0;
        sliders.forEach(s => suma += parseInt(s.value));
        const promedio = suma / sliders.length;
        
        const alertaRend = document.getElementById('alertaRendimiento');
        if (alertaRend) {
            if (promedio < 6) {
                alertaRend.style.display = 'flex';
                const textoAlerta = alertaRend.querySelector('p');
                if (textoAlerta) textoAlerta.innerText = 'Alumno con bajo rendimiento';
            } else {
                alertaRend.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const selectGrupo = document.getElementById('selectGrupo');
        const selectAlumno = document.getElementById('selectAlumno');
        const evalSection = document.getElementById('evalSection');
        const alertaAsistencia = document.getElementById('alertaAsistencia');
        const alertaEvaluado = document.getElementById('alertaEvaluado');
        const alertaNoLista = document.getElementById('alertaNoLista');
        const btnGuardar = document.querySelector('.btn-save-eval'); 

        // Ocultar todas las alertas
        function ocultarAlertas() {
            if(alertaAsistencia) alertaAsistencia.style.display = 'none';
            if(alertaEvaluado) alertaEvaluado.style.display = 'none';
            if(alertaNoLista) alertaNoLista.style.display = 'none';
        }

        // Bloquear visualmente la sección de sliders en escala de grises
        function bloquearSeccionEvaluacion() {
            if (evalSection) {
                evalSection.style.display = 'block';
                evalSection.style.opacity = '0.4';
                evalSection.style.pointerEvents = 'none';
                evalSection.style.filter = 'grayscale(100%)';
            }
            if (btnGuardar) btnGuardar.disabled = true;
        }

        selectGrupo.addEventListener('change', function() {
            const idGrupo = this.value;
            selectAlumno.innerHTML = '<option value="">Selecciona un alumno</option>';
            if(evalSection) evalSection.style.display = 'none';
            ocultarAlertas();

            if (idGrupo) {
                selectAlumno.disabled = false;
                const source = document.querySelectorAll(`.src-alumno[data-grupo="${idGrupo}"]`);
                source.forEach(opt => {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    
                    const evaluadoHoy = parseInt(opt.getAttribute('data-evaluado') || 0);
                    const asistenciaHoy = opt.getAttribute('data-asistencia-hoy');

                    // Etiquetas visuales para el Select (Permitimos seleccionarlos para ver el bloqueo gris)
                    if (evaluadoHoy > 0) {
                        newOpt.text = opt.text + " (Evaluado hoy)";
                    } else if (!asistenciaHoy || asistenciaHoy === '') {
                        newOpt.text = opt.text + " (No se ha pasado lista)";
                    } else if (asistenciaHoy === 'Ausente') {
                        newOpt.text = opt.text + " (Ausente hoy)";
                    } else {
                        newOpt.text = opt.text;
                    }
                    
                    newOpt.setAttribute('data-asistencia-hoy', asistenciaHoy);
                    newOpt.setAttribute('data-evaluado', evaluadoHoy);
                    selectAlumno.appendChild(newOpt);
                });
            } else {
                selectAlumno.disabled = true;
            }
        });

        selectAlumno.addEventListener('change', function() {
            ocultarAlertas();

            if (this.value) {
                const selectedOpt = this.options[this.selectedIndex];
                const asistenciaHoy = selectedOpt.getAttribute('data-asistencia-hoy');
                const evaluadoHoy = parseInt(selectedOpt.getAttribute('data-evaluado') || 0);

                if (evaluadoHoy > 0) {
                    // BLOQUEO: Ya evaluado hoy
                    if (alertaEvaluado) alertaEvaluado.style.display = 'flex';
                    bloquearSeccionEvaluacion();

                } else if (!asistenciaHoy || asistenciaHoy === '') {
                    // BLOQUEO: No se ha pasado lista hoy
                    if (alertaNoLista) alertaNoLista.style.display = 'flex';
                    bloquearSeccionEvaluacion();

                } else if (asistenciaHoy === 'Ausente') {
                    // BLOQUEO: El alumno faltó hoy (Requerimiento 1)
                    if (alertaAsistencia) alertaAsistencia.style.display = 'flex';
                    bloquearSeccionEvaluacion();

                } else {
                    // ESTADO NORMAL: Listo para evaluar (Presente hoy)
                    if (evalSection) {
                        evalSection.style.display = 'block';
                        evalSection.style.opacity = '1';
                        evalSection.style.pointerEvents = 'auto';
                        evalSection.style.filter = 'none';
                    }
                    if (btnGuardar) btnGuardar.disabled = false;
                    
                    document.querySelectorAll('.range-slider').forEach(s => pintarFondoSlider(s));
                    calcularPromedio(); 
                }
            } else {
                if (evalSection) evalSection.style.display = 'none';
            }
        });

        const sliders = document.querySelectorAll('.range-slider');
        sliders.forEach(s => {
            pintarFondoSlider(s);
            s.addEventListener('input', function() {
                const key = this.getAttribute('data-metric');
                const textValue = document.getElementById(`val_${key}`);
                if(textValue) textValue.innerText = this.value;
                pintarFondoSlider(this);
                calcularPromedio();
            });
        });
    });
</script>

<?php require_once '../templates/footer.php'; ?>