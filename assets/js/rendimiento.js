// Archivo: assets/js/rendimiento.js

// Función para pintar la barra del slider (Naranja a la izquierda, Gris a la derecha)
function pintarFondoSlider(slider) {
    const min = slider.min || 1;
    const max = slider.max || 10;
    const valor = slider.value;
    
    // Calcular porcentaje de llenado
    const porcentaje = ((valor - min) / (max - min)) * 100;
    
    // Aplicar gradiente dinámico
    slider.style.background = `linear-gradient(to right, #FF5A00 ${porcentaje}%, #E2E8F0 ${porcentaje}%)`;
}

// NUEVO: Función para calcular el promedio (Requerimiento 2)
function calcularPromedio() {
    const sliders = document.querySelectorAll('.range-slider');
    let suma = 0;
    sliders.forEach(s => suma += parseInt(s.value));
    const promedio = suma / sliders.length;
    
    const alertaRend = document.getElementById('alertaRendimiento');
    if (alertaRend) {
        if (promedio < 6) {
            alertaRend.style.display = 'flex';
        } else {
            alertaRend.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const selectGrupo = document.getElementById('selectGrupo');
    const selectAlumno = document.getElementById('selectAlumno');
    const evalSection = document.getElementById('evalSection');
    const alertaAsistencia = document.getElementById('alertaAsistencia');

    // Filtrar alumnos por grupo
    selectGrupo.addEventListener('change', function() {
        const idGrupo = this.value;
        selectAlumno.innerHTML = '<option value="">Selecciona un alumno</option>';
        evalSection.style.display = 'none';
        if(alertaAsistencia) alertaAsistencia.style.display = 'none';

        if (idGrupo) {
            selectAlumno.disabled = false;
            const source = document.querySelectorAll(`.src-alumno[data-grupo="${idGrupo}"]`);
            source.forEach(opt => {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.text = opt.text;
                // Transferimos el dato de asistencias al select visible
                newOpt.setAttribute('data-asist', opt.getAttribute('data-asist'));
                selectAlumno.appendChild(newOpt);
            });
        } else {
            selectAlumno.disabled = true;
        }
    });

    // Mostrar evaluación o bloqueo al elegir alumno (Requerimiento 1)
    selectAlumno.addEventListener('change', function() {
        if (this.value) {
            const selectedOpt = this.options[this.selectedIndex];
            const asistencias = parseInt(selectedOpt.getAttribute('data-asist') || 0);

            if (asistencias === 0) {
                // Bloquear evaluación si no tiene asistencias
                evalSection.style.display = 'none';
                if(alertaAsistencia) alertaAsistencia.style.display = 'flex';
            } else {
                // Habilitar evaluación y ocultar bloqueo
                if(alertaAsistencia) alertaAsistencia.style.display = 'none';
                evalSection.style.display = 'block';
                
                // Repintar sliders y calcular promedio inicial
                document.querySelectorAll('.range-slider').forEach(s => pintarFondoSlider(s));
                calcularPromedio(); 
            }
        } else {
            evalSection.style.display = 'none';
            if(alertaAsistencia) alertaAsistencia.style.display = 'none';
        }
    });

    // Eventos de los Sliders
    const sliders = document.querySelectorAll('.range-slider');
    
    sliders.forEach(s => {
        // Inicializar color al cargar
        pintarFondoSlider(s);

        // Actualizar color y texto al arrastrar
        s.addEventListener('input', function() {
            const key = this.getAttribute('data-metric');
            document.getElementById(`val_${key}`).innerText = this.value;
            pintarFondoSlider(this);
            calcularPromedio(); // Recalcular promedio en tiempo real (Requerimiento 2)
        });
    });
});