// Archivo: assets/js/solicitudes.js

function validarAprobacion(idAlumno, nombreAlumno) {
    const selectGrupo = document.getElementById('select_grupo_' + idAlumno);
    
    // Si no ha seleccionado un grupo, mostramos el error
    if (!selectGrupo.value) {
        mostrarToastPantalla('error', 'Selecciona un grupo antes de aprobar');
        return;
    }

    // Si seleccionó, enviamos el formulario oculto
    document.getElementById('form_aprobar_' + idAlumno).submit();
}

function mostrarToastPantalla(tipo, mensaje) {
    const container = document.getElementById('toastActionContainer');
    if (!container) return;

    // Limpiamos toasts anteriores
    container.innerHTML = '';

    const toast = document.createElement('div');
    
    if (tipo === 'error') {
        toast.className = 'toast-top-right toast-error';
        toast.innerHTML = `<i class="ph-fill ph-warning-circle" style="font-size: 20px;"></i> <span>${mensaje}</span>`;
    } else {
        toast.className = 'toast-top-right toast-success';
        toast.innerHTML = `<i class="ph-fill ph-check-circle" style="font-size: 20px;"></i> <span>${mensaje}</span>`;
    }

    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}