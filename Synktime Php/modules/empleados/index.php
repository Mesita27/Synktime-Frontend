<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/db_connect.php';

$empresa_id = $_SESSION['empresa_id'];

// Consulta de trabajadores y sus horarios
$sql = "SELECT 
            t.id_trabajador,
            t.nombre,
            t.apellido,
            t.identificacion,
            d.nombre AS departamento,
            s.nombre AS sede,
            h.nombre AS horario,
            th.fecha_inicio,
            th.fecha_fin,
            th.estado
        FROM trabajadores t
        INNER JOIN sedes s ON t.id_sede = s.id_sede
        INNER JOIN negocios n ON s.id_negocio = n.id_negocio
        INNER JOIN empresas e ON n.id_empresa = e.id_empresa
        LEFT JOIN departamentos d ON t.id_departamento = d.id_departamento
        LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
        LEFT JOIN horarios h ON th.id_horario = h.id_horario
        WHERE e.id_empresa = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$result = $stmt->get_result();

$empleados = [];
while ($row = $result->fetch_assoc()) {
    $empleados[] = $row;
}

$stmt->close();
?>

<!-- Custom CSS for employee cards -->
<style>
.employee-card {
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.employee-image {
    height: 120px;
    width: 120px;
    object-fit: cover;
    border-radius: 50%;
    margin: 15px auto;
    border: 3px solid #f8f9fa;
}

.action-buttons .btn {
    margin-right: 5px;
}

.add-employee-card {
    height: 100%;
    border: 2px dashed #dee2e6;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-employee-card:hover {
    border-color: #6c757d;
    background-color: #f8f9fa;
}

.search-bar {
    margin-bottom: 20px;
}
</style>

<div class="container-fluid">
    <!-- Header with add button and search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Empleados</h1>
            <p class="mb-0">Gestión de trabajadores de la sede</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-user-plus"></i> Nuevo Empleado
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-bar input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchEmployees" placeholder="Buscar empleados...">
            </div>
        </div>
    </div>

    <!-- Employee Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="employeesList">
        <?php foreach ($empleados as $empleado): ?>
        <div class="col employee-item">
            <div class="card employee-card h-100">
                <div class="text-center">
                    <?php if (!empty($empleado['foto']) && file_exists('../../assets/img/empleados/' . $empleado['foto'])): ?>
                        <img src="../../assets/img/empleados/<?= htmlspecialchars($empleado['foto']) ?>" class="employee-image" alt="<?= htmlspecialchars($empleado['nombre']) ?>">
                    <?php else: ?>
                        <img src="../../assets/img/default-avatar.png" class="employee-image" alt="Default Avatar">
                    <?php endif; ?>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($empleado['nombre']) ?> <?= htmlspecialchars($empleado['apellido']) ?></h5>
                    <p class="card-text mb-1">
                        <i class="fas fa-id-card me-2"></i> <?= htmlspecialchars($empleado['documento']) ?>
                    </p>
                    <?php if ($empleado['horario_nombre']): ?>
                    <p class="card-text mb-1">
                        <i class="fas fa-clock me-2"></i> <?= htmlspecialchars($empleado['horario_nombre']) ?>
                    </p>
                    <?php else: ?>
                    <p class="card-text mb-1 text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Sin horario asignado
                    </p>
                    <?php endif; ?>
                    <p class="card-text mb-1">
                        <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($empleado['email']) ?>
                    </p>
                    <p class="card-text">
                        <i class="fas fa-phone me-2"></i> <?= htmlspecialchars($empleado['telefono']) ?>
                    </p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-center action-buttons">
                        <button class="btn btn-sm btn-info view-employee" data-id="<?= $empleado['id'] ?>" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary edit-employee" data-id="<?= $empleado['id'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-success assign-schedule" data-id="<?= $empleado['id'] ?>" title="Asignar horario">
                            <i class="fas fa-clock"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-employee" data-id="<?= $empleado['id'] ?>" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- No employees message -->
    <?php if (count($empleados) == 0): ?>
    <div class="alert alert-info mt-4">
        No hay empleados registrados en esta sede. Haga clic en "Nuevo Empleado" para agregar uno.
    </div>
    <?php endif; ?>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Registrar Nuevo Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="crear.php" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PAS">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="documento" class="form-label">Número de Documento *</label>
                            <input type="text" class="form-control" id="documento" name="documento" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                        <div class="col-md-6">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-select" id="genero" name="genero">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>
                        <div class="col-md-6">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
                            <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion">
                        </div>
                        <div class="col-md-6">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="id_sede" value="<?= $_SESSION['id_sede'] ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="addEmployeeForm" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">Detalles del Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewEmployeeContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Editar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editEmployeeContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveEditEmployee">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Schedule Modal -->
<div class="modal fade" id="assignScheduleModal" tabindex="-1" aria-labelledby="assignScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignScheduleModalLabel">Asignar Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignScheduleForm">
                    <input type="hidden" id="employeeId" name="employeeId">
                    <div class="mb-3">
                        <label for="scheduleId" class="form-label">Horario</label>
                        <select class="form-select" id="scheduleId" name="scheduleId" required>
                            <option value="">Seleccione un horario...</option>
                            <?php
                            // Get all active schedules
                            $query = "SELECT id, nombre, hora_entrada, hora_salida FROM horarios WHERE activo = 1 AND id_sede = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $sede_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre']) . ' (' . 
                                    htmlspecialchars($row['hora_entrada']) . ' - ' . htmlspecialchars($row['hora_salida']) . ')</option>';
                            }
                            $stmt->close();
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveScheduleAssignment">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este empleado? Esta acción no se puede deshacer.</p>
                <input type="hidden" id="deleteEmployeeId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEmployee">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Page specific scripts -->
<?php
$page_scripts = '
<script>
$(document).ready(function() {
    // Search functionality
    $("#searchEmployees").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".employee-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // View employee details
    $(".view-employee").on("click", function() {
        var employeeId = $(this).data("id");
        $("#viewEmployeeContent").html(\'<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>\');
        $("#viewEmployeeModal").modal("show");
        
        $.ajax({
            url: "consultar.php",
            type: "GET",
            data: { id: employeeId, format: "ajax" },
            success: function(response) {
                $("#viewEmployeeContent").html(response);
            },
            error: function() {
                $("#viewEmployeeContent").html(\'<div class="alert alert-danger">Error al cargar la información del empleado.</div>\');
            }
        });
    });
    
    // Edit employee
    $(".edit-employee").on("click", function() {
        var employeeId = $(this).data("id");
        $("#editEmployeeContent").html(\'<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>\');
        $("#editEmployeeModal").modal("show");
        
        $.ajax({
            url: "editar.php",
            type: "GET",
            data: { id: employeeId, format: "ajax" },
            success: function(response) {
                $("#editEmployeeContent").html(response);
            },
            error: function() {
                $("#editEmployeeContent").html(\'<div class="alert alert-danger">Error al cargar la información del empleado.</div>\');
            }
        });
    });
    
    // Save edited employee
    $("#saveEditEmployee").on("click", function() {
        $("#editEmployeeForm").submit();
    });
    
    // Assign schedule to employee
    $(".assign-schedule").on("click", function() {
        var employeeId = $(this).data("id");
        $("#employeeId").val(employeeId);
        $("#assignScheduleModal").modal("show");
    });
    
    // Save schedule assignment
    $("#saveScheduleAssignment").on("click", function() {
        var employeeId = $("#employeeId").val();
        var scheduleId = $("#scheduleId").val();
        
        if (!scheduleId) {
            alert("Por favor seleccione un horario");
            return;
        }
        
        $.ajax({
            url: "../horarios/asignar.php",
            type: "POST",
            data: {
                id_trabajador: employeeId,
                id_horario: scheduleId,
                action: "assign"
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status === "success") {
                    $("#assignScheduleModal").modal("hide");
                    Swal.fire({
                        title: "¡Éxito!",
                        text: "Horario asignado correctamente",
                        icon: "success",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: result.message || "Error al asignar el horario",
                        icon: "error",
                        confirmButtonText: "Aceptar"
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: "Error",
                    text: "Error de conexión",
                    icon: "error",
                    confirmButtonText: "Aceptar"
                });
            }
        });
    });
    
    // Delete employee
    $(".delete-employee").on("click", function() {
        var employeeId = $(this).data("id");
        $("#deleteEmployeeId").val(employeeId);
        $("#deleteEmployeeModal").modal("show");
    });
    
    // Confirm delete employee
    $("#confirmDeleteEmployee").on("click", function() {
        var employeeId = $("#deleteEmployeeId").val();
        
        $.ajax({
            url: "eliminar.php",
            type: "POST",
            data: {
                id: employeeId
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status === "success") {
                    $("#deleteEmployeeModal").modal("hide");
                    Swal.fire({
                        title: "¡Éxito!",
                        text: "Empleado eliminado correctamente",
                        icon: "success",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: result.message || "Error al eliminar el empleado",
                        icon: "error",
                        confirmButtonText: "Aceptar"
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: "Error",
                    text: "Error de conexión",
                    icon: "error",
                    confirmButtonText: "Aceptar"
                });
            }
        });
    });
});
</script>
';
?>

<?php
// Include footer
require_once '../../includes/footer.php';
?>