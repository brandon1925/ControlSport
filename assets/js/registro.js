// Archivo: assets/js/registro.js

// ==========================================
// MODAL DE CONSULTA DE ESTATUS
// ==========================================
function abrirModalEstatus() {
    const modal = document.getElementById('modalEstatus');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function cerrarModalEstatus() {
    const modal = document.getElementById('modalEstatus');
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
    document.getElementById('curp_consulta').value = '';
    document.getElementById('resultadoEstatus').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    
    // ESTATUS SUBMIT
    const formEstatus = document.getElementById('formEstatus');
    if (formEstatus) {
        formEstatus.addEventListener('submit', async function(e) {
            e.preventDefault();
            const curpConsulta = document.getElementById('curp_consulta').value.toUpperCase();
            const btnConsulta = document.getElementById('btnConsultarEstatus');
            const resultBox = document.getElementById('resultadoEstatus');
            
            btnConsulta.disabled = true;
            btnConsulta.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Buscando...';
            resultBox.style.display = 'none';

            try {
                const response = await fetch('../../controllers/consultaEstatus.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ curp: curpConsulta })
                });
                const data = await response.json();
                resultBox.style.display = 'block';
                
                if (data.success) {
                    if (data.estado === 'Pendiente') {
                        resultBox.style.background = '#FFFBEB'; resultBox.style.color = '#D97706'; resultBox.style.borderColor = '#FEF3C7';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-clock" style="font-size:22px;"></i> <b style="font-size:15px;">En revisión</b></div> La solicitud de <b>${data.nombre}</b> sigue pendiente de aprobación.`;
                    } else if (data.estado === 'Inscrito') {
                        resultBox.style.background = '#F0FDF4'; resultBox.style.color = '#16A34A'; resultBox.style.borderColor = '#DCFCE7';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-check-circle" style="font-size:22px;"></i> <b style="font-size:15px;">¡Aprobada!</b></div> La solicitud de <b>${data.nombre}</b> fue aceptada.`;
                    } else if (data.estado === 'Rechazado') {
                        resultBox.style.background = '#FEF2F2'; resultBox.style.color = '#EF4444'; resultBox.style.borderColor = '#FEE2E2';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-x-circle" style="font-size:22px;"></i> <b style="font-size:15px;">No aceptada</b></div> Lo sentimos, la solicitud de <b>${data.nombre}</b> no fue aceptada en esta ocasión.`;
                    }
                } else {
                    resultBox.style.background = '#F8FAFC'; resultBox.style.color = '#64748B'; resultBox.style.borderColor = '#E2E8F0';
                    resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-question" style="font-size:22px;"></i> <b style="font-size:15px;">No encontrada</b></div> No hay solicitudes registradas con esta CURP.`;
                }
            } catch (err) {
                resultBox.style.display = 'block'; resultBox.innerHTML = 'Error de conexión. Intenta de nuevo.';
            } finally {
                btnConsulta.disabled = false; btnConsulta.innerHTML = 'Consultar';
            }
        });
    }

    // ==========================================
    // LÓGICA GENERAL DEL FORMULARIO
    // ==========================================
    const curpInput = document.getElementById('curp_alumno');
    const msgCurp = document.getElementById('msg_curp');
    const instMedica = document.getElementById('inst_medica');
    const nssInput = document.getElementById('no_afiliacion');
    const msgNss = document.getElementById('msg_nss');
    const btnGuardar = document.getElementById('btnGuardar');
    const formInscripcion = document.querySelector('form');
    const nombreAlumnoInput = document.querySelector('input[name="nombre_completo"]');

    // Cambiamos "tel" por "tutor" en el validador principal
    let validaciones = { curp: false, nss: false, tutor: false };

    function checkFormulario() {
        if (validaciones.curp && validaciones.nss && validaciones.tutor) {
            if(btnGuardar) { btnGuardar.disabled = false; btnGuardar.style.opacity = '1'; btnGuardar.style.cursor = 'pointer'; }
        } else {
            if(btnGuardar) { btnGuardar.disabled = true; btnGuardar.style.opacity = '0.5'; btnGuardar.style.cursor = 'not-allowed'; }
        }
    }

    if (nombreAlumnoInput) {
        nombreAlumnoInput.addEventListener('input', function(e) {
            let start = this.selectionStart; let end = this.selectionEnd;
            this.value = this.value.toUpperCase(); this.setSelectionRange(start, end);
        });
    }

    // Bloqueo de Negativos
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') e.preventDefault();
        });
        input.addEventListener('paste', function(e) {
            const pastedData = e.clipboardData.getData('text');
            if (pastedData.includes('-')) e.preventDefault();
        });
    });

    if (curpInput) {
        curpInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
            let curp = e.target.value;
            const regexCurp = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/;

            if (curp.length === 0) {
                curpInput.style.borderColor = ''; msgCurp.textContent = ''; validaciones.curp = false;
            } else if (regexCurp.test(curp)) {
                curpInput.style.borderColor = '#22C55E'; msgCurp.textContent = 'CURP Válido ✓'; msgCurp.style.color = '#16A34A'; validaciones.curp = true;
            } else {
                curpInput.style.borderColor = '#EF4444'; msgCurp.style.color = '#DC2626'; validaciones.curp = false;
                if (curp.length < 18) { msgCurp.textContent = `Faltan ${18 - curp.length} caracteres...`; } 
                else { msgCurp.textContent = 'Formato no cumple con la estructura oficial de CURP.'; }
            }
            checkFormulario();
        });
    }

    function validarNSS() {
        if (!nssInput || !instMedica) return;
        let institucion = instMedica.value; let nss = nssInput.value.trim();

        if (institucion === 'Ninguno') {
            nssInput.value = 'N/A'; nssInput.readOnly = true; 
            nssInput.style.background = '#F1F5F9'; nssInput.style.borderColor = '#22C55E';
            msgNss.textContent = 'No aplica ✓'; msgNss.style.color = '#16A34A'; validaciones.nss = true;
        } else {
            nssInput.readOnly = false; nssInput.style.background = '#F8FAFC';
            if (nss === 'N/A') { nssInput.value = ''; nss = ''; }
            
            if (nss.length === 0) {
                nssInput.style.borderColor = ''; msgNss.textContent = ''; validaciones.nss = false;
            } else if (institucion === 'IMSS' || institucion === 'ISSSTE') {
                nssInput.value = nssInput.value.replace(/\D/g, ''); nss = nssInput.value;
                let minReq = (institucion === 'IMSS') ? 11 : 10; let maxReq = 11;
                nssInput.setAttribute('maxlength', maxReq);

                if (nss.length === minReq || nss.length === maxReq) {
                    nssInput.style.borderColor = '#22C55E'; msgNss.textContent = `${institucion} Válido ✓`; msgNss.style.color = '#16A34A'; validaciones.nss = true;
                } else {
                    nssInput.style.borderColor = '#EF4444'; msgNss.style.color = '#DC2626';
                    msgNss.textContent = `Debe tener ${minReq} a ${maxReq} dígitos numéricos.`; validaciones.nss = false;
                }
            } else {
                nssInput.removeAttribute('maxlength');
                if (nss.length >= 5) {
                    nssInput.style.borderColor = '#22C55E'; msgNss.textContent = 'Póliza Válida ✓'; msgNss.style.color = '#16A34A'; validaciones.nss = true;
                } else {
                    nssInput.style.borderColor = '#EF4444'; msgNss.style.color = '#DC2626';
                    msgNss.textContent = 'Ingrese la póliza completa (Mín. 5)'; validaciones.nss = false;
                }
            }
        }
        checkFormulario();
    }

    if (nssInput && instMedica) {
        nssInput.addEventListener('input', validarNSS);
        instMedica.addEventListener('change', validarNSS);
    }

    if (formInscripcion && formInscripcion.id !== 'formEstatus') {
        formInscripcion.addEventListener('submit', function() {
            if(btnGuardar && !btnGuardar.disabled) {
                btnGuardar.disabled = true; btnGuardar.style.opacity = '0.7'; btnGuardar.style.cursor = 'wait';
                btnGuardar.innerHTML = '<i class="ph ph-spinner ph-spin" style="margin-right: 8px;"></i> Procesando...';
            }
        });
    }

    // ==========================================
    // BUSCADOR INTELIGENTE DE TUTORES
    // ==========================================
    const inputBuscarTutor = document.getElementById('buscar_tutor');
    const listaTutores = document.getElementById('lista_tutores');
    const tutorSearchContainer = document.getElementById('tutor_search_container');
    const tutorSelectedCard = document.getElementById('tutor_selected_card');
    const tutorSelectedName = document.getElementById('tutor_selected_name');

    const hiddenIdTutor = document.getElementById('hidden_id_tutor');
    const hiddenNombreTutor = document.getElementById('hidden_nombre_tutor');
    const hiddenTelefonoTutor = document.getElementById('hidden_telefono_tutor');

    let debounceTimer;

    if (inputBuscarTutor) {
        inputBuscarTutor.addEventListener('input', function(e) {
            let start = this.selectionStart; let end = this.selectionEnd;
            this.value = this.value.toUpperCase(); this.setSelectionRange(start, end);
            
            const query = this.value.trim();

            clearTimeout(debounceTimer);
            if (query.length < 2) {
                listaTutores.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch('../../controllers/buscarTutor.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ q: query })
                    });
                    const res = await response.json();
                    
                    listaTutores.innerHTML = '';
                    if (res.success && res.data.length > 0) {
                        res.data.forEach(tutor => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            // Ocultamos los números del medio para respetar la privacidad
                            const telOculto = tutor.telefono.substring(0,3) + '****' + tutor.telefono.substring(7);
                            div.innerHTML = `<i class="ph ph-user"></i> <div><b>${tutor.nombre_completo}</b><br><small style="color:#94A3B8;">Tel: ${telOculto}</small></div>`;
                            
                            div.onclick = () => { window.seleccionarTutor(tutor.id_tutor, tutor.nombre_completo, ''); };
                            listaTutores.appendChild(div);
                        });
                        listaTutores.style.display = 'block';
                    } else {
                        listaTutores.innerHTML = '<div style="padding: 15px; color: #94A3B8; font-size: 13px; text-align:center;">No encontramos a ningún tutor con ese nombre. Regístrate haciendo clic en el botón de abajo.</div>';
                        listaTutores.style.display = 'block';
                    }
                } catch (err) {
                    console.error("Error buscando");
                }
            }, 300);
        });

        // Ocultar si da clic afuera
        document.addEventListener('click', function(e) {
            if (!tutorSearchContainer.contains(e.target)) { listaTutores.style.display = 'none'; }
        });
    }

    window.seleccionarTutor = function(id, nombre, telefono) {
        hiddenIdTutor.value = id;
        hiddenNombreTutor.value = nombre;
        hiddenTelefonoTutor.value = telefono;

        tutorSearchContainer.style.display = 'none';
        tutorSelectedCard.style.display = 'flex';
        tutorSelectedName.innerText = nombre;

        validaciones.tutor = true;
        checkFormulario();
        listaTutores.style.display = 'none';
    };

    window.removerTutor = function() {
        hiddenIdTutor.value = ''; hiddenNombreTutor.value = ''; hiddenTelefonoTutor.value = '';
        inputBuscarTutor.value = '';
        tutorSelectedCard.style.display = 'none';
        tutorSearchContainer.style.display = 'block';

        validaciones.tutor = false;
        checkFormulario();
    };

    window.abrirModalTutor = function() {
        const modal = document.getElementById('modalTutor');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    };

    window.cerrarModalTutor = function() {
        const modal = document.getElementById('modalTutor');
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    };

    // ==========================================
    // LÓGICA DEL MODAL "NUEVO TUTOR"
    // ==========================================
    const modalTelInput = document.getElementById('modal_tel_tutor');
    const modalNombreInput = document.getElementById('modal_nombre_tutor');
    const modalMsgTel = document.getElementById('modal_msg_tel');
    const btnConfirmarTutor = document.getElementById('btnConfirmarTutor');
    
    let telModalValido = false;

    if (modalNombreInput) {
        modalNombreInput.addEventListener('input', function(e) {
            let start = this.selectionStart; let end = this.selectionEnd;
            this.value = this.value.toUpperCase(); this.setSelectionRange(start, end);
            validarBtnModal();
        });
    }

    if (modalTelInput) {
        modalTelInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
            let tel = e.target.value;

            if (tel.length === 0) {
                modalTelInput.style.borderColor = ''; modalMsgTel.textContent = ''; telModalValido = false;
            } else if (tel.length === 10) {
                modalTelInput.style.borderColor = '#22C55E'; modalMsgTel.textContent = 'Teléfono Válido ✓'; modalMsgTel.style.color = '#16A34A'; telModalValido = true;
            } else {
                modalTelInput.style.borderColor = '#EF4444'; modalMsgTel.style.color = '#DC2626';
                modalMsgTel.textContent = `Debe tener 10 números (Faltan ${10 - tel.length})`; telModalValido = false;
            }
            validarBtnModal();
        });
    }

    function validarBtnModal() {
        if (telModalValido && modalNombreInput.value.trim().length >= 8) {
            btnConfirmarTutor.disabled = false; btnConfirmarTutor.style.opacity = '1'; btnConfirmarTutor.style.cursor = 'pointer';
        } else {
            btnConfirmarTutor.disabled = true; btnConfirmarTutor.style.opacity = '0.5'; btnConfirmarTutor.style.cursor = 'not-allowed';
        }
    }

    window.confirmarNuevoTutor = function() {
        const nombre = modalNombreInput.value.trim();
        const telefono = modalTelInput.value.trim();
        
        seleccionarTutor(0, nombre, telefono);
        cerrarModalTutor();
    };
});