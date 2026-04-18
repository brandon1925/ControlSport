<?php
// Archivo: controllers/usuarioController.php
session_start();
require_once '../config/conexion.php';

// Validar que un Administrador esté haciendo los cambios
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] !== 'Administrador') {
    header("Location: ../views/auth/login.php");
    exit;
}

// 1. REGISTRAR NUEVO USUARIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'registrar') {
    
    $nombre = trim($_POST['nombre_completo']);
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    $confirmar_contrasena = trim($_POST['confirmar_contrasena'] ?? '');
    $rol = trim($_POST['rol']);
    $fecha_alta = date("Y-m-d");
    $creado_por = $_SESSION['id_usuario']; // Capturamos quién lo está creando

    // --- Validaciones de Seguridad (Backend) ---
    if (strlen($usuario) < 8 || strlen($usuario) > 30) {
        header("Location: ../views/admin/gestion.php?error=validacion");
        exit;
    }
    
    if ($contrasena !== $confirmar_contrasena) {
        header("Location: ../views/admin/gestion.php?error=mismatch");
        exit;
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!$#%&\/\*]).{8,30}$/', $contrasena)) {
        header("Location: ../views/admin/gestion.php?error=validacion");
        exit;
    }

    // === ENCRIPTACIÓN BCRYPT ===
    $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO ControlSport.usuarios (nombre_completo, usuario, contrasena, rol, estado, fecha_alta, creado_por) 
                VALUES (:nombre, :usuario, :contrasena, :rol, 'Activo', :fecha, :creado_por)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':contrasena', $contrasena_hash);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':fecha', $fecha_alta);
        $stmt->bindParam(':creado_por', $creado_por);
        $stmt->execute();
        
        header("Location: ../views/admin/gestion.php?msg=creado");
        exit;

    } catch (PDOException $e) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }
}

// 2. EDITAR USUARIO EXISTENTE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id_usuario = $_POST['id_usuario'];
    $nombre = trim($_POST['nombre_completo']);
    $usuario = trim($_POST['usuario']);
    $rol = trim($_POST['rol']);
    
    if (strlen($usuario) < 8 || strlen($usuario) > 30) {
        header("Location: ../views/admin/gestion.php?error=validacion");
        exit;
    }

    // Validación de contraseña solo si se está intentando cambiar
    if (!empty($_POST['contrasena'])) {
        $contrasena_check = trim($_POST['contrasena']);
        $confirmar_check = trim($_POST['confirmar_contrasena'] ?? '');
        
        if ($contrasena_check !== $confirmar_check) {
            header("Location: ../views/admin/gestion.php?error=mismatch");
            exit;
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!$#%&\/\*]).{8,30}$/', $contrasena_check)) {
            header("Location: ../views/admin/gestion.php?error=validacion");
            exit;
        }
    }

    // --- Validar Propiedad del Registro ---
    $stmt_check = $conexion->prepare("SELECT creado_por FROM ControlSport.usuarios WHERE id_usuario = :id");
    $stmt_check->execute([':id' => $id_usuario]);
    $propietario = $stmt_check->fetchColumn();

    // Se puede editar si fue creado por él, O si es su propio perfil
    if ($propietario != $_SESSION['id_usuario'] && $id_usuario != $_SESSION['id_usuario']) {
        header("Location: ../views/admin/gestion.php?error=permisos");
        exit;
    }

    try {
        if (!empty($_POST['contrasena'])) {
            $contrasena = trim($_POST['contrasena']);
            // === ENCRIPTACIÓN BCRYPT ===
            $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);
            
            $sql = "UPDATE ControlSport.usuarios SET nombre_completo = :nombre, usuario = :usuario, contrasena = :contrasena, rol = :rol WHERE id_usuario = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':contrasena', $contrasena_hash); // Guardamos el nuevo Hash
        } else {
            $sql = "UPDATE ControlSport.usuarios SET nombre_completo = :nombre, usuario = :usuario, rol = :rol WHERE id_usuario = :id";
            $stmt = $conexion->prepare($sql);
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();
        
        header("Location: ../views/admin/gestion.php?msg=editado");
        exit;
    } catch (PDOException $e) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }
}

// 3. ELIMINAR USUARIO
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    
    $id_usuario = $_GET['id'];

    if ($id_usuario == $_SESSION['id_usuario']) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }

    // --- Validar Propiedad ---
    $stmt_check = $conexion->prepare("SELECT creado_por FROM ControlSport.usuarios WHERE id_usuario = :id");
    $stmt_check->execute([':id' => $id_usuario]);
    $propietario = $stmt_check->fetchColumn();

    if ($propietario != $_SESSION['id_usuario']) {
        header("Location: ../views/admin/gestion.php?error=permisos");
        exit;
    }

    try {
        $sql = "DELETE FROM ControlSport.usuarios WHERE id_usuario = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();

        header("Location: ../views/admin/gestion.php?msg=eliminado");
        exit;
    } catch (PDOException $e) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }
}

// 4. CAMBIAR ESTADO
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'toggle' && isset($_GET['id']) && isset($_GET['estado'])) {
    
    $id_usuario = $_GET['id'];
    $nuevo_estado = ($_GET['estado'] == 'Activo') ? 'Inactivo' : 'Activo';

    if ($id_usuario == $_SESSION['id_usuario']) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }

    // --- Validar Propiedad ---
    $stmt_check = $conexion->prepare("SELECT creado_por FROM ControlSport.usuarios WHERE id_usuario = :id");
    $stmt_check->execute([':id' => $id_usuario]);
    $propietario = $stmt_check->fetchColumn();

    if ($propietario != $_SESSION['id_usuario']) {
        header("Location: ../views/admin/gestion.php?error=permisos");
        exit;
    }

    try {
        $sql = "UPDATE ControlSport.usuarios SET estado = :estado WHERE id_usuario = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();

        header("Location: ../views/admin/gestion.php?msg=estado_actualizado");
        exit;
    } catch (PDOException $e) {
        header("Location: ../views/admin/gestion.php?error=1");
        exit;
    }
}

header("Location: ../views/admin/gestion.php");
exit;
?>