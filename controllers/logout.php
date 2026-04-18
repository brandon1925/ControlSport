<?php
// Archivo: controllers/logout.php
session_start();
session_destroy(); // Destruye todas las variables de sesión
header("Location: ../views/auth/login.php");
exit;
?>