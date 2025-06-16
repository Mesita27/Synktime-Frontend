// assets/js/employee.js - COMPLETAMENTE ACTUALIZADO
class EmployeeManager {
    constructor() {
        this.baseURL = '/SynkTime/SynkTime/api';
        this.employees = [];
        this.formData = {
            empresas: [],
            sedes: [],
            establecimientos: []
        };
        this.currentPage = 1;
        this.totalPages = 1;
        this.currentFilters = {};
        this.editingId = null;
        this.deleteId = null;
    }

    // Inicializar
    async init() {
        try {
            await this.loadFormData();
            await this.populateFormSelects();
            await this.loadEmployees();
            this.setupEventListeners();
        } catch (error) {
            console.error('Error inicializando:', error);
            this.showNotification('Error cargando datos iniciales', 'error');
        }
    }

    // Cargar datos para formularios
    async loadFormData() {
        try {
            const response = await fetch(`${this.baseURL}/employees.php/form-data`);
            const result = await response.json();
            
            if (result.success) {
                this.formData = result.data;
                return result.data;
            } else {
                throw new Error(result.message || 'Error obteniendo datos del formulario');
            }
        } catch (error) {
            console.error('Error en loadFormData:', error);
            throw error;
        }
    }

    // Poblar selectores
    async populateFormSelects() {
        try {
            // Selectores de búsqueda
            this.populateSelect('q_empresa', this.formData.empresas, 'ID_EMPRESA', 'NOMBRE', 'Todas las empresas');
            this.populateSelect('q_sede', this.formData.sedes, 'ID_SEDE', 'NOMBRE', 'Todas las sedes', 'empresa_nombre');
            this.populateSelect('q_establecimiento', this.formData.establecimientos, 'ID_ESTABLECIMIENTO', 'NOMBRE', 'Todos los establecimientos', 'sede_nombre');
            
            // Selectores del formulario
            this.populateSelect('form_empresa', this.formData.empresas, 'ID_EMPRESA', 'NOMBRE', 'Seleccione empresa');
            this.populateSelect('form_sede', this.formData.sedes, 'ID_SEDE', 'NOMBRE', 'Seleccione sede', 'empresa_nombre');
            this.populateSelect('establecimiento', this.formData.establecimientos, 'ID_ESTABLECIMIENTO', 'NOMBRE', 'Seleccione establecimiento', 'sede_nombre');
            
            // Configurar eventos de cascada
            this.setupCascadeEvents();
            
        } catch (error) {
            console.error('Error poblando selects:', error);
        }
    }

    // Poblar un select específico
    populateSelect(selectId, data, valueField, textField, defaultText, extraField = null) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        select.innerHTML = `<option value="">${defaultText}</option>`;
        
