<?php
// components/employee_modals.php - ACTUALIZADO
?>
<!-- Modal Registrar/Editar Empleado -->
<div class="modal" id="employeeModal">
  <div class="modal-content modal-content-md">
    <button type="button" class="modal-close" aria-label="Cerrar" onclick="closeEmployeeModal()">
      <i class="fas fa-times"></i>
    </button>
    <h3 id="employeeModalTitle">Registrar empleado</h3>
    <form id="employeeForm" autocomplete="off">
      <div class="form-row">
        <div class="form-group">
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
          <label for="apellido">Apellido *</label>
          <input type="text" id="apellido" name="apellido" required>
        </div>
        <div class="form-group">
          <label for="dni">DNI *</label>
          <input type="text" id="dni" name="dni" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email">
        </div>
        <div class="form-group">
          <label for="telefono">Teléfono</label>
          <input type="text" id="telefono" name="telefono">
        </div>
        <div class="form-group">
          <label for="fecha_contratacion">Fecha de ingreso *</label>
          <input type="date" id="fecha_contratacion" name="fecha_contratacion" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="form_empresa">Empresa</label>
          <select id="form_empresa" name="form_empresa">
            <option value="">Seleccione empresa</option>
          </select>
        </div>
        <div class="form-group">
          <label for="form_sede">Sede</label>
          <select id="form_sede" name="form_sede">
            <option value="">Seleccione sede</option>
          </select>
        </div>
        <div class="form-group">
          <label for="establecimiento">Establecimiento</label>
          <select id="establecimiento" name="establecimiento">
            <option value="">Seleccione establecimiento</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>
        <div class="form-group">
          <label for="activo">Activo</label>
          <select id="activo" name="activo">
            <option value="Si">Si</option>
            <option value="No">No</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn-primary" id="saveEmployeeBtn">Guardar</button>
        <button type="button" class="btn-secondary" onclick="closeEmployeeModal()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Eliminar Empleado -->
<div class="modal" id="deleteEmployeeModal">
  <div class="modal-content modal-content-sm">
    <button type="button" class="modal-close" aria-label="Cerrar" onclick="closeDeleteEmployeeModal()">
      <i class="fas fa-times"></i>
    </button>
    <h3>Eliminar empleado</h3>
    <p>¿Estás seguro de que deseas eliminar este empleado?</p>
    <div class="form-actions">
      <button class="btn-danger" id="confirmDeleteEmployeeBtn">Eliminar</button>
      <button class="btn-secondary" onclick="closeDeleteEmployeeModal()">Cancelar</button>
    </div>
  </div>
</div>