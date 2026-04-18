<?php
// Archivo: index.php (en la raíz de la carpeta ControlSport)

// Iniciamos la sesión para saber si el usuario ya entró antes
session_start();

// Si no hay una sesión activa, lo mandamos a la vista del Login
if (!isset($_SESSION['id_entrenador'])) {
    header('Location: views/auth/login.php');
    exit;
} else {
    // Si ya inició sesión, lo mandaremos a la interfaz 8 (Gestión) que haremos después
    header('Location: views/admin/gestion_staff.php');
    exit;
}
?>