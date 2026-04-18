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
    <link rel="stylesheet" href="../../assets/css/registro.css">
    <style>
        /* Estilos base para el Toast */
        .toast-container { position: fixed; top: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid; }
    </style>
</head>
<body>

    <div class="form-container">
        
        <!-- Header Azul -->
        <div class="form-header">
            
            <h2>Solicitud de Inscripción</h2>
        </div>

        <!-- Cuerpo del Formulario -->
        <div class="form-body">

            <?php if($id_entrenador === 0): ?>
                <!-- Validación UX: Si entran al link sin el "?ref=", les avisa amablemente -->
                <div class="alert-box">
                    <i class="ph-fill ph-warning-circle" style="font-size: 20px; vertical-align: middle; margin-right: 5px;"></i> 
                    Enlace de inscripción incompleto. Por favor, solicita el enlace correcto a tu entrenador.
                </div>
            <?php else: ?>
                
                <!-- Si el link es correcto, mostramos el formulario -->
                <form action="../../controllers/registroController.php" method="POST">
                    <!-- Mandamos de forma oculta el ID del entrenador para la base de datos -->
                    <input type="hidden" name="id_entrenador" value="<?php echo $id_entrenador; ?>">

                    <div class="input-group">
                        <label>Nombre del alumno</label>
                        <div class="input-wrapper">
                            <i class="ph ph-user"></i>
                            <!-- Se agregó minlength y un pattern que exige al menos 1 espacio entre letras -->
                            <input type="text" name="nombre_completo" placeholder="Nombre completo" minlength="8" pattern="^.*[a-zA-ZÀ-ÿñÑ]+\s+[a-zA-ZÀ-ÿñÑ]+.*$" title="Debes ingresar al menos un nombre y un apellido separados por un espacio (mínimo 8 caracteres)" required>
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
                            <input type="text" name="curp" placeholder="18 caracteres alfanuméricos" maxlength="18" minlength="18" style="text-transform: uppercase;" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Peso (kg)</label>
                        <div class="input-wrapper">
                            <i class="ph ph-scales"></i>
                            <input type="number" step="0.1" name="peso" placeholder="Ej. 45" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Estatura (cm)</label>
                        <div class="input-wrapper">
                            <i class="ph ph-ruler"></i>
                            <input type="number" name="estatura" placeholder="Ej. 155" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Institución Médica</label>
                        <div class="input-wrapper select-wrapper">
                            <i class="ph ph-hospital"></i>
                            <select name="institucion_medica" required>
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
                        <label>Número de Afiliación / Póliza</label>
                        <div class="input-wrapper">
                            <i class="ph ph-file-text"></i>
                            <input type="text" name="no_afiliacion" placeholder="Número de afiliación o póliza">
                        </div>
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
                            <!-- Validación replicada para el tutor -->
                            <input type="text" name="nombre_tutor" placeholder="Nombre del padre o tutor" minlength="8" pattern="^.*[a-zA-ZÀ-ÿñÑ]+\s+[a-zA-ZÀ-ÿñÑ]+.*$" title="Debes ingresar al menos un nombre y un apellido separados por un espacio (mínimo 8 caracteres)" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <div class="input-wrapper">
                            <i class="ph ph-phone"></i>
                            <!-- Forzamos a que sean exactamente 10 números -->
                            <input type="tel" name="telefono_tutor" placeholder="10 dígitos" pattern="[0-9]{10}" title="Debe contener exactamente 10 números" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Enviar Solicitud</button>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <!-- Contenedor para Notificaciones Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        function mostrarToast(tipo, mensaje) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const toast = document.createElement('div');
            
            if (tipo === 'exito') {
                toast.className = 'toast';
                toast.style.background = '#F0FDF4';
                toast.style.borderColor = '#DCFCE7';
                toast.innerHTML = `<i class="ph-fill ph-check-circle" style="color: #16A34A; font-size: 24px;"></i> <p style="color: #166534; margin: 0; font-size: 14px; font-weight: 600;">${mensaje}</p>`;
            } else {
                toast.className = 'toast error';
                toast.style.background = '#FEF2F2';
                toast.style.borderColor = '#FEE2E2';
                toast.innerHTML = `<i class="ph-fill ph-warning-circle" style="color: #EF4444; font-size: 24px;"></i> <p style="color: #991B1B; margin: 0; font-size: 14px; font-weight: 600;">${mensaje}</p>`;
            }

            container.appendChild(toast);
            
            // Animación
            toast.style.transform = 'translateX(150%)';
            toast.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            
            setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 10);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(150%)';
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        // Lógica para detonar notificaciones según la respuesta del servidor
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            document.addEventListener('DOMContentLoaded', () => {
                mostrarToast('exito', '¡Formulario enviado correctamente!');
            });
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if($_GET['error'] == 'curp'): ?>
                    mostrarToast('error', 'El CURP ingresado ya tiene una solicitud registrada.');
                <?php else: ?>
                    mostrarToast('error', 'Ocurrió un error al enviar tu solicitud. Intenta de nuevo.');
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>

</body>
</html>