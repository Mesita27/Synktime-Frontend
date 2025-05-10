<?php include 'layout.php'; ?>

<style>
    .employees-container {
        padding: 20px;
        margin-top: var(--header-height); /* Ajuste para el header fijo */
    }

    .employees-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .employees-table-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 1.5rem;
        overflow-x: auto;
    }

    .employees-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px; /* Asegura que la tabla no se comprima demasiado */
    }

    .employees-table th,
    .employees-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .employees-table th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
    }

    .employees-table tr:hover {
        background-color: #f8f9fa;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-active {
        background-color: #e1f7e1;
        color: #0a6b0a;
    }

    .status-inactive {
        background-color: #ffe1e1;
        color: #6b0a0a;
    }

    .action-icon {
        color: var(--primary);
        cursor: pointer;
        margin: 0 0.5rem;
        font-size: 1.1rem;
        transition: transform 0.2s ease;
    }

    .action-icon:hover {
        transform: scale(1.2);
    }

    /* Estilos para el modal */
    .modal-content {
        width: 90%;
        max-width: 500px;
        margin: 2rem auto;
    }

    @media (max-width: 768px) {
        .employees-container {
            padding: 15px;
        }

        .employees-header {
            flex-direction: column;
            gap: 1rem;
        }

        .employees-table-container {
            padding: 1rem;
        }

        .modal-content {
            width: 95%;
            margin: 1rem auto;
        }
    }
</style>

<div class="employees-container">
    <div class="employees-header">
        <h2>Lista de Empleados</h2>
        <button class="action-button" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Nuevo Empleado
        </button>
    </div>

    <div class="employees-table-container">
        <table class="employees-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>001</td>
                    <td>Juan Pérez</td>
                    <td>TI</td>
                    <td>juan.perez@empresa.com</td>
                    <td><span class="status-badge status-active">Activo</span></td>
                    <td>
                        <i class="fas fa-edit action-icon" onclick="showEditModal(1)"></i>
                        <i class="fas fa-trash action-icon" onclick="showDeleteModal(1)" style="color: #dc3545;"></i>
                    </td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>María García</td>
                    <td>RRHH</td>
                    <td>maria.garcia@empresa.com</td>
                    <td><span class="status-badge status-active">Activo</span></td>
                    <td>
                        <i class="fas fa-edit action-icon" onclick="showEditModal(2)"></i>
                        <i class="fas fa-trash action-icon" onclick="showDeleteModal(2)" style="color: #dc3545;"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para nuevo/editar empleado -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Nuevo Empleado</h2>
        <form id="employeeForm">
            <div class="form-group">
                <label for="employeeName">Nombre Completo</label>
                <input type="text" id="employeeName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="department">Departamento</label>
                <select id="department" class="form-control" required>
                    <option value="">Seleccione un departamento</option>
                    <option value="TI">TI</option>
                    <option value="RRHH">RRHH</option>
                    <option value="Ventas">Ventas</option>
                    <option value="Marketing">Marketing</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" class="form-control" required>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="action-button">Guardar</button>
        </form>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h2>Confirmar Eliminación</h2>
        <p>¿Está seguro que desea eliminar este empleado?</p>
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
            <button class="action-button" onclick="closeModal()" style="background: #6c757d">Cancelar</button>
            <button class="action-button" onclick="deleteEmployee()" style="background: #dc3545">Eliminar</button>
        </div>
    </div>
</div>

<script>
    // Funciones para los modales
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Nuevo Empleado';
        document.getElementById('employeeForm').reset();
        document.getElementById('employeeModal').style.display = 'block';
    }

    function showEditModal(id) {
        document.getElementById('modalTitle').textContent = 'Editar Empleado';
        document.getElementById('employeeModal').style.display = 'block';
        // Aquí irían los datos del empleado a editar
    }

    function showDeleteModal(id) {
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('employeeModal').style.display = 'none';
        document.getElementById('deleteModal').style.display = 'none';
    }

    function deleteEmployee() {
        // Aquí iría la lógica para eliminar el empleado
        closeModal();
    }

    // Manejo del formulario
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Aquí iría la lógica para guardar los datos
        closeModal();
    });
</script>