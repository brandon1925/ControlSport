<?php
// Archivo: views/admin/alumnos.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$rol_usuario = $_SESSION['rol_usuario'];

try {
    // 1. OBTENER GRUPOS DEL ENTRENADOR
    $grupos = [];
    if ($rol_usuario === 'Entrenador') {
        $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
        $stmt_g->execute([':id' => $id_usuario]);
    } else {
        $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo ORDER BY nombre_grupo ASC");
        $stmt_g->execute();
    }
    $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

    // 2. OBTENER ALUMNOS INSCRITOS
    if ($rol_usuario === 'Entrenador') {
        $sql = "SELECT a.*, t.nombre_tutor, t.apellido_p_t, t.apellido_m_t, t.telefono_tutor 
                FROM ControlSport.alumno a
                JOIN ControlSport.tutor t ON a.id_tutor = t.id_tutor
                WHERE a.estado = 'Inscrito' AND a.id_entrenador = :id
                ORDER BY a.nombre_alumno ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id_usuario]);
    } else {
        $sql = "SELECT a.*, t.nombre_tutor, t.apellido_p_t, t.apellido_m_t, t.telefono_tutor 
                FROM ControlSport.alumno a
                JOIN ControlSport.tutor t ON a.id_tutor = t.id_tutor
                WHERE a.estado = 'Inscrito'
                ORDER BY a.nombre_alumno ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en BD: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<link rel="stylesheet" href="../../assets/css/alumnos.css">

<div class="page-wrapper">

    <div class="header-alumnos">
        <h2>Gestor de Alumnos</h2>
        <select id="filtroGrupo" class="select-filtro-grupo">
            <option value="" selected>Seleccionar Grupo</option>
            <?php foreach ($grupos as $g): ?>
                <option value="<?php echo $g['id_grupo']; ?>"><?php echo htmlspecialchars($g['nombre_grupo']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="toastActionContainer"></div>

    <!-- Empty State Inicial -->
    <div class="empty-state-alumnos" id="emptyState">
        <div class="icon-box-empty"><i class="ph ph-users"></i></div>
        <p>Selecciona un grupo para ver la lista de alumnos</p>
    </div>

    <!-- Listado de Alumnos (Oculto por defecto hasta elegir grupo) -->
    <div id="subtitleGrupo" class="subtitle-grupo">0 alumnos en el grupo</div>
    
    <div id="listadoAlumnos" style="display: none;">
        <?php foreach ($alumnos as $al): 
            $nombre_completo = $al['nombre_alumno'] . ' ' . $al['apellido_p_a'];
            $iniciales = strtoupper(substr($al['nombre_alumno'], 0, 1) . substr($al['apellido_p_a'], 0, 1));
        ?>
        <div class="alumno-card" data-grupo="<?php echo $al['id_grupo']; ?>">
            <div class="alumno-info">
                <div class="avatar-initials"><?php echo $iniciales; ?></div>
                <div class="alumno-details">
                    <h4><?php echo htmlspecialchars($nombre_completo); ?></h4>
                    <p>Edad: <?php echo $al['edad']; ?> años &nbsp;&nbsp;&nbsp; CURP: <?php echo $al['curp']; ?></p>
                </div>
            </div>
            
            <div class="alumno-actions">
                <button class="btn-action btn-update" onclick="abrirModalActualizar(this)" data-info='<?php echo htmlspecialchars(json_encode($al), ENT_QUOTES, 'UTF-8'); ?>'>
                    <i class="ph ph-pencil-simple"></i> Actualizar
                </button>
                <button class="btn-action btn-view" onclick="abrirModalExpediente(this)" data-info='<?php echo htmlspecialchars(json_encode($al), ENT_QUOTES, 'UTF-8'); ?>'>
                    <i class="ph ph-eye"></i> Ver Expediente
                </button>
                <button class="btn-action btn-delete" onclick="abrirModalBaja(<?php echo $al['id_alumno']; ?>, '<?php echo htmlspecialchars($nombre_completo); ?>')">
                    <i class="ph ph-trash"></i> Dar de baja
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- ================= MODALES ================= -->

<!-- 1. Modal Expediente -->
<div class="modal-overlay" id="modalExpediente">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header modal-header-simple" style="display:flex; justify-content:space-between;">
            <h3>Expediente del Alumno</h3>
            <button class="close-btn" onclick="cerrarModalGeneral('modalExpediente')"><i class="ph ph-x"></i></button>
        </div>
        <div class="detail-row"><label>Nombre</label><p id="exp_nombre"></p></div>
        <div class="detail-row"><label>Edad</label><p id="exp_edad"></p></div>
        <div class="detail-row"><label>CURP</label><p id="exp_curp"></p></div>
        <div class="detail-row"><label>Institución Médica</label><p id="exp_institucion"></p></div>
        <div class="detail-row"><label>Nombre del Tutor</label><p id="exp_tutor"></p></div>
        <div class="detail-row"><label>Teléfono</label><p id="exp_telefono"></p></div>
        <button class="btn-block" style="background:#0047AB; margin-top:20px;" onclick="cerrarModalGeneral('modalExpediente')">Cerrar</button>
    </div>
</div>

<!-- 2. Modal Actualizar -->
<div class="modal-overlay" id="modalActualizar">
    <div class="modal-card" style="max-width: 480px; text-align: left; max-height: 90vh; display: flex; flex-direction: column; padding: 25px;">
        <div class="modal-header modal-header-simple" style="display:flex; justify-content:space-between; flex-shrink: 0;">
            <h3>Actualizar Datos del Alumno</h3>
            <button class="close-btn" onclick="cerrarModalGeneral('modalActualizar')"><i class="ph ph-x"></i></button>
        </div>
        
        <form action="../../controllers/alumnoController.php" method="POST" style="display: flex; flex-direction: column; overflow: hidden;">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" id="upd_id_alumno" name="id_alumno">
            <input type="hidden" id="upd_id_tutor" name="id_tutor">

            <!-- Contenedor con scroll para evitar que se desborde la pantalla -->
            <div style="overflow-y: auto; padding-right: 10px; max-height: 55vh; margin-bottom: 10px;">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="upd_nombre" name="nombre_alumno" required>
                </div>
                <div class="form-group">
                    <label>Edad</label>
                    <input type="number" id="upd_edad" name="edad" required>
                </div>
                <div class="form-group">
                    <label>Institución Médica</label>
                    <input type="text" id="upd_institucion" name="institucion_medica" required>
                </div>
                <div class="form-group">
                    <label>Nombre del Tutor</label>
                    <input type="text" id="upd_tutor" name="nombre_tutor" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="upd_telefono" name="telefono_tutor" required>
                </div>
                <div class="form-group">
                    <label class="input-label">CURP <span>(no editable)</span></label>
                    <input type="text" id="upd_curp" class="input-readonly" readonly disabled>
                </div>
            </div>
            
            <div class="modal-footer-dual" style="margin-top: 15px; flex-shrink: 0;">
                <button type="button" class="btn-cancel" onclick="cerrarModalGeneral('modalActualizar')">Cancelar</button>
                <button type="submit" class="btn-orange-solid" style="flex:1; justify-content:center;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Confirmar Baja -->
<div class="modal-overlay" id="modalBaja">
    <div class="modal-card modal-confirm">
        <i class="ph-fill ph-warning-circle modal-icon-alert" style="color: #EF4444;"></i>
        <h3>Confirmar Baja</h3>
        <p>¿Está seguro de dar de baja a <b id="baja_nombre" style="color: var(--text-dark);"></b>? Esta acción lo ocultará de las listas activas.</p>
        
        <div class="modal-footer-dual">
            <button class="btn-cancel" onclick="cerrarModalGeneral('modalBaja')">Cancelar</button>
            <a href="#" id="btnConfirmarBaja" class="btn-red-solid" style="display: flex; align-items: center; justify-content: center;">
                Dar de baja
            </a>
        </div>
    </div>
</div>

<script src="../../assets/js/alumnos.js"></script>

<script>
    <?php if(isset($_GET['msg'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if($_GET['msg'] == 'actualizado'): ?>
                mostrarToastPantalla('exito', 'Datos actualizados correctamente');
            <?php elseif($_GET['msg'] == 'baja'): ?>
                mostrarToastPantalla('exito', 'El alumno ha sido dado de baja');
            <?php endif; ?>
        });
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            mostrarToastPantalla('error', 'Ocurrió un error en el servidor');
        });
    <?php endif; ?>
</script>

<?php require_once '../templates/footer.php'; ?>