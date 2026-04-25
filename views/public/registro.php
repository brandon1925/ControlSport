<?php
// Archivo: views/public/registro.php
$id_entrenador = isset($_GET['ref']) ? (int)$_GET['ref'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Inscripción - ControlSport</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../../assets/css/registro.css?v=<?php echo time(); ?>">
    <style>
        .toast-container { position: fixed; top: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid; }
        
        /* ESTILOS DEL BUSCADOR AUTOCOMPLETADO */
        .autocomplete-wrapper { position: relative; width: 100%; }
        .autocomplete-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #E2E8F0; border-radius: 8px; max-height: 220px; overflow-y: auto; z-index: 10; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: none; }
        .autocomplete-item { padding: 12px 15px; cursor: pointer; font-size: 14px; color: #1E293B; border-bottom: 1px solid #F1F5F9; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .autocomplete-item:hover { background: #F8FAFC; color: #0047AB; }
        .autocomplete-item:last-child { border-bottom: none; }
        .btn-add-tutor { background: none; border: 1px dashed #CBD5E1; color: #0047AB; width: 100%; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.2s; }
        .btn-add-tutor:hover { background: #F8FAFC; border-color: #0047AB; }
        .tutor-selected-card { background: #F0FDF4; border: 1px solid #22C55E; padding: 15px; border-radius: 8px; display: none; align-items: center; justify-content: space-between; margin-top: 10px; }
        .tutor-selected-info { display: flex; align-items: center; gap: 12px; color: #166534; font-weight: 600; font-size: 14px; }
        .btn-remove-tutor { background: none; border: none; color: #EF4444; cursor: pointer; font-size: 22px; transition: 0.2s;}
        .btn-remove-tutor:hover { opacity: 0.7; }
    </style>
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <a href="../auth/login.php"><i class="ph ph-arrow-left"></i></a>
            <h2>Solicitud de Inscripción</h2>
        </div>

        <div class="form-body">
            <?php if($id_entrenador === 0): ?>
                <div class="alert-box">
                    <i class="ph-fill ph-warning-circle" style="font-size: 20px; vertical-align: middle; margin-right: 5px;"></i> 
                    Enlace de inscripción incompleto. Por favor, solicita el enlace a tu entrenador.
                </div>
            <?php else: ?>
                
                <div style="text-align: center; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #F1F5F9;">
                    <p style="font-size: 13px; color: #64748B; margin-bottom: 10px; font-weight: 600;">¿Ya llenaste el formulario hace unos días?</p>
                    <button type="button" onclick="abrirModalEstatus()" class="btn-outline-blue">
                        <i class="ph ph-magnifying-glass" style="font-size: 18px;"></i> Consultar estatus de mi solicitud
                    </button>
                </div>
                
                <h3 style="color: #0047AB; font-size: 16px; margin-bottom: 20px; text-align: center;">Nueva Inscripción</h3>

                <form action="../../controllers/registroController.php" method="POST">
                    <input type="hidden" name="id_entrenador" value="<?php echo $id_entrenador; ?>">

                    <div class="input-group">
                        <label>Nombre del alumno</label>
                        <div class="input-wrapper">
                            <i class="ph ph-user"></i>
                            <input type="text" name="nombre_completo" placeholder="Nombre completo" minlength="8" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Edad</label>
                        <div class="input-wrapper">
                            <i class="ph ph-calendar-blank"></i>
                            <input type="number" name="edad" placeholder="Ej. 12" required min="3" max="99">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>CURP</label>
                        <div class="input-wrapper">
                            <i class="ph ph-identification-card"></i>
                            <input type="text" id="curp_alumno" name="curp" placeholder="18 caracteres alfanuméricos" maxlength="18" style="text-transform: uppercase;" required>
                        </div>
                        <small id="msg_curp" style="display:block; margin-top:5px; font-size:11px; font-weight: 600;"></small>
                    </div>

                    <div class="input-group">
                        <label>Peso (kg)</label>
                        <div class="input-wrapper">
                            <i class="ph ph-scales"></i>
                            <input type="number" step="0.1" name="peso" placeholder="Ej. 45.5" min="0" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Estatura (cm)</label>
                        <div class="input-wrapper">
                            <i class="ph ph-ruler"></i>
                            <input type="number" name="estatura" placeholder="Ej. 155" min="0" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Institución Médica</label>
                        <div class="input-wrapper select-wrapper">
                            <i class="ph ph-hospital"></i>
                            <select id="inst_medica" name="institucion_medica" required>
                                <option value="" disabled selected>IMSS, ISSSTE, Seguro Privado...</option>
                                <option value="IMSS">IMSS</option>
                                <option value="ISSSTE">ISSSTE</option>
                                <option value="Insabi/Seguro Popular">Insabi / Seguro Popular</option>
                                <option value="Seguro Privado">Seguro Privado</option>
                                <option value="Ninguno">Ninguno</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Número de Afiliación / NSS</label>
                        <div class="input-wrapper">
                            <i class="ph ph-file-text"></i>
                            <input type="text" id="no_afiliacion" name="no_afiliacion" placeholder="Número de afiliación o póliza" required>
                        </div>
                        <small id="msg_nss" style="display:block; margin-top:5px; font-size:11px; font-weight: 600;"></small>
                    </div>

                    <div class="input-group">
                        <label>Domicilio</label>
                        <div class="input-wrapper">
                            <i class="ph ph-map-pin"></i>
                            <input type="text" name="domicilio" placeholder="Ej: Buenavista de cuellar, 21 de junio 20" required>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- NUEVA SECCIÓN INTELIGENTE DEL TUTOR            -->
                    <!-- ============================================== -->
                    <div class="input-group" style="border-top: 2px dashed #E2E8F0; padding-top: 25px; margin-top: 15px;">
                        <label style="color: #0047AB; font-size: 14px;">Tutor (Buscar o Agregar)</label>
                        
                        <!-- Contenedor de Búsqueda -->
                        <div id="tutor_search_container">
                            <div class="autocomplete-wrapper">
                                <div class="input-wrapper">
                                    <i class="ph ph-magnifying-glass"></i>
                                    <input type="text" id="buscar_tutor" placeholder="Escribe tu nombre para buscarte..." autocomplete="off">
                                </div>
                                <div id="lista_tutores" class="autocomplete-list"></div>
                            </div>
                            <button type="button" class="btn-add-tutor" onclick="abrirModalTutor()">
                                <i class="ph ph-plus-circle" style="font-size: 16px; vertical-align: middle;"></i> No me encuentro, registrarme como nuevo
                            </button>
                        </div>

                        <!-- Tarjeta de Tutor Seleccionado (Oculta) -->
                        <div id="tutor_selected_card" class="tutor-selected-card">
                            <div class="tutor-selected-info">
                                <i class="ph-fill ph-check-circle" style="font-size: 26px;"></i>
                                <div>
                                    <span id="tutor_selected_name" style="display: block; font-size: 15px;"></span>
                                    <span style="font-size: 11px; color: #16A34A; font-weight: normal;">Tutor Seleccionado</span>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-tutor" onclick="removerTutor()"><i class="ph-fill ph-x-circle"></i></button>
                        </div>

                        <!-- Campos ocultos que se van a PostgreSQL -->
                        <input type="hidden" name="id_tutor" id="hidden_id_tutor">
                        <input type="hidden" name="nombre_tutor" id="hidden_nombre_tutor">
                        <input type="hidden" name="telefono_tutor" id="hidden_telefono_tutor">
                    </div>
                    <!-- ============================================== -->

                    <button type="submit" id="btnGuardar" class="btn-submit" disabled style="opacity: 0.5; cursor: not-allowed; margin-top: 25px;">Enviar Solicitud</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL DE NUEVO TUTOR -->
    <div class="modal-overlay" id="modalTutor">
        <div class="modal-card">
            <button type="button" class="close-btn" onclick="cerrarModalTutor()"><i class="ph ph-x"></i></button>
            <h3 style="color: #0047AB; font-size: 20px; margin-bottom: 5px; text-align: left;">Registrar Tutor</h3>
            <p style="color: #64748B; font-size: 13px; margin-bottom: 25px; text-align: left;">Ingresa tus datos para registrarte en el sistema de la escuela.</p>
            
            <div class="input-group" style="text-align: left;">
                <label>Tu Nombre Completo</label>
                <div class="input-wrapper">
                    <i class="ph ph-user"></i>
                    <input type="text" id="modal_nombre_tutor" placeholder="Nombre completo" minlength="8">
                </div>
            </div>

            <div class="input-group" style="text-align: left;">
                <label>Tu Teléfono Celular</label>
                <div class="input-wrapper">
                    <i class="ph ph-phone"></i>
                    <input type="tel" id="modal_tel_tutor" placeholder="10 dígitos numéricos" maxlength="10">
                </div>
                <small id="modal_msg_tel" style="display:block; margin-top:5px; font-size:11px; font-weight: 600; text-align: left;"></small>
            </div>

            <button type="button" id="btnConfirmarTutor" class="btn-submit" style="background: #0047AB; margin-top: 10px;" disabled onclick="confirmarNuevoTutor()">Guardar y Seleccionar</button>
        </div>
    </div>

    <!-- MODAL DE CONSULTA DE ESTATUS -->
    <div class="modal-overlay" id="modalEstatus">
        <div class="modal-card">
            <button type="button" class="close-btn" onclick="cerrarModalEstatus()"><i class="ph ph-x"></i></button>
            <h3 style="color: #0047AB; font-size: 20px; margin-bottom: 5px; text-align: left;">Consultar Estatus</h3>
            <p style="color: #64748B; font-size: 13px; margin-bottom: 20px; text-align: left;">Ingresa la CURP del alumno para ver la decisión del entrenador.</p>
            
            <form id="formEstatus">
                <div class="input-group" style="text-align: left;">
                    <div class="input-wrapper">
                        <i class="ph ph-identification-card"></i>
                        <input type="text" id="curp_consulta" placeholder="CURP (18 caracteres)" maxlength="18" style="text-transform: uppercase;" required>
                    </div>
                </div>
                <button type="submit" id="btnConsultarEstatus" class="btn-submit" style="margin-top: 0; padding: 12px; background: #0047AB;">Consultar</button>
            </form>
            <div id="resultadoEstatus" class="alert-box" style="display: none; margin-top: 20px; text-align: left; padding: 15px;"></div>
        </div>
    </div>

    <!-- Contenedor Toasts -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="../../assets/js/registro.js?v=<?php echo time(); ?>"></script>

    <script>
        function mostrarToast(tipo, mensaje) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            if (tipo === 'exito') {
                toast.className = 'toast';
                toast.style.background = '#F0FDF4'; toast.style.borderColor = '#DCFCE7';
                toast.innerHTML = `<i class="ph-fill ph-check-circle" style="color: #16A34A; font-size: 24px;"></i> <p style="color: #166534; margin: 0; font-size: 14px; font-weight: 600;">${mensaje}</p>`;
            } else {
                toast.className = 'toast error';
                toast.style.background = '#FEF2F2'; toast.style.borderColor = '#FEE2E2';
                toast.innerHTML = `<i class="ph-fill ph-warning-circle" style="color: #EF4444; font-size: 24px;"></i> <p style="color: #991B1B; margin: 0; font-size: 14px; font-weight: 600;">${mensaje}</p>`;
            }
            container.appendChild(toast);
            toast.style.transform = 'translateX(150%)'; toast.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 10);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 400); }, 4000);
        }

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            document.addEventListener('DOMContentLoaded', () => { mostrarToast('exito', '¡Formulario enviado correctamente!'); });
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if($_GET['error'] == 'curp'): ?>
                    mostrarToast('error', 'La CURP ingresada ya tiene una solicitud registrada.');
                <?php elseif($_GET['error'] == 'nss'): ?>
                    mostrarToast('error', 'El Número de Afiliación / NSS ingresado ya está registrado.');
                <?php else: ?>
                    mostrarToast('error', 'Ocurrió un error al enviar tu solicitud. Intenta de nuevo.');
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>