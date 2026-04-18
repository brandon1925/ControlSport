<!-- Archivo: views/auth/login.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ControlSport - Iniciar Sesión</title>
    <!-- Iconos Phosphor -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>

    <!-- Contenedor del Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="login-card">
        <div class="logo-box">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
        </div>
        <h1>ControlSport</h1>
        <p class="subtitle">Sistema de gestión deportiva</p>

        <form action="../../controllers/authController.php" method="POST">
            <div class="input-group">
                <label>Usuario</label>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <input type="text" name="usuario" placeholder="Ingresa tu usuario" required autocomplete="off">
                </div>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>
    </div>

    <!-- Modal Inactivo -->
    <div class="modal-overlay" id="modalInactivo">
        <div class="modal-card">
            <i class="ph ph-warning-circle modal-icon"></i>
            <h3 class="modal-title">Cuenta Desactivada</h3>
            <p class="modal-text">Tu usuario se encuentra actualmente inactivo en el sistema. Por favor, <b>contacta al administrador</b> para recuperar tu acceso.</p>
            <button class="btn-blue" onclick="cerrarModal()">Entendido</button>
        </div>
    </div>

    <!-- Script Lógica de Errores importado desde assets -->
    <script src="../../assets/js/login.js"></script>
    
    <script>
        // Leer variables GET con PHP para disparar eventos
        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'inactivo'): ?>
                document.addEventListener('DOMContentLoaded', abrirModal);
            <?php else: ?>
                document.addEventListener('DOMContentLoaded', () => mostrarToastError('Usuario o contraseña incorrectos.'));
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>