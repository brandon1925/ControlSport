// Archivo: assets/js/historial.js

function validarFechas() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const grupo = document.getElementById('id_grupo').value;

    if (!grupo) {
        alert("Por favor, selecciona un grupo.");
        return false;
    }

    if (fechaInicio && fechaFin) {
        if (new Date(fechaInicio) > new Date(fechaFin)) {
            alert("La fecha de inicio no puede ser posterior a la fecha de fin.");
            return false;
        }
    } else {
        alert("Por favor, selecciona el rango de fechas completo.");
        return false;
    }

    return true;
}

// Opcional: Función para exportar que usaremos más adelante
function exportarHistorial() {
    console.log("Exportando historial...");
    // Aquí implementaremos la generación de PDF en el futuro
}