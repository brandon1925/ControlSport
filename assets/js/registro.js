// Archivo: assets/js/registro.js

// ==========================================
// NUEVO: LÓGICA DE CONSULTA DE ESTATUS (MODAL)
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
    // Limpiamos los campos al cerrar
    document.getElementById('curp_consulta').value = '';
    document.getElementById('resultadoEstatus').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Capturamos el formulario de estatus
    const formEstatus = document.getElementById('formEstatus');
    if (formEstatus) {
        formEstatus.addEventListener('submit', async function(e) {
            e.preventDefault(); // Evitamos que la página se recargue

            const curpConsulta = document.getElementById('curp_consulta').value.toUpperCase();
            const btnConsulta = document.getElementById('btnConsultarEstatus');
            const resultBox = document.getElementById('resultadoEstatus');
            
            // Estado de carga
            btnConsulta.disabled = true;
            btnConsulta.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Buscando...';
            resultBox.style.display = 'none';

            try {
                // Hacemos la consulta al backend
                const response = await fetch('../../controllers/consultaEstatus.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ curp: curpConsulta })
                });
                
                const data = await response.json();
                
                resultBox.style.display = 'block';
                
                if (data.success) {
                    if (data.estado === 'Pendiente') {
                        resultBox.style.background = '#FFFBEB';
                        resultBox.style.color = '#D97706';
                        resultBox.style.borderColor = '#FEF3C7';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-clock" style="font-size:22px;"></i> <b style="font-size:15px;">En revisión</b></div> La solicitud de <b>${data.nombre}</b> sigue pendiente de aprobación por el entrenador.`;
                    } else if (data.estado === 'Inscrito') {
                        resultBox.style.background = '#F0FDF4';
                        resultBox.style.color = '#16A34A';
                        resultBox.style.borderColor = '#DCFCE7';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-check-circle" style="font-size:22px;"></i> <b style="font-size:15px;">¡Aprobada!</b></div> La solicitud de <b>${data.nombre}</b> fue aceptada. Ya es parte oficial del grupo.`;
                    } else if (data.estado === 'Rechazado') {
                        // === NUEVO ESTADO AGREGADO ===
                        resultBox.style.background = '#FEF2F2';
                        resultBox.style.color = '#EF4444';
                        resultBox.style.borderColor = '#FEE2E2';
                        resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-x-circle" style="font-size:22px;"></i> <b style="font-size:15px;">Solicitud no aceptada</b></div> Lo sentimos, la solicitud de <b>${data.nombre}</b> fue rechazada en esta ocasión. Por favor, contacta al entrenador para más detalles.`;
                    }
                } else {
                    // Si no la encuentra (porque nunca la llenaron o la escribieron mal)
                    resultBox.style.background = '#F8FAFC';
                    resultBox.style.color = '#64748B';
                    resultBox.style.borderColor = '#E2E8F0';
                    resultBox.innerHTML = `<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;"><i class="ph-fill ph-question" style="font-size:22px;"></i> <b style="font-size:15px;">No encontrada</b></div> No hay solicitudes registradas con esta CURP. Verifica que esté bien escrita.`;
                }
            } catch (err) {
                resultBox.style.display = 'block';
                resultBox.innerHTML = 'Error de conexión. Intenta de nuevo.';
            } finally {
                // Restauramos el botón
                btnConsulta.disabled = false;
                btnConsulta.innerHTML = 'Consultar';
            }
        });
    }


    // ==========================================
    // LÓGICA DE VALIDACIÓN DEL FORMULARIO
    // ==========================================
    const curpInput = document.getElementById('curp_alumno');
    const msgCurp = document.getElementById('msg_curp');
    const telInput = document.getElementById('tel_tutor');
    const msgTel = document.getElementById('msg_tel');
    const instMedica = document.getElementById('inst_medica');
    const nssInput = document.getElementById('no_afiliacion');
    const msgNss = document.getElementById('msg_nss');
    const btnGuardar = document.getElementById('btnGuardar');
    const formInscripcion = document.querySelector('form');
    const nombreAlumnoInput = document.querySelector('input[name="nombre_completo"]');
    const nombreTutorInput = document.querySelector('input[name="nombre_tutor"]');

    let validaciones = { curp: false, tel: false, nss: false };

    function checkFormulario() {
        if (validaciones.curp && validaciones.tel && validaciones.nss) {
            if(btnGuardar) { btnGuardar.disabled = false; btnGuardar.style.opacity = '1'; btnGuardar.style.cursor = 'pointer'; }
        } else {
            if(btnGuardar) { btnGuardar.disabled = true; btnGuardar.style.opacity = '0.5'; btnGuardar.style.cursor = 'not-allowed'; }
        }
    }

    function capitalizarPalabras(texto) {
        return texto.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    }

    if (nombreAlumnoInput) {
        nombreAlumnoInput.addEventListener('input', function(e) {
            let start = this.selectionStart; let end = this.selectionEnd;
            this.value = capitalizarPalabras(this.value); this.setSelectionRange(start, end);
        });
    }

    if (nombreTutorInput) {
        nombreTutorInput.addEventListener('input', function(e) {
            let start = this.selectionStart; let end = this.selectionEnd;
            this.value = capitalizarPalabras(this.value); this.setSelectionRange(start, end);
        });
    }

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
                else { msgCurp.textContent = 'El formato no cumple con la estructura oficial de CURP.'; }
            }
            checkFormulario();
        });
    }

    if (telInput) {
        telInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
            let tel = e.target.value;

            if (tel.length === 0) {
                telInput.style.borderColor = ''; msgTel.textContent = ''; validaciones.tel = false;
            } else if (tel.length === 10) {
                telInput.style.borderColor = '#22C55E'; msgTel.textContent = 'Teléfono Válido ✓'; msgTel.style.color = '#16A34A'; validaciones.tel = true;
            } else {
                telInput.style.borderColor = '#EF4444'; msgTel.style.color = '#DC2626';
                msgTel.textContent = `Debe tener 10 números (Faltan ${10 - tel.length})`; validaciones.tel = false;
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
                    msgNss.textContent = `El NSS del ${institucion} debe tener ${minReq} dígitos numéricos.`; validaciones.nss = false;
                }
            } else {
                nssInput.removeAttribute('maxlength');
                if (nss.length >= 5) {
                    nssInput.style.borderColor = '#22C55E'; msgNss.textContent = 'Póliza Válida ✓'; msgNss.style.color = '#16A34A'; validaciones.nss = true;
                } else {
                    nssInput.style.borderColor = '#EF4444'; msgNss.style.color = '#DC2626';
                    msgNss.textContent = 'Ingrese la póliza completa (Mínimo 5 caracteres)'; validaciones.nss = false;
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
            if(btnGuardar) {
                btnGuardar.disabled = true; btnGuardar.style.opacity = '0.7'; btnGuardar.style.cursor = 'wait';
                btnGuardar.innerHTML = '<i class="ph ph-spinner ph-spin" style="margin-right: 8px;"></i> Procesando Solicitud...';
            }
        });
    }
});