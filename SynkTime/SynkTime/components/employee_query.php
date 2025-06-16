<?php
// components/employee_query.php - ACTUALIZADO CON SELECTORES
?>
<div class="employee-query-box">
    <form id="employeeQueryForm" class="employee-query-form" method="get" action="">
        <div class="query-row">
            <div class="form-group">
                <label for="q_codigo">Código</label>
                <input type="text" name="codigo" id="q_codigo" placeholder="EMP001">
            </div>
            <div class="form-group">
                <label for="q_identificacion">Identificación</label>
                <input type="text" name="identificacion" id="q_identificacion" placeholder="DNI">
            </div>
            <div class="form-group">
                <label for="q_nombre">Nombre</label>
                <input type="text" name="nombre" id="q_nombre" placeholder="Nombre completo">
            </div>
            <div class="form-group">
                <label for="q_departamento">Departamento</label>
                <input type="text" name="departamento" id="q_departamento" placeholder="Establecimiento">
            </div>
        </div>
        <div class="query-row">
            <div class="form-group">
                <label for="q_empresa">Empresa</label>
                <select name="id_empresa" id="q_empresa">
                    <option value="">Todas las empresas</option>
                </select>
            </div>
            <div class="form-group">
                <label for="q_sede">Sede</label>
                <select name="id_sede" id="q_sede">
                    <option value="">Todas las sedes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="q_establecimiento">Establecimiento</label>
                <select name="id_establecimiento" id="q_establecimiento">
                    <option value="">Todos los establecimientos</option>
                </select>
            </div>
            <div class="form-group">
                <label for="q_estado">Estado</label>
                <select name="estado" id="q_estado">
                    <option value="">Todos</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="query-btns">
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button type="button" class="btn-secondary" id="btnClearSearch">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </form>
</div>