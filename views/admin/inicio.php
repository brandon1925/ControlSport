<?php
// Archivo: views/admin/inicio.php
session_start();
require_once '../../config/conexion.php';

// Validar inicio de sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$rol_usuario = $_SESSION['rol_usuario'];
$id_usuario = $_SESSION['id_usuario'];

// --- CONSULTAS A LA BASE DE DATOS SEGÚN EL ROL ---
if ($rol_usuario === 'Administrador') {
    // 1. Lógica para el Administrador Global
    try {
        $stmt_activos = $conexion->query("SELECT COUNT(*) FROM ControlSport.usuarios WHERE rol = 'Entrenador' AND estado = 'Activo'");
        $entrenadores_activos = $stmt_activos->fetchColumn();

        $stmt_inactivos = $conexion->query("SELECT COUNT(*) FROM ControlSport.usuarios WHERE rol = 'Entrenador' AND estado = 'Inactivo'");
        $entrenadores_inactivos = $stmt_inactivos->fetchColumn();
        
        $stmt_alum = $conexion->query("SELECT COUNT(*) FROM ControlSport.alumno");
        $total_alum_admin = $stmt_alum->fetchColumn();
        
        $stmt_grupos = $conexion->query("SELECT COUNT(*) FROM ControlSport.grupo");
        $total_grupos_admin = $stmt_grupos->fetchColumn();
    } catch (PDOException $e) {
        $entrenadores_activos = $entrenadores_inactivos = $total_alum_admin = $total_grupos_admin = 0;
    }
} else {
    // 2. Lógica para el Entrenador (Solo ve lo suyo)
    try {
        // Solicitudes Pendientes (Aspirantes nuevos asignados a este entrenador)
        $stmt_sol = $conexion->prepare("SELECT COUNT(*) FROM ControlSport.alumno WHERE estado = 'Pendiente' AND id_entrenador = :id");
        $stmt_sol->execute([':id' => $id_usuario]);
        $sol_pendientes = $stmt_sol->fetchColumn();

        // Alumnos Activos / Inscritos
        $stmt_act = $conexion->prepare("SELECT COUNT(*) FROM ControlSport.alumno WHERE estado = 'Inscrito' AND id_entrenador = :id");
        $stmt_act->execute([':id' => $id_usuario]);
        $alum_activos = $stmt_act->fetchColumn();

        // Evaluaciones de Hoy
        $stmt_eval = $conexion->prepare("SELECT COUNT(*) FROM ControlSport.evaluacion_rendimiento er JOIN ControlSport.alumno a ON er.id_alumno = a.id_alumno WHERE er.fecha_evaluacion = CURRENT_DATE AND a.id_entrenador = :id");
        $stmt_eval->execute([':id' => $id_usuario]);
        $eval_hoy = $stmt_eval->fetchColumn();
        
        // Promedio de Asistencia (Cálculo real matemático de sus alumnos)
        $stmt_asist = $conexion->prepare("
            SELECT 
                COUNT(CASE WHEN da.estado_asistencia = 'Presente' THEN 1 END) AS presentes,
                COUNT(da.id_detalle) AS total
            FROM ControlSport.detalle_asistencia da
            JOIN ControlSport.alumno a ON da.id_alumno = a.id_alumno
            WHERE a.id_entrenador = :id
        ");
        $stmt_asist->execute([':id' => $id_usuario]);
        $data_asist = $stmt_asist->fetch(PDO::FETCH_ASSOC);

        if ($data_asist['total'] > 0) {
            $promedio = round(($data_asist['presentes'] / $data_asist['total']) * 100);
            $asistencia_prom = $promedio . "%";
        } else {
            $asistencia_prom = "0%"; // Si no hay clases aún, muestra 0%
        }
        
    } catch (PDOException $e) {
        $sol_pendientes = $alum_activos = $eval_hoy = 0;
        $asistencia_prom = "0%";
    }
}

// 1. INCLUIR EL HEADER Y SIDEBAR
require_once '../templates/header.php'; 
?>

<!-- Importar Estilos Específicos de Inicio -->
<link rel="stylesheet" href="../../assets/css/inicio.css">

<div class="page-wrapper">

    <div class="header-actions">
        <div class="page-title">
            <i class="ph ph-squares-four"></i> Panel de Control
        </div>
        
        <?php if($rol_usuario === 'Entrenador'): ?>
            <!-- Botón exclusivo para Entrenadores -->
            <button class="btn-orange" onclick="copiarEnlaceRegistro()">
                <!-- <i class="ph ph-link"></i> Generar enlace de inscripción -->
            </button>
        <?php endif; ?>
    </div>

    <!-- Grid de Tarjetas (Dashboard) -->
    <div class="dashboard-grid">
        
        <?php if($rol_usuario === 'Administrador'): ?>
            
            <!-- VISTA ADMINISTRADOR -->
            <div class="stat-card card-green">
                <div class="stat-icon"><i class="ph ph-user-circle-check"></i></div>
                <div class="stat-info">
                    <h4>Entrenadores Activos</h4>
                    <h2><?= $entrenadores_activos ?></h2>
                </div>
            </div>

            <div class="stat-card card-orange">
                <div class="stat-icon"><i class="ph ph-user-circle-minus"></i></div>
                <div class="stat-info">
                    <h4>Entrenadores Inactivos</h4>
                    <h2><?= $entrenadores_inactivos ?></h2>
                </div>
            </div>

            <div class="stat-card card-blue">
                <div class="stat-icon"><i class="ph ph-users"></i></div>
                <div class="stat-info">
                    <h4>Alumnos Totales</h4>
                    <h2><?= $total_alum_admin ?></h2>
                </div>
            </div>

            <div class="stat-card card-purple">
                <div class="stat-icon"><i class="ph ph-users-three"></i></div>
                <div class="stat-info">
                    <h4>Grupos Creados</h4>
                    <h2><?= $total_grupos_admin ?></h2>
                </div>
            </div>

        <?php else: ?>

            <!-- VISTA ENTRENADOR (Idéntico a imagen Figma) -->
            <div class="stat-card card-orange">
                <div class="stat-icon"><i class="ph ph-file-text"></i></div>
                <div class="stat-info">
                    <h4>Solicitudes Pendientes</h4>
                    <h2><?= $sol_pendientes ?></h2>
                </div>
            </div>

            <div class="stat-card card-blue">
                <div class="stat-icon"><i class="ph ph-users"></i></div>
                <div class="stat-info">
                    <h4>Alumnos Activos</h4>
                    <h2><?= $alum_activos ?></h2>
                </div>
            </div>

            <div class="stat-card card-green">
                <div class="stat-icon"><i class="ph ph-clipboard-text"></i></div>
                <div class="stat-info">
                    <h4>Asistencia Promedio</h4>
                    <h2><?= $asistencia_prom ?></h2>
                </div>
            </div>

            <div class="stat-card card-purple">
                <div class="stat-icon"><i class="ph ph-trend-up"></i></div>
                <div class="stat-info">
                    <h4>Evaluaciones Hoy</h4>
                    <h2><?= $eval_hoy ?></h2>
                </div>
            </div>

        <?php endif; ?>

    </div>

</div>

<!-- Pasar ID de PHP a JS -->
<script>
    const ID_USUARIO_ACTUAL = <?php echo $_SESSION['id_usuario']; ?>;
</script>

<?php if($rol_usuario === 'Entrenador'): ?>
    <!-- Cargar Script de Copiar Enlace SOLO para Entrenadores -->
    <script src="../../assets/js/inicio.js"></script>
<?php endif; ?>

<?php 
// 2. INCLUIR EL FOOTER
require_once '../templates/footer.php'; 
?>