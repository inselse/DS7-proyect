
CREATE DATABASE IF NOT EXISTS taller_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE taller_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','mecanico') NOT NULL DEFAULT 'mecanico',
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehiculos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    placa           VARCHAR(20) NOT NULL UNIQUE,
    marca           VARCHAR(80) NOT NULL,
    modelo          VARCHAR(80) NOT NULL,
    anio            YEAR NOT NULL,
    color           VARCHAR(40) NOT NULL,
    dueno_nombre    VARCHAR(120) NOT NULL,
    dueno_telefono  VARCHAR(30),
    dueno_email     VARCHAR(150),
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cubiculos (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero    TINYINT UNSIGNED NOT NULL UNIQUE,
    nombre    VARCHAR(50) NOT NULL,
    estado    ENUM('libre','ocupado') NOT NULL DEFAULT 'libre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registros_entrada (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id     INT UNSIGNED NOT NULL,
    cubiculo_id     INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    fecha_entrada   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_salida    DATETIME,
    estado          ENUM('activo','completado','cancelado') NOT NULL DEFAULT 'activo',
    observaciones   TEXT,
    FOREIGN KEY (vehiculo_id)  REFERENCES vehiculos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (cubiculo_id)  REFERENCES cubiculos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id)   REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mantenimientos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registro_id     INT UNSIGNED NOT NULL,
    tipo            ENUM('cambio_aceite','frenos','filtros','otro') NOT NULL,
    descripcion     TEXT NOT NULL,
    costo           DECIMAL(10,2) DEFAULT 0.00,
    mecanico_id     INT UNSIGNED,
    estado          ENUM('pendiente','en_proceso','completado') NOT NULL DEFAULT 'pendiente',
    fecha_inicio    DATETIME,
    fecha_fin       DATETIME,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registro_id)  REFERENCES registros_entrada(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (mecanico_id)  REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@taller.com', '$2y$12$WdNckTn1WurM7e1XUZvc/.50TV9VtPywDW2SFRC8fSAu69KFygRae', 'admin'),
('Carlos Mecanico', 'carlos@taller.com', '$2y$12$Y5I1ECyc7fhdKVT.EyyOeuozOaTdhaXFRODrP0O3iHuVb1yyJAkgq', 'mecanico'),
('Ana Mecanico', 'ana@taller.com', '$2y$12$Y5I1ECyc7fhdKVT.EyyOeuozOaTdhaXFRODrP0O3iHuVb1yyJAkgq', 'mecanico');

INSERT INTO cubiculos (numero, nombre) VALUES
(1, 'Cubiculo 01'),
(2, 'Cubiculo 02'),
(3, 'Cubiculo 03'),
(4, 'Cubiculo 04'),
(5, 'Cubiculo 05'),
(6, 'Cubiculo 06'),
(7, 'Cubiculo 07'),
(8, 'Cubiculo 08');

INSERT INTO vehiculos (placa, marca, modelo, anio, color, dueno_nombre, dueno_telefono, dueno_email) VALUES
('ABC-123', 'Toyota', 'Corolla', 2020, 'Azul', 'Juan Perez', '555-0101', 'juan@email.com'),
('DEF-456', 'Chevrolet', 'Cruze', 2019, 'Rojo', 'Maria Garcia', '555-0102', 'maria@email.com'),
('GHI-789', 'Honda', 'Civic', 2021, 'Negro', 'Pedro Lopez', '555-0103', 'pedro@email.com'),
('JKL-012', 'Nissan', 'Sentra', 2018, 'Blanco', 'Laura Martinez', '555-0104', 'laura@email.com'),
('MNO-345', 'Ford', 'Focus', 2022, 'Gris', 'Diego Rodriguez', '555-0105', 'diego@email.com');

INSERT INTO registros_entrada (vehiculo_id, cubiculo_id, usuario_id, fecha_entrada, estado) VALUES
(1, 1, 1, '2026-06-20 09:15:00', 'activo'),
(2, 3, 1, '2026-06-21 10:30:00', 'activo'),
(3, 5, 2, '2026-06-22 08:00:00', 'activo');

INSERT INTO mantenimientos (registro_id, tipo, descripcion, costo, mecanico_id, estado, fecha_inicio) VALUES
(1, 'cambio_aceite', 'Cambio de aceite sintetico 5W30 y filtro de aceite', 850.00, 2, 'en_proceso', '2026-06-20 09:30:00'),
(1, 'filtros', 'Cambio de filtro de aire y filtro de cabina', 450.00, 2, 'pendiente', NULL),
(2, 'frenos', 'Revision y cambio de pastillas de freno delanteras', 1200.00, 3, 'en_proceso', '2026-06-21 11:00:00'),
(3, 'cambio_aceite', 'Cambio de aceite convencional 10W40', 650.00, 2, 'pendiente', NULL),
(3, 'otro', 'Diagnostico general del motor por ruido anormal', 0.00, 3, 'pendiente', NULL);

INSERT INTO registros_entrada (vehiculo_id, cubiculo_id, usuario_id, fecha_entrada, fecha_salida, estado, observaciones) VALUES
(4, 2, 1, '2026-06-10 08:30:00', '2026-06-10 16:45:00', 'completado', 'Servicio completo realizado sin novedades'),
(5, 4, 1, '2026-06-15 09:00:00', '2026-06-16 14:30:00', 'completado', 'Reparacion de aire acondicionado');

INSERT INTO mantenimientos (registro_id, tipo, descripcion, costo, mecanico_id, estado, fecha_inicio, fecha_fin) VALUES
(4, 'cambio_aceite', 'Cambio de aceite y filtro', 750.00, 2, 'completado', '2026-06-10 09:00:00', '2026-06-10 11:30:00'),
(4, 'frenos', 'Rectificacion de discos y cambio de balatas', 1800.00, 3, 'completado', '2026-06-10 11:30:00', '2026-06-10 15:45:00'),
(5, 'otro', 'Reparacion de compresor de aire acondicionado, recarga de gas refrigerante', 3200.00, 2, 'completado', '2026-06-15 09:30:00', '2026-06-16 13:00:00');

UPDATE cubiculos SET estado = 'ocupado' WHERE id IN (1, 3, 5);
