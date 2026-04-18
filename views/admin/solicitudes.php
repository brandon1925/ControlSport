<?php
// Archivo: views/admin/solicitudes.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$rol_usuario = $_SESSION['rol_usuario'];

try {
    // 1. LÓGICA DE GRUPOS POR DEFECTO PARA ENTRENADORES
    $grupos = [];
    if ($rol_usuario === 'Entrenador') {
        $stmt_g = $conexion->prepare("SELECT id_grupo, nombre_grupo FROM ControlSport.grupo WHERE id_entrenador = :id ORDER BY nombre_grupo ASC");
        $stmt_g->execute([':id' => $id_usuario]);
        $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

        if (count($grupos) == 0) {
            $grupos_defecto = [
                'Grupo A - Principiantes',
                'Grupo B - Intermedios',
                'Grupo C - Avanzados',
                'Grupo D - Competencia',
                'Grupo E - Élite'
            ];
            $stmt_insert = $conexion->prepare("INSERT INTO ControlSport.grupo (id_entrenador, nombre_grupo, limite_alumnos, cupo_actual) VALUES (:id, :nombre, 30, 0)");
            foreach ($grupos_defecto as $g_nombre) {
                $stmt_insert->execute([':id' => $id_usuario, ':nombre' => $g_nombre]);
            }
            $stmt_g->execute([':id' => $id_usuario]);
            $grupos = $stmt_g->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // 2. OBTENER LAS SOLICITUDES PENDIENTES (Añadimos todos los campos necesarios para el Modal de Detalles)
    if ($rol_usuario === 'Entrenador') {
        $sql = "SELECT a.id_alumno, a.nombre_alumno, a.apellido_p_a, a.apellido_m_a, a.edad, a.curp, a.peso, a.estatura, a.institucion_medica, a.num_afiliacion, a.domicilio, 
                       t.nombre_tutor, t.apellido_p_t, t.apellido_m_t, t.telefono_tutor 
                FROM ControlSport.alumno a
                JOIN ControlSport.tutor t ON a.id_tutor = t.id_tutor
                WHERE a.estado = 'Pendiente' AND a.id_entrenador = :id
                ORDER BY a.id_alumno DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id_usuario]);
    } else {
        $sql = "SELECT a.id_alumno, a.nombre_alumno, a.apellido_p_a, a.apellido_m_a, a.edad, a.curp, a.peso, a.estatura, a.institucion_medica, a.num_afiliacion, a.domicilio, 
                       t.nombre_tutor, t.apellido_p_t, t.apellido_m_t, t.telefono_tutor 
                FROM ControlSport.alumno a
                JOIN ControlSport.tutor t ON a.id_tutor = t.id_tutor
                WHERE a.estado = 'Pendiente'
                ORDER BY a.id_alumno DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }
    
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_pendientes = count($solicitudes);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<link rel="stylesheet" href="../../assets/css/solicitudes.css">
<style>
    /* Estilos específicos para el Modal de Detalles */
    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
    .detail-item { background: #F8FAFC; padding: 12px 15px; border-radius: 8px; border: 1px solid #E2E8F0; text-align: left; }
    .detail-item label { display: block; font-size: 11px; color: #94A3B8; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
    .detail-item p { margin: 0; color: #1E293B; font-size: 14px; font-weight: 600; }
    .section-title { margin-top: 25px; margin-bottom: 10px; color: #0047AB; font-size: 16px; font-weight: 600; border-bottom: 2px solid #E2E8F0; padding-bottom: 5px; text-align: left;}
</style>

<div class="page-wrapper">

    <div class="header-solicitudes">
        <div class="title-container">
            <h2>Solicitudes Pendientes</h2>
        </div>
        <?php if($total_pendientes > 0): ?>
            <div class="badge-pendientes"><?php echo $total_pendientes; ?> pendientes</div>
        <?php endif; ?>
    </div>

    <div id="toastActionContainer"></div>

    <?php if ($total_pendientes > 0): ?>
        <div class="solicitudes-list">
            
            <?php foreach ($solicitudes as $sol): 
                $nombre_completo_alumno = $sol['nombre_alumno'] . ' ' . $sol['apellido_p_a'];
                $nombre_completo_tutor = $sol['nombre_tutor'] . ' ' . $sol['apellido_p_t'];
            ?>
            <div class="solicitud-card">
                <div class="sol-info">
                    <h4><?php echo htmlspecialchars($nombre_completo_alumno); ?></h4>
                    <div class="sol-meta">
                        <span>Edad: <?php echo $sol['edad']; ?> años</span>
                        <span>Tutora: <?php echo htmlspecialchars($nombre_completo_tutor); ?></span>
                        <span>Tel: <?php echo htmlspecialchars($sol['telefono_tutor']); ?></span>
                    </div>
                </div>

                <div class="sol-actions">
                    <!-- Botón Ver Detalles (Pasa la info por JSON al dataset) -->
                    <button class="btn-outline-blue" onclick="abrirModalDetalles(this)" data-info='<?php echo htmlspecialchars(json_encode($sol), ENT_QUOTES, 'UTF-8'); ?>'>
                        <i class="ph ph-eye"></i> Ver detalles
                    </button>

                    <?php if ($rol_usuario === 'Entrenador'): ?>
                        <form id="form_aprobar_<?php echo $sol['id_alumno']; ?>" action="../../controllers/solicitudController.php" method="POST" style="display:flex; gap:12px; margin:0;">
                            <input type="hidden" name="accion" value="aprobar">
                            <input type="hidden" name="id_alumno" value="<?php echo $sol['id_alumno']; ?>">
                            <input type="hidden" name="nombre_alumno" value="<?php echo htmlspecialchars($nombre_completo_alumno); ?>">
                            
                            <select name="id_grupo" id="select_grupo_<?php echo $sol['id_alumno']; ?>" class="select-grupo">
                                <option value="" disabled selected>Asignar Grupo</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?php echo $g['id_grupo']; ?>"><?php echo htmlspecialchars($g['nombre_grupo']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn-orange-solid" onclick="validarAprobacion(<?php echo $sol['id_alumno']; ?>, '<?php echo htmlspecialchars($nombre_completo_alumno); ?>')">
                                <i class="ph ph-check"></i> Aprobar
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Botón Rechazar (Abre modal de confirmación) -->
                    <button type="button" class="btn-outline-gray" onclick="abrirModalRechazar(<?php echo $sol['id_alumno']; ?>, '<?php echo htmlspecialchars($nombre_completo_alumno); ?>')">
                        <i class="ph ph-x"></i> Rechazar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="ph ph-file-dashed"></i>
            <h3>No hay solicitudes pendientes</h3>
            <p>Cuando un alumno llene el formulario de registro, aparecerá aquí.</p>
        </div>
    <?php endif; ?>

</div>

<!-- ================= MODALES ================= -->

<!-- Modal Ver Detalles -->
<div class="modal-overlay" id="modalDetalles">
    <div class="modal-card" style="max-width: 650px;">
        <div class="modal-header">
            <h3><i class="ph ph-identification-card" style="vertical-align: middle;"></i> Expediente de Solicitud</h3>
            <button class="close-btn" onclick="cerrarModalGeneral('modalDetalles')"><i class="ph ph-x"></i></button>
        </div>
        
        <div class="section-title">Datos del Alumno</div>
        <div class="details-grid">
            <div class="detail-item"><label>Nombre Completo</label><p id="det_nombre_alumno"></p></div>
            <div class="detail-item"><label>CURP</label><p id="det_curp"></p></div>
            <div class="detail-item"><label>Edad</label><p id="det_edad"></p></div>
            <div class="detail-item"><label>Peso y Estatura</label><p><span id="det_peso"></span> / <span id="det_estatura"></span></p></div>
            <div class="detail-item"><label>Institución Médica</label><p id="det_medico"></p></div>
            <div class="detail-item"><label>No. Afiliación</label><p id="det_afiliacion"></p></div>
            <div class="detail-item" style="grid-column: span 2;"><label>Domicilio</label><p id="det_domicilio"></p></div>
        </div>

        <div class="section-title">Datos del Tutor</div>
        <div class="details-grid">
            <div class="detail-item"><label>Nombre del Tutor</label><p id="det_tutor"></p></div>
            <div class="detail-item"><label>Teléfono</label><p id="det_telefono"></p></div>
        </div>
    </div>
</div>

<!-- Modal Rechazar (Confirmación) -->
<div class="modal-overlay" id="modalRechazar">
    <div class="modal-card modal-confirm">
        <i class="ph-fill ph-warning-circle modal-icon-alert" style="color: #EF4444;"></i>
        <h3>Rechazar Solicitud</h3>
        <p>¿Estás seguro de que deseas rechazar la solicitud de <b id="rechazar_nombre" style="color: var(--text-dark);"></b>? El alumno será notificado y esta acción no se puede deshacer.</p>
        
        <div class="modal-footer-dual">
            <button class="btn-cancel" onclick="cerrarModalGeneral('modalRechazar')">Cancelar</button>
            <a href="#" id="btnConfirmarRechazo" class="btn-red-solid" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="ph ph-trash"></i> Confirmar Rechazo
            </a>
        </div>
    </div>
</div>

<!-- ================= SCRIPTS ================= -->
<script src="../../assets/js/solicitudes.js"></script>
<script>
    // Funciones para manejo de Modales en esta vista
    function abrirModalGeneral(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function cerrarModalGeneral(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    // Llenar datos y abrir Modal Detalles
    function abrirModalDetalles(btnElement) {
        // Extraemos la información en JSON desde el atributo data-info
        const data = JSON.parse(btnElement.getAttribute('data-info'));
        
        document.getElementById('det_nombre_alumno').innerText = `${data.nombre_alumno} ${data.apellido_p_a} ${data.apellido_m_a}`;
        document.getElementById('det_curp').innerText = data.curp;
        document.getElementById('det_edad').innerText = `${data.edad} años`;
        document.getElementById('det_peso').innerText = `${data.peso} kg`;
        document.getElementById('det_estatura').innerText = `${data.estatura} cm`;
        document.getElementById('det_medico').innerText = data.institucion_medica;
        document.getElementById('det_afiliacion').innerText = data.num_afiliacion || 'No proporcionado';
        document.getElementById('det_domicilio').innerText = data.domicilio;

        document.getElementById('det_tutor').innerText = `${data.nombre_tutor} ${data.apellido_p_t} ${data.apellido_m_t}`;
        document.getElementById('det_telefono').innerText = data.telefono_tutor;

        abrirModalGeneral('modalDetalles');
    }

    // Llenar datos y abrir Modal Rechazar
    function abrirModalRechazar(idAlumno, nombreAlumno) {
        document.getElementById('rechazar_nombre').innerText = nombreAlumno;
        // Asignamos la ruta al botón rojo de confirmar
        document.getElementById('btnConfirmarRechazo').href = `../../controllers/solicitudController.php?accion=rechazar&id=${idAlumno}&nombre=${encodeURIComponent(nombreAlumno)}`;
        abrirModalGeneral('modalRechazar');
    }

    // Detonadores de Toasts post-recarga
    <?php if(isset($_GET['msg'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if($_GET['msg'] == 'aprobado'): ?>
                mostrarToastPantalla('exito', 'Solicitud aprobada: <?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>');
            <?php elseif($_GET['msg'] == 'rechazado'): ?>
                mostrarToastPantalla('error', 'Solicitud rechazada: <?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>');
            <?php endif; ?>
        });
    <?php endif; ?>
</script>

<?php require_once '../templates/footer.php'; ?>