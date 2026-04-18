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

document.addEventListener('DOMContentLoaded', () => {
    const selectGrupo = document.getElementById('selectGrupo');
    const selectAlumno = document.getElementById('selectAlumno');
    const evalSection = document.getElementById('evalSection');

    // Filtrar alumnos por grupo
    selectGrupo.addEventListener('change', function() {
        const idGrupo = this.value;
        selectAlumno.innerHTML = '<option value="">Selecciona un alumno</option>';
        evalSection.style.display = 'none';

        if (idGrupo) {
            selectAlumno.disabled = false;
            const source = document.querySelectorAll(`.src-alumno[data-grupo="${idGrupo}"]`);
            source.forEach(opt => {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.text = opt.text;
                selectAlumno.appendChild(newOpt);
            });
        } else {
            selectAlumno.disabled = true;
        }
    });

    // Mostrar evaluación al elegir alumno
    selectAlumno.addEventListener('change', function() {
        if (this.value) {
            evalSection.style.display = 'block';
            // Repintar todos los sliders al mostrar la sección
            document.querySelectorAll('.range-slider').forEach(s => pintarFondoSlider(s));
        } else {
            evalSection.style.display = 'none';
        }
    });

    // Eventos de los Sliders
    const sliders = document.querySelectorAll('.range-slider');
    
    sliders.forEach(s => {
        // Inicializar color al cargar
        pintarFondoSlider(s);

        // Actualizar color y texto al mover
        s.addEventListener('input', function() {
            const key = this.getAttribute('data-metric');
            document.getElementById(`val_${key}`).innerText = this.value;
            pintarFondoSlider(this);
        });
    });
});