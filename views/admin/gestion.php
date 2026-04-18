<?php
// Archivo: views/admin/gestion.php
session_start();
require_once '../../config/conexion.php';

// Validar inicio de sesión y rol de Administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] !== 'Administrador') {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario_sesion = $_SESSION['id_usuario'];

// Obtener la lista de usuarios (Filtrado por creador o a sí mismo)
try {
    $sql = "SELECT * FROM ControlSport.usuarios WHERE creado_por = :id OR id_usuario = :id ORDER BY id_usuario ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_usuario_sesion]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar usuarios: " . $e->getMessage());
}

require_once '../templates/header.php'; 
?>

<!-- SOLUCIÓN: Ocultar el ojito nativo de Edge/Chrome -->
<style>
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none;
    }
</style>

<div class="page-wrapper">
    <div class="header-actions">
        <div class="page-title">
            <i class="ph ph-shield-check"></i> Administración de Personal
        </div>
        <button class="btn-orange" onclick="abrirModal('modalAgregar')">
            <i class="ph ph-plus"></i> Nuevo Usuario
        </button>
    </div>

    <!-- Contenedor del Toast Local -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Usuario / Nombre</th>
                    <th>Rol</th>
                    <th>Fecha de Alta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user): 
                    $inicial = strtoupper(substr($user['nombre_completo'], 0, 1));
                    $is_active = ($user['estado'] === 'Activo');
                    $es_propio = ($user['id_usuario'] == $id_usuario_sesion);
                ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="avatar"><?php echo $inicial; ?></div>
                            <div>
                                <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($user['nombre_completo']); ?></div>
                                <div style="font-size: 12px; color: #94A3B8;">@<?php echo htmlspecialchars($user['usuario']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-rol"><?php echo htmlspecialchars($user['rol']); ?></span></td>
                    <td><?php echo date("d/m/Y", strtotime($user['fecha_alta'])); ?></td>
                    <td>
                        <?php if ($es_propio): ?>
                            <span class="badge-rol" style="background:#F1F5F9; color:#94A3B8;">No aplicable</span>
                        <?php else: ?>
                            <label class="switch">
                                <input type="checkbox" <?php echo $is_active ? 'checked' : ''; ?> 
                                       onchange="window.location.href='../../controllers/usuarioController.php?accion=toggle&id=<?php echo $user['id_usuario']; ?>&estado=<?php echo $user['estado']; ?>'">
                                <span class="slider"></span>
                            </label>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="action-btn" onclick="abrirModalVer(this)" data-info='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'>
                            <i class="ph ph-eye"></i>
                        </button>
                        <button class="action-btn" onclick="abrirModalEditar(this)" data-info='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'>
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                        <?php if (!$es_propio): ?>
                            <button class="action-btn delete" onclick="abrirModalEliminar(<?php echo $user['id_usuario']; ?>, '<?php echo htmlspecialchars($user['nombre_completo']); ?>')">
                                <i class="ph ph-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODALES ================= -->

<!-- Modal: Ver Detalles (OJO: Sin mostrar contraseña real por Bcrypt) -->
<div class="modal-overlay" id="modalVer">
    <div class="modal-card" style="max-width: 400px; text-align: left;">
        <div class="modal-header">
            <h3>Detalles del Usuario</h3>
            <button class="close-btn" type="button" onclick="cerrarModal('modalVer')"><i class="ph ph-x"></i></button>
        </div>
        <div class="info-user-header">
            <div class="info-avatar" id="ver_avatar"></div>
            <div class="info-name">
                <h4 id="ver_nombre"></h4>
                <span class="info-badge" id="ver_estado"></span>
            </div>
        </div>
        <div class="info-details">
            <p><strong>Usuario (Login):</strong> <span id="ver_usuario"></span></p>
            <p><strong>Contraseña:</strong> <span style="color: #10B981; font-weight: 600;"><i class="ph ph-lock-key"></i> Protegida (Bcrypt)</span></p>
            <p><strong>Rol en el Sistema:</strong> <span id="ver_rol"></span></p>
            <p><strong>Fecha de Alta:</strong> <span id="ver_fecha"></span></p>
        </div>
    </div>
</div>

<!-- 1. Modal: Agregar Nuevo -->
<div class="modal-overlay" id="modalAgregar">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Nuevo Entrenador / Usuario</h3>
            <button class="close-btn" type="button" onclick="cerrarModal('modalAgregar')"><i class="ph ph-x"></i></button>
        </div>
        <form action="../../controllers/usuarioController.php" method="POST">
            <input type="hidden" name="accion" value="registrar">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Usuario (Login)</label>
                <div style="position: relative;">
                    <input type="text" name="usuario" id="reg_usuario" required autocomplete="off" minlength="8" maxlength="30" style="padding-right: 40px; width: 100%; box-sizing: border-box;" oninput="validarUsuarioEnVivo('reg_usuario', 'status_reg_usuario')">
                    <i class="ph" id="status_reg_usuario" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                </div>
                <small id="hint_reg_usuario" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Mín. 8 caracteres y máx. 30 caracteres</small>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <div style="position: relative;">
                    <input type="password" name="contrasena" id="reg_pass" required minlength="8" maxlength="30" pattern="^(?=.*[A-Z])(?=.*[0-9])(?=.*[!$#%&/*]).{8,30}$" style="padding-right: 70px; width: 100%; box-sizing: border-box;" oninput="validarPasswordEnVivo('reg_pass', 'status_reg_pass')">
                    <i class="ph" id="status_reg_pass" style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                    <i class="ph ph-eye" id="toggle_reg_pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94A3B8; font-size: 20px;" onclick="togglePassword('reg_pass', 'toggle_reg_pass')"></i>
                </div>
                <small id="hint_reg_pass" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Mín. 8 caracteres, 1 mayúscula, 1 número y 1 especial (!$#%&/*)</small>
            </div>
            
            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <div style="position: relative;">
                    <input type="password" name="confirmar_contrasena" id="reg_pass_conf" required minlength="8" maxlength="30" style="padding-right: 70px; width: 100%; box-sizing: border-box;" oninput="validarCoincidenciaEnVivo('reg_pass', 'reg_pass_conf', 'status_reg_pass_conf')">
                    <i class="ph" id="status_reg_pass_conf" style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                    <i class="ph ph-eye" id="toggle_reg_pass_conf" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94A3B8; font-size: 20px;" onclick="togglePassword('reg_pass_conf', 'toggle_reg_pass_conf')"></i>
                </div>
                <small id="hint_reg_pass_conf" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Las contraseñas deben coincidir</small>
            </div>

            <div class="form-group">
                <label>Rol en el Sistema</label>
                <select name="rol" required>
                    <option value="" disabled selected>Seleccione un rol...</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Entrenador">Entrenador</option>
                </select>
            </div>
            <button type="submit" class="btn-block" style="background-color: #FF5A00; border: none; color: white; padding: 12px; border-radius: 8px; width: 100%; font-weight: bold; cursor: pointer; margin-top: 10px;">Agregar Nuevo Usuario</button>
        </form>
    </div>
</div>

<!-- 2. Modal: Editar -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Editar Usuario</h3>
            <button class="close-btn" type="button" onclick="cerrarModal('modalEditar')"><i class="ph ph-x"></i></button>
        </div>
        <form action="../../controllers/usuarioController.php" method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_usuario" id="edit_id">
            
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo" id="edit_nombre" required>
            </div>

            <div class="form-group">
                <label>Usuario (Login)</label>
                <div style="position: relative;">
                    <input type="text" name="usuario" id="edit_usuario" required minlength="8" maxlength="30" style="padding-right: 40px; width: 100%; box-sizing: border-box;" oninput="validarUsuarioEnVivo('edit_usuario', 'status_edit_usuario')">
                    <i class="ph" id="status_edit_usuario" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                </div>
                <small id="hint_edit_usuario" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Mín. 8 caracteres y máx. 30 caracteres</small>
            </div>
            
            <div class="form-group">
                <label>Nueva Contraseña <span style="font-size: 11px; color: #94A3B8;">(dejar vacío para no cambiar)</span></label>
                <div style="position: relative;">
                    <input type="password" name="contrasena" id="edit_pass" placeholder="••••••••" minlength="8" maxlength="30" pattern="^(?=.*[A-Z])(?=.*[0-9])(?=.*[!$#%&/*]).{8,30}$" style="padding-right: 70px; width: 100%; box-sizing: border-box;" oninput="validarPasswordEnVivo('edit_pass', 'status_edit_pass')">
                    <i class="ph" id="status_edit_pass" style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                    <i class="ph ph-eye" id="toggle_edit_pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94A3B8; font-size: 20px;" onclick="togglePassword('edit_pass', 'toggle_edit_pass')"></i>
                </div>
                <small id="hint_edit_pass" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Mín. 8 caracteres, 1 mayúscula, 1 número y 1 especial (!$#%&/*)</small>
            </div>
            
            <div class="form-group">
                <label>Confirmar Nueva Contraseña</label>
                <div style="position: relative;">
                    <input type="password" name="confirmar_contrasena" id="edit_pass_conf" placeholder="••••••••" minlength="8" maxlength="30" style="padding-right: 70px; width: 100%; box-sizing: border-box;" oninput="validarCoincidenciaEnVivo('edit_pass', 'edit_pass_conf', 'status_edit_pass_conf')">
                    <i class="ph" id="status_edit_pass_conf" style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); font-size: 20px; transition: 0.2s;"></i>
                    <i class="ph ph-eye" id="toggle_edit_pass_conf" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94A3B8; font-size: 20px;" onclick="togglePassword('edit_pass_conf', 'toggle_edit_pass_conf')"></i>
                </div>
                <small id="hint_edit_pass_conf" style="color: #94A3B8; font-size: 11px; display: block; margin-top: 5px; transition: 0.2s;">Las contraseñas deben coincidir</small>
            </div>

            <div class="form-group">
                <label>Rol en el Sistema</label>
                <select name="rol" id="edit_rol" required>
                    <option value="Administrador">Administrador</option>
                    <option value="Entrenador">Entrenador</option>
                </select>
            </div>
            <button type="submit" class="btn-block" style="background-color: #FF5A00; border: none; color: white; padding: 12px; border-radius: 8px; width: 100%; font-weight: bold; cursor: pointer; margin-top: 10px;">Guardar Cambios</button>
        </form>
    </div>
</div>

<!-- 3. Modal: Eliminar -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-card modal-confirm">
        <i class="ph-fill ph-warning-circle modal-icon-alert" style="color: #EF4444;"></i>
        <h3 style="color: #EF4444;">Eliminar Usuario</h3>
        <p>¿Estás seguro que deseas eliminar permanentemente a <b id="eliminar_nombre" style="color: var(--text-dark);"></b>?</p>
        <div class="modal-footer-dual">
            <button class="btn-cancel" onclick="cerrarModal('modalEliminar')">Cancelar</button>
            <a href="#" id="btnConfirmarEliminar" class="btn-red-solid" style="display: flex; align-items: center; justify-content: center;">Eliminar</a>
        </div>
    </div>
</div>

<script src="../../assets/js/gestion.js?v=<?php echo time(); ?>"></script>

<script>
const ID_USUARIO_ACTUAL = <?php echo $_SESSION['id_usuario']; ?>;

<?php 
if (isset($_GET['msg'])) {
    $mensaje = "";
    if ($_GET['msg'] == 'creado') $mensaje = "Usuario registrado correctamente";
    if ($_GET['msg'] == 'editado') $mensaje = "Datos del usuario actualizados";
    if ($_GET['msg'] == 'eliminado') $mensaje = "Usuario eliminado permanentemente";
    if ($_GET['msg'] == 'estado_actualizado') $mensaje = "Estado actualizado con éxito";
    
    echo "document.addEventListener('DOMContentLoaded', () => mostrarToast('exito', '$mensaje'));";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 'validacion') {
        echo "document.addEventListener('DOMContentLoaded', () => mostrarToast('error', 'Los datos no cumplen con los requisitos de seguridad.'));";
    } elseif ($_GET['error'] == 'mismatch') {
        echo "document.addEventListener('DOMContentLoaded', () => mostrarToast('error', 'Las contraseñas no coinciden.'));";
    } elseif ($_GET['error'] == 'permisos') {
        echo "document.addEventListener('DOMContentLoaded', () => mostrarToast('error', 'No tienes permisos para modificar a este usuario.'));";
    } else {
        echo "document.addEventListener('DOMContentLoaded', () => mostrarToast('error', 'Ocurrió un error al procesar la solicitud.'));";
    }
}
?>

// ================= FUNCIONES DE VALIDACIÓN EXTRA =================
function validarUsuarioEnVivo(inputId, statusIconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(statusIconId);
    const hint = document.getElementById('hint_' + inputId);
    
    if (input.value.length === 0) {
        icon.className = 'ph'; 
        if(hint) hint.style.color = '#94A3B8'; 
        return;
    }

    if (input.value.length >= 8 && input.value.length <= 30) {
        icon.className = 'ph-fill ph-check-circle';
        icon.style.color = '#22C55E'; 
        if(hint) hint.style.color = '#22C55E'; 
    } else {
        icon.className = 'ph-fill ph-x-circle';
        icon.style.color = '#EF4444'; 
        if(hint) hint.style.color = '#EF4444'; 
    }
}

function validarCoincidenciaEnVivo(passId, confId, statusIconId) {
    const pass = document.getElementById(passId).value;
    const conf = document.getElementById(confId).value;
    const icon = document.getElementById(statusIconId);
    const hint = document.getElementById('hint_' + confId);

    if (conf.length === 0) {
        icon.className = 'ph';
        if(hint) {
            hint.style.color = '#94A3B8';
            hint.innerText = 'Las contraseñas deben coincidir';
        }
        return;
    }

    if (pass === conf && pass.length > 0) {
        icon.className = 'ph-fill ph-check-circle';
        icon.style.color = '#22C55E';
        if(hint) {
            hint.style.color = '#22C55E';
            hint.innerText = '¡Las contraseñas coinciden!';
        }
    } else {
        icon.className = 'ph-fill ph-x-circle';
        icon.style.color = '#EF4444';
        if(hint) {
            hint.style.color = '#EF4444';
            hint.innerText = 'Las contraseñas no coinciden';
        }
    }
}

let funcionOriginalAbrirEditar;
if(typeof abrirModalEditar === 'function') {
    funcionOriginalAbrirEditar = abrirModalEditar;
    abrirModalEditar = function(btnElement) {
        funcionOriginalAbrirEditar(btnElement);
        validarUsuarioEnVivo('edit_usuario', 'status_edit_usuario');
        validarPasswordEnVivo('edit_pass', 'status_edit_pass');
        validarCoincidenciaEnVivo('edit_pass', 'edit_pass_conf', 'status_edit_pass_conf');
    }
}

// Actualizar la función abrirModalVer si ya existía para que funcione con el nuevo campo
let funcionOriginalAbrirVer;
if(typeof abrirModalVer === 'function') {
    funcionOriginalAbrirVer = abrirModalVer;
}
abrirModalVer = function(btnElement) {
    const data = JSON.parse(btnElement.getAttribute('data-info'));
    
    document.getElementById('ver_avatar').innerText = data.nombre_completo.charAt(0).toUpperCase();
    document.getElementById('ver_nombre').innerText = data.nombre_completo;
    document.getElementById('ver_usuario').innerText = "@" + data.usuario;
    document.getElementById('ver_rol').innerText = data.rol;
    
    // Formatear la fecha (De YYYY-MM-DD a DD/MM/YYYY)
    const partes = data.fecha_alta.split('-');
    document.getElementById('ver_fecha').innerText = `${partes[2]}/${partes[1]}/${partes[0]}`;
    
    const badge = document.getElementById('ver_estado');
    badge.innerText = data.estado;
    if (data.estado === 'Activo') {
        badge.className = 'info-badge';
    } else {
        badge.className = 'info-badge inactivo';
    }
    
    abrirModal('modalVer');
}
</script>

<?php require_once '../templates/footer.php'; ?>