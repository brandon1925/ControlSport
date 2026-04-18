// Archivo: assets/js/gestion.js

function abrirModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Llenar Modal Ver Detalles
function abrirModalVer(btnElement) {
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

// Llenar Modal Editar
function abrirModalEditar(btnElement) {
    const data = JSON.parse(btnElement.getAttribute('data-info'));
    
    document.getElementById('edit_id').value = data.id_usuario;
    document.getElementById('edit_nombre').value = data.nombre_completo;
    document.getElementById('edit_usuario').value = data.usuario;
    document.getElementById('edit_rol').value = data.rol;
    
    // Limpiar campos de contraseña al abrir
    document.getElementById('edit_pass').value = '';
    document.getElementById('edit_pass_conf').value = '';
    
    // Disparar las validaciones para resetear los iconos
    validarPasswordEnVivo('edit_pass', 'status_edit_pass');
    validarCoincidenciaEnVivo('edit_pass', 'edit_pass_conf', 'status_edit_pass_conf');

    abrirModal('modalEditar');
}

// Llenar Modal Eliminar
function abrirModalEliminar(id, nombre) {
    if (typeof ID_USUARIO_ACTUAL !== 'undefined' && id === ID_USUARIO_ACTUAL) {
        mostrarToast('error', 'Por seguridad, no puedes eliminar tu propia cuenta.');
        return;
    }
    document.getElementById('eliminar_nombre').innerText = nombre;
    document.getElementById('btnConfirmarEliminar').href = `../../controllers/usuarioController.php?accion=eliminar&id=${id}`;
    abrirModal('modalEliminar');
}

// --- Función para el "Ojito" de Contraseñas ---
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("ph-eye");
        icon.classList.add("ph-eye-slash"); 
        icon.style.color = "#0047AB";       
    } else {
        input.type = "password";
        icon.classList.remove("ph-eye-slash");
        icon.classList.add("ph-eye");
        icon.style.color = "#94A3B8";       
    }
}

// --- Validación en Vivo: Reglas de Seguridad ---
function validarPasswordEnVivo(inputId, statusIconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(statusIconId);
    const hint = document.getElementById('hint_' + inputId);
    
    // Reglas: Mín 8 chars, 1 mayúscula, 1 número, 1 especial (!$#%&/*)
    const regex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!$#%&\/\*]).{8,30}$/;
    
    if (input.value.length === 0) {
        icon.className = 'ph'; // Ocultar icono
        if(hint) hint.style.color = '#94A3B8'; 
        return;
    }

    if (regex.test(input.value)) {
        icon.className = 'ph-fill ph-check-circle';
        icon.style.color = '#22C55E'; 
        if(hint) hint.style.color = '#22C55E'; 
    } else {
        icon.className = 'ph-fill ph-x-circle';
        icon.style.color = '#EF4444'; 
        if(hint) hint.style.color = '#EF4444'; 
    }
    
    const confInputId = inputId + "_conf"; 
    if(document.getElementById(confInputId)) {
        validarCoincidenciaEnVivo(inputId, confInputId, 'status_' + confInputId);
    }
}

// --- Validación en Vivo: Coincidencia de Contraseñas ---
function validarCoincidenciaEnVivo(passId, confId, statusIconId) {
    const pass = document.getElementById(passId).value;
    const conf = document.getElementById(confId).value;
    const icon = document.getElementById(statusIconId);

    if (conf.length === 0) {
        icon.className = 'ph';
        return;
    }

    if (pass === conf && pass.length > 0) {
        icon.className = 'ph-fill ph-check-circle';
        icon.style.color = '#22C55E';
    } else {
        icon.className = 'ph-fill ph-x-circle';
        icon.style.color = '#EF4444';
    }
}

// --- Sistema de Toasts ---
function mostrarToast(tipo, mensaje) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    if (tipo === 'exito') {
        toast.className = 'toast show';
        toast.style.background = '#F0FDF4';
        toast.style.borderColor = '#DCFCE7';
        toast.innerHTML = `<i class="ph-fill ph-check-circle" style="color:#16A34A; font-size:24px;"></i> <p style="color:#166534; margin:0;">${mensaje}</p>`;
    } else {
        toast.className = 'toast error show';
        toast.innerHTML = `<i class="ph-fill ph-warning-circle"></i> <p>${mensaje}</p>`;
    }
    
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}