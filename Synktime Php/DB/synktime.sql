CREATE DATABASE IF NOT EXISTS synktime;
USE synktime;
-- Estructura de la base de datos para SynkTime

-- Tabla de Empresas
CREATE TABLE empresas (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    razon_social VARCHAR(150) NOT NULL,
    nit VARCHAR(20) NOT NULL UNIQUE,
    direccion VARCHAR(150),
    telefono VARCHAR(20),
    email VARCHAR(100),
    logo VARCHAR(255),
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Negocios
CREATE TABLE negocios (
    id_negocio INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    telefono VARCHAR(20),
    email VARCHAR(100),
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- Tabla de Sedes
CREATE TABLE sedes (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    id_negocio INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_negocio) REFERENCES negocios(id_negocio) ON DELETE CASCADE
);

-- Tabla de Roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
);

-- Insertar roles predeterminados
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Control total del sistema'),
('Gerente', 'Gestión de negocios y sedes'),
('Supervisor', 'Gestión de trabajadores y horarios'),
('Trabajador', 'Solo registro de asistencia');

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    id_rol INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- Tabla de Asignaciones de Usuario a Sede
CREATE TABLE usuario_sede (
    id_usuario INT NOT NULL,
    id_sede INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_sede),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE CASCADE
);

-- Tabla de Departamentos
CREATE TABLE departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    id_sede INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE CASCADE
);

-- Tabla de Trabajadores
CREATE TABLE trabajadores (
    id_trabajador INT AUTO_INCREMENT PRIMARY KEY,
    id_sede INT NOT NULL,
    id_departamento INT,
    codigo VARCHAR(20) NOT NULL,
    identificacion VARCHAR(20) NOT NULL,
    tipo_identificacion VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion VARCHAR(150),
    fecha_nacimiento DATE,
    fecha_contratacion DATE NOT NULL,
    genero ENUM('M', 'F', 'Otro'),
    foto VARCHAR(255),
    huella_digital BLOB,
    tarjeta_rfid VARCHAR(50),
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE CASCADE,
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento) ON DELETE SET NULL
);

-- Tabla de Horarios
CREATE TABLE horarios (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    id_sede INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    tolerancia_entrada INT DEFAULT 10, -- minutos de tolerancia
    tolerancia_salida INT DEFAULT 10, -- minutos de tolerancia
    dias_laborales VARCHAR(15) NOT NULL, -- formato: '1,2,3,4,5' (lunes a viernes)
    es_turno BOOLEAN DEFAULT FALSE,
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE CASCADE
);

-- Tabla de Asignación de Horarios a Trabajadores
CREATE TABLE trabajador_horario (
    id_trabajador INT NOT NULL,
    id_horario INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_trabajador, id_horario, fecha_inicio),
    FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador) ON DELETE CASCADE,
    FOREIGN KEY (id_horario) REFERENCES horarios(id_horario) ON DELETE CASCADE
);

-- Tabla de Dispositivos de Marcación
CREATE TABLE dispositivos (
    id_dispositivo INT AUTO_INCREMENT PRIMARY KEY,
    id_sede INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Biométrico', 'RFID', 'QR', 'Manual') NOT NULL,
    ubicacion VARCHAR(100),
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    token_mqtt VARCHAR(255),
    estado BOOLEAN DEFAULT TRUE,
    ultimo_ping DATETIME,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sede) REFERENCES sedes(id_sede) ON DELETE CASCADE
);

-- Tabla de Asistencias
CREATE TABLE asistencias (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_trabajador INT NOT NULL,
    id_horario INT NOT NULL,
    id_dispositivo INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada DATETIME,
    hora_salida DATETIME,
    estado_entrada ENUM('Puntual', 'Tardanza', 'Ausente', 'Justificado') NOT NULL,
    estado_salida ENUM('A tiempo', 'Temprano', 'Tarde', 'Ausente', 'Justificado'),
    observaciones TEXT,
    FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador) ON DELETE CASCADE,
    FOREIGN KEY (id_horario) REFERENCES horarios(id_horario) ON DELETE CASCADE,
    FOREIGN KEY (id_dispositivo) REFERENCES dispositivos(id_dispositivo) ON DELETE CASCADE
);

-- Tabla de Justificaciones
CREATE TABLE justificaciones (
    id_justificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_asistencia INT NOT NULL,
    id_usuario INT NOT NULL, -- Usuario que registra la justificación
    tipo ENUM('Permiso', 'Enfermedad', 'Vacaciones', 'Otros') NOT NULL,
    descripcion TEXT NOT NULL,
    documento_adjunto VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_asistencia) REFERENCES asistencias(id_asistencia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Configuración del Sistema
CREATE TABLE configuracion (
    id_configuracion INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    clave VARCHAR(50) NOT NULL,
    valor TEXT NOT NULL,
    descripcion VARCHAR(255),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- Tabla de Registros de Actividad (Log)
CREATE TABLE logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT,
    detalles TEXT,
    ip_address VARCHAR(45),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_trabajador_sede ON trabajadores(id_sede);
CREATE INDEX idx_horario_sede ON horarios(id_sede);
CREATE INDEX idx_asistencia_fecha ON asistencias(fecha);
CREATE INDEX idx_asistencia_trabajador ON asistencias(id_trabajador);
CREATE INDEX idx_asistencia_horario ON asistencias(id_horario);
CREATE INDEX idx_usuario_empresa ON usuarios(id_empresa);
CREATE INDEX idx_sede_negocio ON sedes(id_negocio);
CREATE INDEX idx_negocio_empresa ON negocios(id_empresa);