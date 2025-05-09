<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SynkTime</h2>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">JD</div>
        <div class="user-name">Juan Pérez</div>
        <div class="user-role">Administrador</div>
        <div class="user-company">Tecnologías Avanzadas S.A.</div>
    </div>
    
    <div class="sidebar-content">
        <ul class="sidebar-list">
            <li class="sidebar-list-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="sidebar-list-item <?php echo strpos($_SERVER['PHP_SELF'], 'modules/empleados') !== false ? 'active' : ''; ?>">
                <a href="modules/empleados/index.php">
                    <i class="fas fa-users"></i>
                    Empleados
                </a>
            </li>
            <li class="sidebar-list-item <?php echo strpos($_SERVER['PHP_SELF'], 'modules/asistencias') !== false ? 'active' : ''; ?>">
                <a href="modules/asistencias/index.php">
                    <i class="fas fa-user-check"></i>
                    Asistencias
                </a>
            </li>
            <li class="sidebar-list-item <?php echo strpos($_SERVER['PHP_SELF'], 'modules/horarios') !== false ? 'active' : ''; ?>">
                <a href="modules/horarios/index.php">
                    <i class="fas fa-clock"></i>
                    Horarios
                </a>
            </li>
            <li class="sidebar-list-item <?php echo strpos($_SERVER['PHP_SELF'], 'modules/reportes') !== false ? 'active' : ''; ?>">
                <a href="modules/reportes/index.php">
                    <i class="fas fa-chart-bar"></i>
                    Reportes
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <i class="fas fa-building"></i>
                    Negocios
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <i class="fas fa-map-marker-alt"></i>
                    Sedes
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <i class="fas fa-tablet-alt"></i>
                    Dispositivos
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <i class="fas fa-cog"></i>
                    Configuración
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <a href="login.php" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</aside>