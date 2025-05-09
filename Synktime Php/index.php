<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SynkTime - Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="app-content" id="app-content">
            <!-- Header -->
            <header class="app-header">
                <div class="app-header-left">
                    <button class="sidebar-toggle" id="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="app-header-right">
                    <div class="sede-info">
                        <span class="sede-label">Sede actual:</span>
                        <span class="sede-name">Sede Principal</span>
                    </div>
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-menu">
                        <button id="user-dropdown">
                            <div class="user-image">JD</div>
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <div class="content-wrapper">
                <!-- Welcome Banner -->
                <div class="app-card fade-in" style="background: var(--primary-gradient); color: white; margin-bottom: 2rem;">
                    <div class="app-card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Bienvenido, Juan Pérez</h2>
                                <p style="opacity: 0.9;">Resumen del sistema de asistencia para el día de hoy, <?php echo date('d/m/Y'); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Empresa: Tecnologías Avanzadas S.A.</h3>
                                <p style="opacity: 0.9;">Tu último acceso fue: <?php echo date('d/m/Y H:i', strtotime('-1 day')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid fade-in">
                    <div class="stats-card">
                        <div class="stats-icon primary">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stats-info">
                            <h4>85%</h4>
                            <p>Asistencia Hoy</p>
                        </div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="stats-icon success">
                            <i class="fas fa-business-time"></i>
                        </div>
                        <div class="stats-info">
                            <h4>134</h4>
                            <p>Total Empleados</p>
                        </div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="stats-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-info">
                            <h4>12</h4>
                            <p>Tardanzas Hoy</p>
                        </div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="stats-icon danger">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="stats-info">
                            <h4>8</h4>
                            <p>Ausencias Hoy</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="app-card fade-in">
                    <div class="app-card-header">
                        <h3 class="app-card-title">Estadísticas de Asistencia</h3>
                        <div>
                            <select class="app-form-control" style="width: auto;">
                                <option value="semana">Esta Semana</option>
                                <option value="mes">Este Mes</option>
                                <option value="trimestre">Este Trimestre</option>
                            </select>
                        </div>
                    </div>
                    <div class="app-card-body">
                        <div class="chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="row" style="display: flex; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="app-card fade-in" style="flex: 1;">
                        <div class="app-card-header">
                            <h3 class="app-card-title">Distribución por Sede</h3>
                        </div>
                        <div class="app-card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="locationChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="app-card fade-in" style="flex: 1;">
                        <div class="app-card-header">
                            <h3 class="app-card-title">Estado de Dispositivos</h3>
                        </div>
                        <div class="app-card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="devicesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="app-card fade-in">
                    <div class="app-card-header">
                        <h3 class="app-card-title">Actividad Reciente</h3>
                        <a href="modules/asistencias/index.php" class="btn btn-primary" style="font-size: 0.875rem;">Ver Todo</a>
                    </div>
                    <div class="app-card-body">
                        <div class="table-container">
                            <table class="app-table" id="recentActivityTable">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th>Tipo</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th>Dispositivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>María López</td>
                                        <td>Entrada</td>
                                        <td>08:02 AM</td>
                                        <td><span class="badge" style="background-color: #27ae60; color: white; padding: 3px 8px; border-radius: 4px;">Puntual</span></td>
                                        <td>Bio Principal</td>
                                    </tr>
                                    <tr>
                                        <td>Carlos Ramírez</td>
                                        <td>Entrada</td>
                                        <td>08:15 AM</td>
                                        <td><span class="badge" style="background-color: #f39c12; color: white; padding: 3px 8px; border-radius: 4px;">Tardanza</span></td>
                                        <td>Bio Entrada</td>
                                    </tr>
                                    <tr>
                                        <td>Andrea Suárez</td>
                                        <td>Salida</td>
                                        <td>05:30 PM</td>
                                        <td><span class="badge" style="background-color: #27ae60; color: white; padding: 3px 8px; border-radius: 4px;">A tiempo</span></td>
                                        <td>Bio Salida</td>
                                    </tr>
                                    <tr>
                                        <td>Roberto García</td>
                                        <td>Entrada</td>
                                        <td>08:45 AM</td>
                                        <td><span class="badge" style="background-color: #e74c3c; color: white; padding: 3px 8px; border-radius: 4px;">Tardanza</span></td>
                                        <td>RFID Lobby</td>
                                    </tr>
                                    <tr>
                                        <td>Sofía Mendoza</td>
                                        <td>Ausencia</td>
                                        <td>--:--</td>
                                        <td><span class="badge" style="background-color: #7f8c8d; color: white; padding: 3px 8px; border-radius: 4px;">Justificada</span></td>
                                        <td>--</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle sidebar on mobile
            $('#sidebar-toggle').click(function() {
                $('body').toggleClass('sidebar-mobile-open');
            });
            
            // DataTables
            $('#recentActivityTable').DataTable({
                paging: false,
                searching: false,
                info: false
            });
            
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                    datasets: [
                        {
                            label: 'Asistencia',
                            data: [120, 118, 115, 125, 112, 60, 18],
                            borderColor: '#0072ff',
                            backgroundColor: 'rgba(0, 114, 255, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Tardanzas',
                            data: [8, 12, 15, 10, 18, 5, 2],
                            borderColor: '#f39c12',
                            backgroundColor: 'rgba(243, 156, 18, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Ausencias',
                            data: [6, 4, 4, 7, 4, 0, 1],
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Location Chart
            const locationCtx = document.getElementById('locationChart').getContext('2d');
            const locationChart = new Chart(locationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Sede Principal', 'Sede Norte', 'Sede Sur', 'Sede Este'],
                    datasets: [{
                        data: [60, 25, 30, 19],
                        backgroundColor: [
                            '#0072ff',
                            '#00c6ff',
                            '#27ae60',
                            '#3498db'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // Devices Chart
            const devicesCtx = document.getElementById('devicesChart').getContext('2d');
            const devicesChart = new Chart(devicesCtx, {
                type: 'bar',
                data: {
                    labels: ['Biométricos', 'RFID', 'QR', 'Manual'],
                    datasets: [
                        {
                            label: 'Activos',
                            data: [8, 4, 3, 1],
                            backgroundColor: '#27ae60',
                        },
                        {
                            label: 'Inactivos',
                            data: [1, 0, 1, 0],
                            backgroundColor: '#e74c3c',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>