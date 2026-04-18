// Archivo: assets/js/inicio.js

function copiarEnlaceRegistro() {
    // Obtenemos el ID del usuario que tiene la sesión abierta desde la variable que crearemos en PHP
    if (typeof ID_USUARIO_ACTUAL === 'undefined') {
        console.error("No se pudo obtener el ID del usuario.");
        return;
    }

    const idUsuario = ID_USUARIO_ACTUAL;
    
    // Construimos dinámicamente la ruta donde vivirá tu formulario público
    const baseUrl = window.location.origin;
    const pathArray = window.location.pathname.split('/');
    
    // Buscamos la carpeta "views" para armar la ruta correcta sin importar en qué servidor estés
    const viewsIndex = pathArray.indexOf('views');
    const rootPath = pathArray.slice(0, viewsIndex).join('/');
    
    // Enlace inteligente (Opción 2): Agrega la variable ?ref=ID
    const enlaceInteligente = `${baseUrl}${rootPath}/views/public/registro.php?ref=${idUsuario}`;

    // Intentamos copiar al portapapeles nativo del navegador
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(enlaceInteligente).then(() => {
            if(typeof mostrarToastGlobal === 'function') {
                mostrarToastGlobal('Enlace de inscripción copiado al portapapeles');
            }
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    } else {
        // Método de respaldo para navegadores más antiguos o sin HTTPS
        const textArea = document.createElement("textarea");
        textArea.value = enlaceInteligente;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            if(typeof mostrarToastGlobal === 'function') {
                mostrarToastGlobal('Enlace de inscripción copiado al portapapeles');
            }
        } catch (err) {
            console.error('Error al copiar: ', err);
        }
        document.body.removeChild(textArea);
    }
}