        data.forEach(item => {
            const extraText = extraField && item[extraField] ? ` (${item[extraField]})` : '';
            const optionText = `${item[textField]}${extraText}`;
            select.innerHTML += `<option value="${item[valueField]}">${optionText}</option>`;
        });
    }

    // Configurar eventos de cascada
    setupCascadeEvents() {
        // Cascada en búsqueda
        const empresaSelect = document.getElementById('q_empresa');
        const sedeSelect = document.getElementById('q_sede');
        const establecimientoSelect = document.getElementById('q_establecimiento');
        
        if (empresaSelect) {
            empresaSelect.addEventListener('change', (e) => {
                this.filterSedes(e.target.value, 'q_sede');
                this.filterEstablecimientos(null, 'q_establecimiento');
            });
        }
        
        if (sedeSelect) {
            sedeSelect.addEventListener('change', (e) => {
                this.filterEstablecimientos(e.target.value, 'q_establecimiento');
            });
        }
        
        // Cascada en formulario
        const formEmpresaSelect = document.getElementById('form_empresa');
        const formSedeSelect = document.getElementById('form_sede');
        const formEstablecimientoSelect = document.getElementById('establecimiento');
        
        if (formEmpresaSelect) {
            formEmpresaSelect.addEventListener('change', (e) => {
                this.filterSedes(e.target.value, 'form_sede');
                this.filterEstablecimientos(null, 'establecimiento');
            });
        }
        
        if (formSedeSelect) {
            formSedeSelect.addEventListener('change', (e) => {
                this.filterEstablecimientos(e.target.value, 'establecimiento');
            });
        }
    }

    // Filtrar sedes por empresa
    filterSedes(empresaId, targetSelectId) {
        const sedeSelect = document.getElementById(targetSelectId);
        if (!sedeSelect) return;
        
        const defaultText = targetSelectId.includes('q_') ? 'Todas las sedes' : 'Seleccione sede';
        sedeSelect.innerHTML = `<option value="">${defaultText}</option>`;
        
        const filteredSedes = empresaId ? 
            this.formData.sedes.filter(sede => sede.ID_EMPRESA == empresaId) : 
            this.formData.sedes;
        
        filteredSedes.forEach(sede => {
            const extraText = sede.empresa_nombre ? ` (${sede.empresa_nombre})` : '';
            sedeSelect.innerHTML += `<option value="${sede.ID_SEDE}">${sede.NOMBRE}${extraText}</option>`;
        });
    }

    // Filtrar establecimientos por sede
    filterEstablecimientos(sedeId, targetSelectId) {
        const establecimientoSelect = document.getElementById(targetSelectId);
        if (!establecimientoSelect) return;
        
        const defaultText = targetSelectId.includes('q_') ? 'Todos los establecimientos' : 'Seleccione establecimiento';
        establecimientoSelect.innerHTML = `<option value="">${defaultText}</option>`;
        
        const filteredEstablecimientos = sedeId ? 
            this.formData.establecimientos.filter(est => est.ID_SEDE == sedeId) : 
            this.formData.establecimientos;
        
        filteredEstablecimientos.forEach(est => {
            const extraText = est.sede_nombre ? ` (${est.sede_nombre})` : '';
            establecimientoSelect.innerHTML += `<option value="${est.ID_ESTABLECIMIENTO}">${est.NOMBRE}${extraText}</option>`;
        });
    }

    // Cargar empleados
    async loadEmployees(filters = {}, page = 1) {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                page: page,
                limit: 50,
                ...filters
            });
            
            const response = await fetch(`${this.baseURL}/employees.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.employees = result.data;
                this.currentPage = result.pagination.current_page;
                this.totalPages = result.pagination.total_pages;
                this.currentFilters = filters;
                
                this.renderEmployeeTable(this.employees);
                this.updatePaginationInfo(result.pagination);
            } else {
                throw new Error(result.message || 'Error obteniendo empleados');
            }
            
            this.showLoading(false);
            
        } catch (error) {
            this.showLoading(false);
            this.showNotification('Error cargando empleados: ' + error.message, 'error');
            this.renderEmployeeTable([]);
        }
    }

    // Renderizar tabla
    renderEmployeeTable(data = []) {
        const tbody = document.getElementById('employeeTableBody');
        tbody.innerHTML = '';
        
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding: 2rem;">No se encontraron empleados</td></tr>';
            return;
        }
        
        data.forEach((emp) => {
            tbody.innerHTML += `
                <tr data-employee-id="${emp.id}">
                    <td>${emp.codigo}</td>
                    <td>${emp.identificacion}</td>
                    <td>${emp.nombre} ${emp.apellido}</td>
                    <td>${emp.email || '-'}</td>
                    <td>${emp.departamento}</td>
                    <td>${emp.sede}</td>
                    <td>${emp.fecha_contratacion}</td>
                    <td>
                        <span class="${emp.estado === 'Activo' ? 'status-active' : 'status-inactive'}">${emp.estado}</span>
                    </td>
                    <td>
                        <button class="btn-icon btn-edit" data-edit-id="${emp.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" data-delete-id="${emp.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // Configurar eventos
    setupEventListeners() {
        // Búsqueda
        const searchForm = document.getElementById('employeeQueryForm');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => this.handleSearch(e));
        }
        
        // Limpiar búsqueda
        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => this.handleClearSearch(e));
        }
        
        // Formulario de empleado
        const employeeForm = document.getElementById('employeeForm');
        if (employeeForm) {
            employeeForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
        
        // Confirmar eliminación
        const confirmDeleteBtn = document.getElementById('confirmDeleteEmployeeBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => this.handleConfirmDelete());
        }
        
        // Eventos de tabla
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-edit')) {
                const employeeId = e.target.closest('.btn-edit').getAttribute('data-edit-id');
                this.handleEdit(employeeId);
            }
            
            if (e.target.closest('.btn-delete')) {
                const employeeId = e.target.closest('.btn-delete').getAttribute('data-delete-id');
                this.handleDelete(employeeId);
            }
        });
        
        // Botón agregar
        const addBtn = document.getElementById('btnAddEmployee');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.handleAdd());
        }
        
        // Exportar Excel
        const exportBtn = document.getElementById('btnExportXLS');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.handleExport());
        }
        
        // Cerrar modales
        document.addEventListener('mousedown', (e) => {
            document.querySelectorAll('.modal.show').forEach(modal => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // Manejar búsqueda
    async handleSearch(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const filters = {};
        
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                filters[key] = value.trim();
            }
        }
        
        await this.loadEmployees(filters, 1);
    }

    // Limpiar búsqueda
    async handleClearSearch(e) {
        e.preventDefault();
        
        document.getElementById('employeeQueryForm').reset();
        await this.loadEmployees({}, 1);
    }

    // Manejar envío del formulario
    async handleFormSubmit(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(e.target);
            const employeeData = {
                nombre: formData.get('nombre'),
                apellido: formData.get('apellido'),
                dni: formData.get('dni'),
                email: formData.get('email'),
                telefono: formData.get('telefono'),
                id_establecimiento: formData.get('establecimiento'),
                fecha_contratacion: formData.get('fecha_contratacion'),
                estado: formData.get('estado'),
                activo: formData.get('activo')
            };
            
            this.showLoading(true);
            
            let response;
            if (this.editingId) {
                response = await fetch(`${this.baseURL}/employees.php/${this.editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(employeeData)
                });
                this.showNotification('Empleado actualizado exitosamente', 'success');
            } else {
                response = await fetch(`${this.baseURL}/employees.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(employeeData)
                });
                this.showNotification('Empleado creado exitosamente', 'success');
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message);
            }
            
            this.closeEmployeeModal();
            await this.loadEmployees(this.currentFilters, this.currentPage);
            this.showLoading(false);
            
        } catch (error) {
            this.showLoading(false);
            this.showNotification('Error guardando empleado: ' + error.message, 'error');
        }
    }

    // Manejar adición
    handleAdd() {
        this.editingId = null;
        document.getElementById('employeeForm').reset();
        document.getElementById('employeeModalTitle').textContent = "Registrar empleado";
        document.getElementById('employeeModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    // Manejar edición
    async handleEdit(employeeId) {
        try {
            this.showLoading(true);
            
            const response = await fetch(`${this.baseURL}/employees.php/${employeeId}`);
            const result = await response.json();
            
            if (result.success) {
                const employee = result.data;
                this.populateEditForm(employee);
                
                this.editingId = employeeId;
                document.getElementById('employeeModalTitle').textContent = "Editar empleado";
                document.getElementById('employeeModal').classList.add('show');
                document.body.style.overflow = 'hidden';
            } else {
                throw new Error(result.message);
            }
            
            this.showLoading(false);
            
        } catch (error) {
            this.showLoading(false);
            this.showNotification('Error cargando datos del empleado: ' + error.message, 'error');
        }
    }

    // Poblar formulario de edición
    populateEditForm(employee) {
        const form = document.getElementById('employeeForm');
        if (!form) return;
        
        form.nombre.value = employee.nombre || '';
        form.apellido.value = employee.apellido || '';
        form.dni.value = employee.identificacion || '';
        form.email.value = employee.email || '';
        form.telefono.value = employee.telefono || '';
        form.fecha_contratacion.value = employee.fecha_contratacion || '';
        form.estado.value = employee.estado || 'Activo';
        form.activo.value = employee.activo || 'Si';
        
        // Cascada de selects
        if (employee.id_empresa) {
            document.getElementById('form_empresa').value = employee.id_empresa;
            this.filterSedes(employee.id_empresa, 'form_sede');
            
            setTimeout(() => {
                if (employee.id_sede) {
                    document.getElementById('form_sede').value = employee.id_sede;
                    this.filterEstablecimientos(employee.id_sede, 'establecimiento');
                    
                    setTimeout(() => {
                        if (employee.id_establecimiento) {
                            document.getElementById('establecimiento').value = employee.id_establecimiento;
                        }
                    }, 100);
                }
            }, 100);
        }
    }

    // Manejar eliminación
    handleDelete(employeeId) {
        this.deleteId = employeeId;
        document.getElementById('deleteEmployeeModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    // Confirmar eliminación
    async handleConfirmDelete() {
        if (!this.deleteId) return;
        
        try {
            this.showLoading(true);
            
            const response = await fetch(`${this.baseURL}/employees.php/${this.deleteId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Empleado eliminado exitosamente', 'success');
                this.closeDeleteEmployeeModal();
                await this.loadEmployees(this.currentFilters, this.currentPage);
            } else {
                throw new Error(result.message);
            }
            
            this.deleteId = null;
            this.showLoading(false);
            
        } catch (error) {
            this.showLoading(false);
            this.showNotification('Error eliminando empleado: ' + error.message, 'error');
        }
    }

    // Cerrar modal de empleado
    closeEmployeeModal() {
        document.getElementById('employeeModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Cerrar modal de eliminación
    closeDeleteEmployeeModal() {
        document.getElementById('deleteEmployeeModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Mostrar notificación
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Mostrar/ocultar loading
    showLoading(show) {
        let loader = document.getElementById('pageLoader');
        
        if (show && !loader) {
            loader = document.createElement('div');
            loader.id = 'pageLoader';
            loader.className = 'page-loader';
            loader.innerHTML = '<div class="loader-spinner"></div><span>Cargando...</span>';
            document.body.appendChild(loader);
        } else if (!show && loader) {
            loader.remove();
        }
    }

    // Actualizar información de paginación
    updatePaginationInfo(pagination) {
        const info = document.getElementById('paginationInfo');
        if (info) {
            info.textContent = `Página ${pagination.current_page} de ${pagination.total_pages} - Total: ${pagination.total} empleados`;
        }
    }

    // Exportar a Excel
    handleExport() {
        if (this.employees.length === 0) {
            this.showNotification('No hay datos para exportar', 'warning');
            return;
        }
        
        try {
            const table = document.querySelector('.employee-table');
            if (!table) return;
            
            if (typeof XLSX === 'undefined') {
                this.showNotification("Librería de Excel no está cargada", 'error');
                return;
            }
            
            const wb = XLSX.utils.table_to_book(table, { sheet: "Empleados" });
            XLSX.writeFile(wb, `empleados_${new Date().toISOString().split('T')[0]}.xlsx`);
            
            this.showNotification('Archivo exportado exitosamente', 'success');
        } catch (error) {
            this.showNotification('Error exportando archivo: ' + error.message, 'error');
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const employeeManager = new EmployeeManager();
    employeeManager.init();
    
    // Hacer disponible globalmente para funciones de modal
    window.employeeManager = employeeManager;
    window.closeEmployeeModal = () => employeeManager.closeEmployeeModal();
    window.closeDeleteEmployeeModal = () => employeeManager.closeDeleteEmployeeModal();
});