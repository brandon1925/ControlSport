// Archivo: assets/js/login.js

function mostrarToastError(mensaje) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="ph-fill ph-warning-circle"></i> <p>${mensaje}</p>`;
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { 
        toast.classList.remove('show'); 
        setTimeout(() => toast.remove(), 400); 
    }, 4000);
}

function abrirModal() {
    const modal = document.getElementById('modalInactivo');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function cerrarModal() {
    const modal = document.getElementById('modalInactivo');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
}