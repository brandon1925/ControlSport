<?php
// Archivo: views/public/registro.php

// Capturamos el ID del entrenador que viene oculto en la URL
$id_entrenador = isset($_GET['ref']) ? (int)$_GET['ref'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Inscripción - ControlSport</title>
    <!-- Iconos Phosphor -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Agregamos time() para evitar la caché y que cargue siempre bien el diseño -->
    <link rel="stylesheet" href="../../assets/css/registro.css?v=<?php echo time(); ?>">
    <style>
        .toast-container { position: fixed; top: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid; }
    </style>
</head>
<body>

    <div class="form-container">
        <!-- Header Azul -->
        <div class="form-header">
            <a href="../auth/login.php"><i class="ph ph-arrow-left"></i></a>
            <h2>Solicitud de Inscripción</h2>
        </div>

        <div class="form-body">
            <?php if($id_entrenador === 0): ?>
                <div class="alert-box">
                    <i class="ph-fill ph-warning-circle" style="font-size: 20px; vertical-align: middle; margin-right: 5px;"></i> 
                    Enlace de inscripción incompleto. Por favor, solicita el enlace correcto a tu entrenador.
                </div>
            <?php else: ?>
                
                <!-- SECCIÓN: Consultar Estatus -->
                <div style="text-align: center; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #F1F5F9;">
                    <p style="font-size: 13px; color: #64748B; margin-bottom: 10px; font-weight: 600;">¿Ya llenaste el formulario hace unos días?</p>
                    <button type="button" onclick="abrirModalEstatus()" class="btn-outline-blue">
                        <i class="ph ph-magnifying-glass" style="font-size: 18px;"></i> Consultar estatus de mi solicitud
                    </button>
                </div>
                
                <h3 style="color: #0047AB; font-size: 16px; margin-bottom: 20px; text-align: center;">Nueva Inscripción</h3>

                <!-- Formulario Principal -->
                <form action="../../controllers/registroController.php" method="POST">
                    <input type="hidden" name="id_entrenador" value="<?php echo $id_entrenador; ?>">

                    <div class="input-group">
                        <label>Nombre del alumno</label>
                        <div class="input-wrapper">
                            <i class="ph ph-user"></i>
                            <input type="text" name="nombre_completo" placeholder="Nombre completo" minlength="8" pattern="^.*[a-zA-ZÀ-ÿñÑ]+\s+[a-zA-ZÀ-ÿñÑ]+.*$" required>
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

                    <!-- AQUÍ ESTABA EL ERROR: Solo dejamos un campo de estatura -->
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
                            <input type="text" name="domicilio" placeholder="Dirección completa" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Nombre del Tutor</label>
                        <div class="input-wrapper">
                            <i class="ph ph-users"></i>
                            <input type="text" name="nombre_tutor" placeholder="Nombre del padre o tutor" minlength="8" pattern="^.*[a-zA-ZÀ-ÿñÑ]+\s+[a-zA-ZÀ-ÿñÑ]+.*$" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Teléfono del Tutor</label>
                        <div class="input-wrapper">
                            <i class="ph ph-phone"></i>
                            <input type="tel" id="tel_tutor" name="telefono_tutor" placeholder="10 dígitos numéricos" maxlength="10" required>
                        </div>
                        <small id="msg_tel" style="display:block; margin-top:5px; font-size:11px; font-weight: 600;"></small>
                    </div>

                    <button type="submit" id="btnGuardar" class="btn-submit" disabled style="opacity: 0.5; cursor: not-allowed;">Enviar Solicitud</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL DE CONSULTA DE ESTATUS -->
    <div class="modal-overlay" id="modalEstatus">
        <div class="modal-card">
            <button class="close-btn" onclick="cerrarModalEstatus()"><i class="ph ph-x"></i></button>
            <h3 style="color: #0047AB; font-size: 20px; margin-bottom: 5px;">Consultar Estatus</h3>
            <p style="color: #64748B; font-size: 13px; margin-bottom: 20px;">Ingresa la CURP del alumno para ver la decisión del entrenador.</p>
            
            <form id="formEstatus">
                <div class="input-group">
                    <div class="input-wrapper">
                        <i class="ph ph-identification-card"></i>
                        <input type="text" id="curp_consulta" placeholder="Ingresa la CURP (18 caracteres)" maxlength="18" style="text-transform: uppercase;" required>
                    </div>
                </div>
                <button type="submit" id="btnConsultarEstatus" class="btn-submit" style="margin-top: 0; padding: 12px; background: #0047AB;">Consultar</button>
            </form>

            <!-- Caja de resultados dinámica -->
            <div id="resultadoEstatus" class="alert-box" style="display: none; margin-top: 20px; text-align: left; padding: 15px;"></div>
        </div>
    </div>

    <!-- Contenedor Toasts -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="../../assets/js/registro.js"></script>

    <!-- Script de Notificaciones PHP -->
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
                <?php else: ?>
                    mostrarToast('error', 'Ocurrió un error al enviar tu solicitud. Intenta de nuevo.');
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>