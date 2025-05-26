CREATE DATABASE cluster_admin;
USE cluster_admin;

-- Tabla de usuarios (inquilinos y comité)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    numero_casa INT NOT NULL CHECK (numero_casa BETWEEN 1 AND 60),
    rol ENUM('inquilino', 'presidente', 'secretario', 'vocal') NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de pagos de mantenimiento
CREATE TABLE pagos_mantenimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    recargo DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    mes_correspondiente VARCHAR(7) NOT NULL, -- formato YYYY-MM
    concepto TEXT,
    verificado BOOLEAN DEFAULT FALSE,
    verificado_por INT,
    fecha_verificacion TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (verificado_por) REFERENCES usuarios(id)
);

-- Tabla de egresos del comité
CREATE TABLE egresos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monto DECIMAL(10,2) NOT NULL,
    pagado_a VARCHAR(200) NOT NULL,
    fecha_pago DATE NOT NULL,
    motivo TEXT NOT NULL,
    realizado_por INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (realizado_por) REFERENCES usuarios(id)
);

-- Tabla de reservaciones de amenidades
CREATE TABLE reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    amenidad ENUM('palapa', 'alberca', 'ambas') NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de solicitudes de servicios (foro)
CREATE TABLE solicitudes_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completado', 'rechazado') DEFAULT 'pendiente',
    comentario_comite TEXT,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de recibos electrónicos
CREATE TABLE recibos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT NOT NULL,
    numero_recibo VARCHAR(50) UNIQUE NOT NULL,
    enviado_email BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP NULL,
    FOREIGN KEY (pago_id) REFERENCES pagos_mantenimiento(id)
);

-- Insertar datos iniciales
INSERT INTO usuarios (nombre, email, password, numero_casa, rol) VALUES
('Admin Sistema', 'admin@cluster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'presidente'),
('Secretario Cluster', 'secretario@cluster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'secretario'),
('Vocal Cluster', 'vocal@cluster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'vocal');

-- Crear índices para optimizar consultas
CREATE INDEX idx_usuario_casa ON usuarios(numero_casa);
CREATE INDEX idx_pago_usuario ON pagos_mantenimiento(usuario_id);
CREATE INDEX idx_pago_mes ON pagos_mantenimiento(mes_correspondiente);
CREATE INDEX idx_reserva_fecha ON reservaciones(fecha_reserva);
CREATE INDEX idx_solicitud_estado ON solicitudes_servicios(estado);