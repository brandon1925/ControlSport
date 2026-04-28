// Archivo: assets/js/informes.js

let chartEvolucion = null;

// Colores consistentes para las líneas
const coloresHabilidades = {
    velocidad: '#FF5A00',     // Naranja
    fuerza: '#0047AB',        // Azul oscuro
    resistencia: '#22C55E',   // Verde
    agilidad: '#F59E0B',      // Amarillo
    coordinacion: '#8B5CF6',  // Morado
    flexibilidad: '#EC4899'   // Rosa
};

function cambiarGrupo() {
    document.getElementById('formFiltro').submit();
}

// Ocultar Ranking y Mostrar Detalles del Alumno
function verDetalleAlumno(idAlumno, nombreAlumno) {
    document.getElementById('rankingView').style.display = 'none';
    document.getElementById('detalleAlumnoView').style.display = 'block';
    document.getElementById('nombreDetalle').innerText = nombreAlumno;

    // Obtener datos del alumno desde la variable global inyectada en PHP
    const evals = datosEvaluacionesGlobal.filter(e => e.id_alumno == idAlumno);
    
    // --- LÓGICA PARA MOSTRAR BANNER DE BAJO RENDIMIENTO ---
    const bannerAlerta = document.getElementById('alertaRendimientoDetalle');
    if (evals.length > 0) {
        let sumaTotal = 0;
        evals.forEach(e => {
            sumaTotal += (parseInt(e.velocidad) + parseInt(e.fuerza) + parseInt(e.resistencia) + parseInt(e.agilidad) + parseInt(e.coordinacion) + parseInt(e.flexibilidad));
        });
        
        let promedioGeneral = sumaTotal / (evals.length * 6);
        
        if (promedioGeneral < 6) {
            bannerAlerta.style.display = 'flex';
        } else {
            bannerAlerta.style.display = 'none';
        }
    } else {
        bannerAlerta.style.display = 'none';
    }
    // ------------------------------------------------------

    // Cargar Notas
    cargarObservaciones(evals);

    // Renderizar Gráfica por defecto (Todas)
    renderizarGrafica(evals, 'todas');

    // Configurar botones de filtro
    const botones = document.querySelectorAll('.filter-btn');
    botones.forEach(btn => {
        btn.onclick = function() {
            botones.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderizarGrafica(evals, this.getAttribute('data-skill'));
        };
    });
}

function volverRanking() {
    document.getElementById('detalleAlumnoView').style.display = 'none';
    document.getElementById('rankingView').style.display = 'block';
}

function cargarObservaciones(evals) {
    const list = document.getElementById('obsList');
    list.innerHTML = '';

    const evalsConNotas = evals.filter(e => e.notas_adicionales && e.notas_adicionales.trim() !== '');

    if (evalsConNotas.length === 0) {
        list.innerHTML = '<div class="empty-obs"><i class="ph ph-chat-slash" style="font-size:32px; margin-bottom:10px; display:block;"></i>Sin observaciones registradas</div>';
        return;
    }

    evalsConNotas.forEach(e => {
        list.innerHTML += `
            <div class="obs-item">
                <div class="obs-date"><i class="ph ph-calendar"></i> ${e.fecha_evaluacion}</div>
                <p class="obs-text">"${e.notas_adicionales}"</p>
            </div>
        `;
    });
}

function renderizarGrafica(evals, filtro) {
    const ctx = document.getElementById('chartEvolucion').getContext('2d');
    
    if (chartEvolucion) {
        chartEvolucion.destroy();
    }

    // Ordenar evaluaciones por fecha (más antigua a más reciente)
    const sortedEvals = [...evals].sort((a, b) => new Date(a.fecha_evaluacion) - new Date(b.fecha_evaluacion));
    const labels = sortedEvals.map(e => e.fecha_evaluacion);

    let datasets = [];

    const crearDataset = (llave, label, color) => ({
        label: label,
        data: sortedEvals.map(e => e[llave]),
        borderColor: color,
        backgroundColor: color,
        pointBackgroundColor: 'white',
        pointBorderColor: color,
        pointBorderWidth: 2,
        pointRadius: 4,
        tension: 0.3, // Curvas suaves
        borderWidth: 2
    });

    if (filtro === 'todas') {
        datasets.push(crearDataset('velocidad', 'Velocidad', coloresHabilidades.velocidad));
        datasets.push(crearDataset('fuerza', 'Fuerza', coloresHabilidades.fuerza));
        datasets.push(crearDataset('resistencia', 'Resistencia', coloresHabilidades.resistencia));
        datasets.push(crearDataset('agilidad', 'Agilidad', coloresHabilidades.agilidad));
        datasets.push(crearDataset('coordinacion', 'Coordinación', coloresHabilidades.coordinacion));
        datasets.push(crearDataset('flexibilidad', 'Flexibilidad', coloresHabilidades.flexibilidad));
    } else {
        const labelText = filtro.charAt(0).toUpperCase() + filtro.slice(1);
        datasets.push(crearDataset(filtro, labelText, coloresHabilidades[filtro]));
    }

    chartEvolucion = new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
            },
            scales: {
                y: { min: 0, max: 10, ticks: { stepSize: 2 } },
                x: { grid: { display: false } }
            }
        }
    });
}