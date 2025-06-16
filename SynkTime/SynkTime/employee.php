<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados | SynkTime</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/employee.css">
    <link rel="stylesheet" href="assets/css/employee-api.css">
</head>
<body>
<div class="app-container">
    <?php include 'components/sidebar.php'; ?>
    <div class="main-wrapper">
        <?php include 'components/header.php'; ?>
        <main class="main-content">
            <div class="employee-header">
                <h2 class="page-title"><i class="fas fa-users"></i> Empleados</h2>
                <div class="employee-actions">
                    <button class="btn-primary" id="btnAddEmployee">
                        <i class="fas fa-user-plus"></i> Registrar empleado
                    </button>
                    <button class="btn-secondary" id="btnExportXLS">
                        <i class="fas fa-file-excel"></i> Exportar .xls
                    </button>
                </div>
            </div>
            
            <!-- DEBUG INFO -->
            <div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                <h4>🔍 Debug Info</h4>
                <p><strong>Fecha:</strong> 2025-06-16 08:53:45</p>
                <p><strong>Usuario:</strong> Mesita27</p>
                <p><strong>API URL:</strong> <code>/SynkTime/SynkTime/api/employees.php</code></p>
                <p><strong>Status:</strong> <span id="apiStatus">Conectando...</span></p>
            </div>
            
            <?php include 'components/employee_query.php'; ?>
            
            <div class="employee-table-container">
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Departamento</th>
                            <th>Sede</th>
                            <th>Fecha contratación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        <tr>
                            <td colspan="9" style="text-align:center; padding: 2rem;">
                                🔄 Cargando empleados...
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="paginationInfo"></div>
            </div>
            
            <?php include 'components/employee_modals.php'; ?>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/layout.js"></script>
<script src="assets/js/employee-debug.js"></script>
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<script>
// Update API status
document.addEventListener('DOMContentLoaded', function() {
    const statusEl = document.getElementById('apiStatus');
    
    fetch('/SynkTime/SynkTime/api/employees.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.innerHTML = '✅ Conectado - ' + data.data.length + ' empleados encontrados';
                statusEl.style.color = 'green';
            } else {
                statusEl.innerHTML = '❌ Error: ' + data.message;
                statusEl.style.color = 'red';
            }
        })
        .catch(error => {
            statusEl.innerHTML = '💥 Error de conexión: ' + error.message;
            statusEl.style.color = 'red';
        });
});
</script>
</body>
</html>