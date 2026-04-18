// Archivo: assets/js/alumnos.js

// Lógica de Filtro por Grupo
document.addEventListener('DOMContentLoaded', () => {
    const selectGrupo = document.getElementById('filtroGrupo');
    const emptyState = document.getElementById('emptyState');
    const listadoAlumnos = document.getElementById('listadoAlumnos');
    const subtitleGrupo = document.getElementById('subtitleGrupo');
    const tarjetas = document.querySelectorAll('.alumno-card');

    if (selectGrupo) {
        selectGrupo.addEventListener('change', function() {
            const idGrupoSeleccionado = this.value;
            
            if (idGrupoSeleccionado === "") {
                emptyState.style.display = 'block';
                listadoAlumnos.style.display = 'none';
                subtitleGrupo.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                listadoAlumnos.style.display = 'block';
                subtitleGrupo.style.display = 'block';
                
                let count = 0;
                let nombreGrupoText = this.options[this.selectedIndex].text;

                tarjetas.forEach(tarjeta => {
                    if (tarjeta.getAttribute('data-grupo') === idGrupoSeleccionado) {
                        tarjeta.style.display = 'flex';
                        count++;
                    } else {
                        tarjeta.style.display = 'none';
                    }
                });

                subtitleGrupo.innerText = `${count} alumnos en ${nombreGrupoText}`;
            }
        });
    }
});

// Modales Funciones Base
function abrirModalGeneral(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function cerrarModalGeneral(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Llenar Modal Ver Expediente (Solo Lectura)
function abrirModalExpediente(btn) {
    const data = JSON.parse(btn.getAttribute('data-info'));
    
    document.getElementById('exp_nombre').innerText = `${data.nombre_alumno} ${data.apellido_p_a} ${data.apellido_m_a}`;
    document.getElementById('exp_edad').innerText = `${data.edad} años`;
    document.getElementById('exp_curp').innerText = data.curp;
    document.getElementById('exp_institucion').innerText = data.institucion_medica;
    document.getElementById('exp_tutor').innerText = `${data.nombre_tutor} ${data.apellido_p_t} ${data.apellido_m_t}`;
    document.getElementById('exp_telefono').innerText = data.telefono_tutor;

    abrirModalGeneral('modalExpediente');
}

// Llenar Modal Actualizar (Edición)
function abrirModalActualizar(btn) {
    const data = JSON.parse(btn.getAttribute('data-info'));
    
    document.getElementById('upd_id_alumno').value = data.id_alumno;
    document.getElementById('upd_id_tutor').value = data.id_tutor;
    
    document.getElementById('upd_nombre').value = `${data.nombre_alumno} ${data.apellido_p_a} ${data.apellido_m_a}`.trim();
    document.getElementById('upd_edad').value = data.edad;
    document.getElementById('upd_institucion').value = data.institucion_medica;
    document.getElementById('upd_tutor').value = `${data.nombre_tutor} ${data.apellido_p_t} ${data.apellido_m_t}`.trim();
    document.getElementById('upd_telefono').value = data.telefono_tutor;
    document.getElementById('upd_curp').value = data.curp;

    abrirModalGeneral('modalActualizar');
}

// Llenar Modal Confirmar Baja
function abrirModalBaja(idAlumno, nombreAlumno) {
    document.getElementById('baja_nombre').innerText = nombreAlumno;
    document.getElementById('btnConfirmarBaja').href = `../../controllers/alumnoController.php?accion=baja&id=${idAlumno}&nombre=${encodeURIComponent(nombreAlumno)}`;
    
    abrirModalGeneral('modalBaja');
}

// Toasts
function mostrarToastPantalla(tipo, mensaje) {
    const container = document.getElementById('toastActionContainer');
    if (!container) return;
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
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 4000);
}