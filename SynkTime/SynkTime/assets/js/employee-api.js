// assets/js/employee-api.js - ACTUALIZADO PARA TU BD
class EmployeeAPI {
    constructor() {
        this.baseURL = '/SynkTime/SynkTime/api';
        this.employees = [];
        this.formData = {
            establecimientos: [],
            sedes: [],
            empresas: []
        };
    }
    
    /**
     * Obtener todos los empleados con filtros
     */
    async getEmployees(filters = {}, page = 1, limit = 50) {
        try {
            const params = new URLSearchParams({
                page: page,
                limit: limit,
                ...filters
            });
            
            const response = await fetch(`${this.baseURL}/employees.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.employees = result.data;
                return result;
            } else {
                throw new Error(result.message || 'Error obteniendo empleados');
            }
        } catch (error) {
            console.error('Error en getEmployees:', error);
            throw error;
        }
    }
    
    /**
     * Obtener empleado por ID
     */
    async getEmployee(id) {
        try {
            const response = await fetch(`${this.baseURL}/employees.php/${id}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.message || 'Error obteniendo empleado');
            }
        } catch (error) {
            console.error('Error en getEmployee:', error);
            throw error;
        }
    }
    
    /**
     * Crear nuevo empleado
     */
    async createEmployee(employeeData) {
        try {
            const response = await fetch(`${this.baseURL}/employees.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(employeeData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result;
            } else {
                throw new Error(result.message || 'Error creando empleado');
            }
        } catch (error) {
            console.error('Error en createEmployee:', error);
            throw error;
        }
    }
    
    /**
     * Actualizar empleado
     */
    async updateEmployee(id, employeeData) {
        try {
            const response = await fetch(`${this.baseURL}/employees.php/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(employeeData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result;
            } else {
                throw new Error(result.message || 'Error actualizando empleado');
            }
        } catch (error) {
            console.error('Error en updateEmployee:', error);
            throw error;
        }
    }
    
    /**
     * Eliminar empleado
     */
    async deleteEmployee(id) {
        try {
            const response = await fetch(`${this.baseURL}/employees.php/${id}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result;
            } else {
                throw new Error(result.message || 'Error eliminando empleado');
            }
        } catch (error) {
            console.error('Error en deleteEmployee:', error);
            throw error;
        }
    }
    
    /**
     * Obtener datos para formularios
     */
    async getFormData() {
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
            console.error('Error en getFormData:', error);
            throw error;
        }
    }
    
    /**
     * Buscar empleados (wrapper para filtros)
     */
    async searchEmployees(searchFilters) {
        return await this.getEmployees(searchFilters);
    }
}

// Instancia global
const employeeAPI = new EmployeeAPI();