<?php
// Archivo: views/admin/rendimiento.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
    $stmt_g->execute([':id' => $id_usuario]);
    $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

    $stmt_a = $conexion->prepare("SELECT id_alumno, nombre_alumno, apellido_p_a, id_grupo FROM ControlSport.alumno WHERE estado = 'Inscrito' AND id_entrenador = :id");
    $stmt_a->execute([':id' => $id_usuario]);
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

        <!-- Sección de Habilidades -->
        <div id="evalSection" style="display: none;">
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
            <option class="src-alumno" value="<?php echo $al['id_alumno']; ?>" data-grupo="<?php echo $al['id_grupo']; ?>">
                <?php echo htmlspecialchars($al['nombre_alumno'] . ' ' . $al['apellido_p_a']); ?>
            </option>
        <?php endforeach; ?>
    </div>
</div>

<!-- Lógica de Rendimiento Integrada para control visual avanzado -->
<script>
    // Función que pinta la barra del slider con colores dinámicos
    function pintarFondoSlider(slider) {
        const min = slider.min || 1;
        const max = slider.max || 10;
        const valor = parseInt(slider.value);
        
        // Calcular porcentaje de llenado
        const porcentaje = ((valor - min) / (max - min)) * 100;
        
        let bg;
        if (valor <= 3) {
            // Gris-naranja (Tono más apagado para valores bajos)
            bg = `linear-gradient(to right, #D69E85 ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        } else if (valor <= 7) {
            // Naranja sólido (Valores medios)
            bg = `linear-gradient(to right, #FF5A00 ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        } else {
            // Naranja a Azul (Transición premium para valores altos)
            bg = `linear-gradient(to right, #FF5A00 0%, #0047AB ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
        }
        
        slider.style.background = bg;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const selectGrupo = document.getElementById('selectGrupo');
        const selectAlumno = document.getElementById('selectAlumno');
        const evalSection = document.getElementById('evalSection');

        // Filtrar alumnos por grupo
        selectGrupo.addEventListener('change', function() {
            const idGrupo = this.value;
            selectAlumno.innerHTML = '<option value="">Selecciona un alumno</option>';
            evalSection.style.display = 'none';

            if (idGrupo) {
                selectAlumno.disabled = false;
                const source = document.querySelectorAll(`.src-alumno[data-grupo="${idGrupo}"]`);
                source.forEach(opt => {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    selectAlumno.appendChild(newOpt);
                });
            } else {
                selectAlumno.disabled = true;
            }
        });

        // Mostrar evaluación al elegir alumno
        selectAlumno.addEventListener('change', function() {
            if (this.value) {
                evalSection.style.display = 'block';
                // Repintar todos los sliders al mostrar la sección
                document.querySelectorAll('.range-slider').forEach(s => pintarFondoSlider(s));
            } else {
                evalSection.style.display = 'none';
            }
        });

        // Eventos de los Sliders
        const sliders = document.querySelectorAll('.range-slider');
        
        sliders.forEach(s => {
            // Inicializar color al cargar
            pintarFondoSlider(s);

            // Actualizar color y texto al arrastrar
            s.addEventListener('input', function() {
                const key = this.getAttribute('data-metric');
                document.getElementById(`val_${key}`).innerText = this.value;
                pintarFondoSlider(this);
            });
        });
    });
</script>

<?php require_once '../templates/footer.php'; ?>