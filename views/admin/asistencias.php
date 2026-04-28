<?php
// Archivo: views/admin/asistencias.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$fecha_hoy = date('Y-m-d');

try {
    // 1. Obtener grupos del entrenador
    $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
    $stmt_g->execute([':id' => $id_usuario]);
    $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener alumnos inscritos
    $sql = "SELECT id_alumno, nombre_alumno, apellido_p_a, id_grupo FROM ControlSport.alumno WHERE estado = 'Inscrito' AND id_entrenador = :id";
    $stmt_a = $conexion->prepare($sql);
    $stmt_a->execute([':id' => $id_usuario]);
    $alumnos = $stmt_a->fetchAll(PDO::FETCH_ASSOC);

    // 3. Consultar los detalles de asistencias tomadas HOY
    $stmt_asist_hoy = $conexion->prepare("
        SELECT pl.id_grupo, da.id_alumno, da.estado_asistencia 
        FROM ControlSport.pase_lista pl
        JOIN ControlSport.detalle_asistencia da ON pl.id_pase = da.id_pase
        WHERE pl.fecha = :fecha
    ");
    $stmt_asist_hoy->execute([':fecha' => $fecha_hoy]);
    $rows_hoy = $stmt_asist_hoy->fetchAll(PDO::FETCH_ASSOC);

    // Construimos un array tridimensional para JS: [id_grupo][id_alumno] = estado
    $asistencias_hoy = [];
    foreach($rows_hoy as $row) {
        $asistencias_hoy[$row['id_grupo']][$row['id_alumno']] = $row['estado_asistencia'];
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<link rel="stylesheet" href="../../assets/css/asistencias.css?v=<?php echo time(); ?>">

<div class="page-wrapper">
    <div class="header-asistencias">
        <h2>Pase de Lista</h2>
    </div>

    <div class="selector-card">
        <label>Seleccionar Grupo</label>
        <select id="selectGrupoAsistencia" class="select-modern" style="max-width: 400px;">
            <option value="" selected>Selecciona un grupo</option>
            <?php foreach ($grupos as $g): ?>
                <option value="<?php echo $g['id_grupo']; ?>"><?php echo htmlspecialchars($g['nombre_grupo']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <form action="../../controllers/asistenciaController.php" method="POST" id="formAsistencia">
        <input type="hidden" name="id_grupo" id="input_id_grupo">
        
        <div class="asistencia-container" id="asistenciaListado">
            
            <div id="bannerBloqueo" class="banner-warning" style="display: none;">
                <i class="ph-fill ph-warning-circle"></i>
                <div>
                    <h4>Asistencia ya registrada</h4>
                    <p>Ya se ha tomado la asistencia de este grupo el día de hoy. Puedes consultar los datos o editar el pase de lista.</p>
                </div>
            </div>

            <div class="asistencia-header">
                <span id="txtCantidadAlumnos" style="font-weight: 500;">0 alumnos</span>
                <div class="labels-header">
                    <span>Fecha: <?php echo date('d/m/Y'); ?></span>
                    <span style="color: #0047AB;">Falta / Asistencia</span>
                </div>
            </div>

            <div id="alumnosWrapper">
                <?php foreach ($alumnos as $al): 
                    $iniciales = strtoupper(substr($al['nombre_alumno'], 0, 1) . substr($al['apellido_p_a'], 0, 1));
                ?>
                <div class="alumno-asistencia-item" data-grupo="<?php echo $al['id_grupo']; ?>" data-alumno="<?php echo $al['id_alumno']; ?>" style="display: none;">
                    <div class="alumno-info-box">
                        <div class="avatar-asistencia"><?php echo $iniciales; ?></div>
                        <span class="alumno-nombre"><?php echo htmlspecialchars($al['nombre_alumno'] . ' ' . $al['apellido_p_a']); ?></span>
                    </div>
                    
                    <label class="asistencia-switch">
                        <input type="checkbox" id="chk_<?php echo $al['id_alumno']; ?>" name="asistencia[<?php echo $al['id_alumno']; ?>]" value="Presente">
                        <span class="asistencia-slider"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="footer-actions">
                <button type="button" id="btnEditar" class="btn-editar-lista" style="display: none;" onclick="abrirModalEditarLista()">
                    <i class="ph ph-pencil-simple"></i> Editar Asistencia
                </button>
                <button type="submit" id="btnGuardar" class="btn-guardar-asistencia">
                    Guardar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal Confirmación de Edición -->
<div class="modal-overlay" id="modalEditarLista">
    <div class="modal-card modal-confirm">
        <i class="ph-fill ph-warning-circle modal-icon-alert" style="color: #F59E0B;"></i>
        <h3 style="color: #D97706;">Editar Pase de Lista</h3>
        <p>El pase de lista de hoy ya fue guardado. ¿Estás seguro de que deseas desbloquear los controles para realizar modificaciones?</p>
        
        <div class="modal-footer-dual">
            <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarLista')">Cancelar</button>
            <button type="button" class="btn-orange" style="background:#F59E0B; width:100%; border-radius:8px; padding:12px; color:white; border:none; font-weight:bold; cursor:pointer;" onclick="confirmarEdicion()">Desbloquear Lista</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('selectGrupoAsistencia');
    const container = document.getElementById('asistenciaListado');
    const bannerBloqueo = document.getElementById('bannerBloqueo');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnEditar = document.getElementById('btnEditar');
    const inputGrupo = document.getElementById('input_id_grupo');
    const alumnos = document.querySelectorAll('.alumno-asistencia-item');
    const txtCount = document.getElementById('txtCantidadAlumnos');

    const asistenciasHoy = <?php echo json_encode($asistencias_hoy); ?>;

    select.addEventListener('change', function() {
        const grupoId = this.value;
        inputGrupo.value = grupoId;
        container.style.display = 'none';

        if (grupoId === "") return;

        const datosGuardados = asistenciasHoy[grupoId];

        if (datosGuardados) {
            bannerBloqueo.style.display = 'flex';
            btnGuardar.style.display = 'none';
            btnEditar.style.display = 'flex';
        } else {
            bannerBloqueo.style.display = 'none';
            btnGuardar.style.display = 'block';
            btnGuardar.innerText = 'Guardar';
            btnEditar.style.display = 'none';
        }

        container.style.display = 'block';
        let contador = 0;

        alumnos.forEach(item => {
            if (item.getAttribute('data-grupo') === grupoId) {
                item.style.display = 'flex';
                contador++;
                
                const idAlumno = item.getAttribute('data-alumno');
                const checkbox = document.getElementById('chk_' + idAlumno);
                
                if (datosGuardados) {
                    checkbox.checked = (datosGuardados[idAlumno] === 'Presente');
                    checkbox.disabled = true;
                    item.classList.add('readonly');
                } else {
                    checkbox.checked = false;
                    checkbox.disabled = false;
                    item.classList.remove('readonly');
                }
            } else {
                item.style.display = 'none';
            }
        });
        txtCount.innerText = `${contador} alumnos`;
    });

    // --- Manejo Inteligente de Errores y Éxitos ---
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'success'): ?>
            mostrarToastGlobal('Asistencia guardada: <?php echo $_GET['p']; ?>/<?php echo $_GET['t']; ?> presentes');
        <?php elseif($_GET['msg'] == 'updated'): ?>
            mostrarToastGlobal('Modificaciones guardadas correctamente. <?php echo $_GET['p']; ?>/<?php echo $_GET['t']; ?> presentes');
        <?php endif; ?>
    <?php endif; ?>

    // NUEVO: Captura de fallos en Base de Datos
    <?php if(isset($_GET['error']) && $_GET['error'] == 'db'): ?>
        mostrarToastGlobal('Error de BD: El registro no se pudo guardar.');
        console.error("Error PostgreSQL: <?php echo addslashes($_GET['detalle'] ?? ''); ?>");
        alert("Atención: Hubo un fallo en la base de datos al intentar guardar. Revisa la consola de tu navegador para más detalles técnicos (Probablemente necesites sincronizar las secuencias SQL).");
    <?php endif; ?>
});

function abrirModalEditarLista() {
    const modal = document.getElementById('modalEditarLista');
    if(!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
}

function confirmarEdicion() {
    cerrarModal('modalEditarLista');
    document.getElementById('bannerBloqueo').style.display = 'none';
    document.getElementById('btnEditar').style.display = 'none';
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.style.display = 'block';
    btnGuardar.innerText = 'Guardar Cambios';

    const grupoId = document.getElementById('selectGrupoAsistencia').value;
    document.querySelectorAll('.alumno-asistencia-item').forEach(item => {
        if (item.getAttribute('data-grupo') === grupoId) {
            const idAlumno = item.getAttribute('data-alumno');
            document.getElementById('chk_' + idAlumno).disabled = false;
            item.classList.remove('readonly');
        }
    });
}
</script>

<?php require_once '../templates/footer.php'; ?>