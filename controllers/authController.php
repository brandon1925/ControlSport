<?php
// Archivo: controllers/authController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario = trim($_POST['usuario']);
    $password_ingresada = trim($_POST['password']);

    try {
        // 1. Buscamos al usuario en la base de datos
        $sql = "SELECT id_usuario, nombre_completo, contrasena, rol, estado FROM ControlSport.usuarios WHERE usuario = :usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Verificamos si la cuenta está inactiva
            if ($user['estado'] === 'Inactivo') {
                header("Location: ../views/auth/login.php?error=inactivo");
                exit;
            }

            // 3. Verificación de Contraseña (Soporta Bcrypt y actualización silenciosa)
            $login_exitoso = false;

            if (password_verify($password_ingresada, $user['contrasena'])) {
                // Caso A: La contraseña ya estaba encriptada con Bcrypt y es correcta
                $login_exitoso = true;
            } elseif ($user['contrasena'] === $password_ingresada) {
                // Caso B: (Actualización Silenciosa) Era una contraseña vieja sin encriptar (ej. el admin original)
                $login_exitoso = true;
                
                // Aprovechamos que inició sesión para encriptársela en la base de datos para el futuro
                $nuevo_hash = password_hash($password_ingresada, PASSWORD_BCRYPT);
                $sql_update = "UPDATE ControlSport.usuarios SET contrasena = :hash WHERE id_usuario = :id";
                $stmt_upd = $conexion->prepare($sql_update);
                $stmt_upd->execute([':hash' => $nuevo_hash, ':id' => $user['id_usuario']]);
            }

            // 4. Si el login es exitoso, creamos las variables de sesión
            if ($login_exitoso) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['rol_usuario'] = $user['rol'];
                $_SESSION['nombre_usuario'] = $user['nombre_completo'];

                // Redirigir al panel de inicio con un flag de éxito para el Toast
                header("Location: ../views/admin/inicio.php?login=success");
                exit;
            } else {
                // Contraseña incorrecta
                header("Location: ../views/auth/login.php?error=1");
                exit;
            }

        } else {
            // El usuario no existe
            header("Location: ../views/auth/login.php?error=1");
            exit;
        }

    } catch (PDOException $e) {
        die("Error de base de datos en login: " . $e->getMessage());
    }
} else {
    // Si entran directamente al archivo sin POST
    header("Location: ../views/auth/login.php");
    exit;
}
?>