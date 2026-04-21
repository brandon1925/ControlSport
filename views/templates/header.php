<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ControlSport - Panel</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="toast-global-container" id="toastGlobalContainer"></div>

    <script>
        function mostrarToastGlobal(mensaje) {
            const container = document.getElementById('toastGlobalContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-global';
            toast.innerHTML = `<i class="ph-fill ph-check-circle"></i> <p>${mensaje}</p>`;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3500);
        }
    </script>

    <?php 
    if (isset($_GET['login']) && $_GET['login'] == 'success') {
        $nombre_user = isset($_SESSION['nombre_usuario']) ? explode(' ', trim($_SESSION['nombre_usuario']))[0] : '';
        echo "<script> document.addEventListener('DOMContentLoaded', () => mostrarToastGlobal('Inicio de sesión correcto. ¡Bienvenido $nombre_user!')); </script>";
    }
    ?>

    <button class="mobile-menu-toggle" id="mobileMenuBtn">
        <i class="ph ph-list"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <div class="logo-area">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="ph ph-pulse"></i>
                <span>ControlSport</span>
            </div>
            <button class="close-sidebar-mobile" id="closeSidebarBtn"><i class="ph ph-x"></i></button>
        </div>
        
        <?php 
        $rol_sidebar = $_SESSION['rol_usuario'] ?? ''; 
        $pagina_actual = basename($_SERVER['PHP_SELF']);
        ?>
        
        <ul class="menu">
            <li class="<?= ($pagina_actual == 'inicio.php') ? 'active' : '' ?>">
                <a href="inicio.php"><i class="ph ph-house"></i> Inicio</a>
            </li>
            
            <?php if ($rol_sidebar === 'Entrenador'): ?>
                <li class="<?= ($pagina_actual == 'solicitudes.php') ? 'active' : '' ?>">
                    <a href="solicitudes.php"><i class="ph ph-file-text"></i> Solicitudes</a>
                </li>
                <li class="<?= ($pagina_actual == 'alumnos.php') ? 'active' : '' ?>">
                    <a href="alumnos.php"><i class="ph ph-users-three"></i> Alumnos</a>
                </li>
                <li class="<?= ($pagina_actual == 'asistencias.php') ? 'active' : '' ?>">
                    <a href="asistencias.php"><i class="ph ph-calendar-check"></i> Asistencias</a>
                </li>
                <li class="<?= ($pagina_actual == 'historial.php') ? 'active' : '' ?>">
                    <a href="historial.php"><i class="ph ph-chart-bar"></i> Historial</a>
                </li>
                <li class="<?= ($pagina_actual == 'rendimiento.php') ? 'active' : '' ?>">
                    <a href="rendimiento.php"><i class="ph ph-trend-up"></i> Rendimiento</a>
                </li>
                <li class="<?= ($pagina_actual == 'informes.php') ? 'active' : '' ?>">
                    <a href="informes.php"><i class="ph ph-chart-line-up"></i> Informes</a>
                </li>
            <?php endif; ?>

            <?php if ($rol_sidebar === 'Administrador'): ?>
                <li class="<?= ($pagina_actual == 'gestion.php') ? 'active' : '' ?>">
                    <a href="gestion.php"><i class="ph ph-shield-check"></i> Administración</a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="logout">
            <a href="../../controllers/logout.php"><i class="ph ph-sign-out"></i> Cerrar sesión</a>
        </div>
    </aside>

 <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if(mobileBtn && sidebar) {
                // Abrir menú
                mobileBtn.addEventListener('click', () => {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    mobileBtn.classList.add('oculto'); // <-- Esconde las 3 rayitas
                });

                // Cerrar menú con la X
                closeBtn.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    mobileBtn.classList.remove('oculto'); // <-- Muestra las 3 rayitas
                });

                // Cerrar menú tocando el fondo oscuro
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    mobileBtn.classList.remove('oculto'); // <-- Muestra las 3 rayitas
                });
            }
        });
    </script>

    <main class="main-content">