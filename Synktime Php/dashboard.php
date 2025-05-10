<?php include 'layout.php'; ?>

<!-- Agregamos los CDN necesarios -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>

<style>
    .dashboard-container {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: calc(100vh - var(--header-height));
        padding: 1rem;
        overflow: hidden;
        background-color: #f5f7fb;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        height: 30%;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 1.5rem;
        height: 65%;
    }

    .stat-card, .chart-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .stat-card h3 {
        font-size: 1rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .stat-card .value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--dark);
    }

    .chart-card {
        position: relative;
    }

    .chart-container {
        width: 100%;
        height: calc(100% - 40px); /* Ajusta el alto para evitar solapamiento */
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            height: auto;
        }

        .chart-grid {
            grid-template-columns: 1fr;
            height: auto;
        }

        .chart-container {
            height: 300px; /* Altura fija en pantallas pequeñas */
        }
    }
</style>

<div class="dashboard-container">
    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Empleados Activos</h3>
            <div class="value">245</div>
        </div>
        <div class="stat-card">
            <h3>Asistencia Hoy</h3>
            <div class="value">98%</div>
        </div>
        <div class="stat-card">
            <h3>Tardanzas</h3>
            <div class="value">12</div>
        </div>
        <div class="stat-card">
            <h3>Total Horas Trabajadas</h3>
            <div class="value">1,960</div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="chart-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Asistencia Semanal</h3>
            </div>
            <div class="chart-container" id="attendanceChart"></div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Horas por Departamento</h3>
            </div>
            <div class="chart-container" id="departmentChart"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Configuración del gráfico de Asistencia
    const attendanceOptions = {
        series: [{
            name: 'Asistencia',
            data: [92, 95, 88, 97, 94, 91, 93]
        }],
        chart: {
            type: 'area',
            height: '100%',
            toolbar: { show: false }
        },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 3 },
        xaxis: { categories: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] },
        yaxis: { labels: { formatter: val => `${val}%` } },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
                stops: [0, 90, 100]
            }
        }
    };

    // Configuración del gráfico de Departamentos
    const departmentOptions = {
        series: [{
            data: [120, 80, 95, 65, 85]
        }],
        chart: {
            type: 'bar',
            height: '100%',
            toolbar: { show: false }
        },
        colors: ['#0072ff', '#00c6ff', '#0088ff', '#00a1ff', '#00b4ff'],
        plotOptions: {
            bar: { borderRadius: 8, columnWidth: '60%' }
        },
        xaxis: { categories: ['TI', 'RRHH', 'Ventas', 'Marketing', 'Operaciones'] },
        yaxis: { labels: { formatter: val => `${val} hrs` } }
    };

    // Renderizar gráficos
    const attendanceChart = new ApexCharts(document.querySelector('#attendanceChart'), attendanceOptions);
    const departmentChart = new ApexCharts(document.querySelector('#departmentChart'), departmentOptions);

    attendanceChart.render();
    departmentChart.render();
});
</script>