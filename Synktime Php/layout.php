<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SynkTime - Sistema de Control de Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0072ff;
            --primary-light: #00c6ff;
            --primary-gradient: linear-gradient(135deg, #0072ff, #00c6ff);
            --primary-hover: linear-gradient(135deg, #005bcf, #00a1d6);
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 900;
            transition: all 0.3s ease;
        }

        .header.sidebar-collapsed {
            left: var(--sidebar-collapsed-width);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-title {
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            color: #2c3e50;
            font-weight: 500;
        }

        .current-datetime {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-container {
            transition: all 0.3s ease;
        }

        .logo-img {
            max-width: 200px;
            height: auto;
            transition: all 0.3s ease;
        }

        .collapsed .logo-img {
            max-width: 40px;
        }

        .nav-menu {
            padding: 1rem 0;
            list-style: none;
        }

        .nav-item {
            margin: 0.5rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-gradient);
            color: white;
        }

        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .collapsed .nav-link span {
            display: none;
        }

        .nav-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .collapsed .nav-section-title {
            display: none;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            transition: all 0.3s ease;
        }

        .main-content.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.mobile-active {
                transform: translateX(0);
            }

            .main-content, .header {
                margin-left: 0 !important;
                left: 0 !important;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .collapsed .nav-link span {
                display: inline;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 1rem;
            }

            .header-title {
                font-size: 1.2rem;
            }

            .current-datetime {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="assets/img/synktime-logo.png" alt="SynkTime Logo" class="logo-img">
            </div>
        </div>
        <nav class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Gestión de Personal</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="employees.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Empleados</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="departments.php" class="nav-link">
                            <i class="fas fa-building"></i>
                            <span>Departamentos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="positions.php" class="nav-link">
                            <i class="fas fa-id-badge"></i>
                            <span>Cargos</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Control de Asistencia</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-clock"></i>
                            <span>Registro de Asistencia</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="schedules.php" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Horarios</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="leaves.php" class="nav-link">
                            <i class="fas fa-calendar-minus"></i>
                            <span>Permisos y Ausencias</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Reportes</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="attendance-reports.php" class="nav-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Reportes de Asistencia</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="employee-reports.php" class="nav-link">
                            <i class="fas fa-user-clock"></i>
                            <span>Reportes por Empleado</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Configuración</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span>Configuración General</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="fas fa-user-shield"></i>
                            <span>Usuarios del Sistema</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">Dashboard</h1>
        </div>
        <div class="header-right">
            <div class="current-datetime" id="currentDateTime"></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Mesita27'); ?></span>
                <button class="action-button" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Content will be injected here -->
    </main>

    <script>
        // Toggle Sidebar
        const toggleBtn = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const header = document.querySelector('.header');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');

        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                sidebar.classList.toggle('mobile-active');
                sidebarOverlay.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                header.classList.toggle('sidebar-collapsed');
            }
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-active');
            sidebarOverlay.classList.remove('active');
        });

        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const dateTimeStr = now.toLocaleString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            document.getElementById('currentDateTime').textContent = dateTimeStr;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Set active navigation item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        // Logout Function
        function logout() {
            window.location.href = 'login.php';
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                sidebarOverlay.classList.remove('active');
                sidebar.classList.remove('mobile-active');
            }
        });
    </script>
</body>
</html>