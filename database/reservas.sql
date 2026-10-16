CREATE DATABASE IF NOT EXISTS Reservas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE Reservas;

-- Tabla principal de reservas
CREATE TABLE reserva (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre_huesped VARCHAR(120) NOT NULL,
    email_huesped VARCHAR(254) NOT NULL,
    nombre_alojamiento VARCHAR(150) NOT NULL,

    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,

    estado ENUM(
        'pendiente',
        'confirmada',
        'cancelada'
    ) NOT NULL DEFAULT 'pendiente',

    importe DECIMAL(10,2) UNSIGNED NOT NULL,
    notas TEXT NULL,

    fecha_creacion TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_reserva_fechas
        CHECK (fecha_salida > fecha_entrada),

    INDEX idx_reserva_estado_fecha (estado, fecha_entrada),
    INDEX idx_reserva_fecha_entrada (fecha_entrada)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

  -- Historial de acontecimientos de cada reserva
CREATE TABLE reserva_evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reserva_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,

    fecha_creacion TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evento_reserva
        FOREIGN KEY (reserva_id)
        REFERENCES reserva(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_evento_reserva_fecha (
        reserva_id,
        fecha_creacion
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

  -- Datos de ejemplo
INSERT INTO reserva (
    id,
    nombre_huesped,
    email_huesped,
    nombre_alojamiento,
    fecha_entrada,
    fecha_salida,
    estado,
    importe,
    notas,
    fecha_creacion
) VALUES
(
    1,
    'Salva Martinez',
    'salva@example.com',
    'Apartamento Playa Gandia',
    '2026-10-20',
    '2026-10-25',
    'confirmada',
    425.00,
    'Llegada por la tarde',
    '2026-08-01 09:00:00'
),
(
    2,
    'Claudio Balbastre',
    'claudio@example.com',
    'Casa Rural La Safor',
    '2026-11-06',
    '2026-11-09',
    'pendiente',
    280.00,
    NULL,
    '2026-10-10 12:15:00'
),
(
    3,
    'Ismael Plester',
    'isma@example.com',
    'Hotel Centro',
    '2026-09-10',
    '2026-09-14',
    'cancelada',
    360.00,
    'Cancelacion solicitada por el huesped',
    '2026-08-20 16:15:00'
),
(
    4,
    'Fernando Tordesillas',
    'fernando@example.com',
    'Villa Mediterranea',
    '2026-12-01',
    '2026-12-05',
    'confirmada',
    720.00,
    'Dos personas',
    '2026-10-01 08:00:00'
),
(
    5,
    'Joan Cortes',
    'joan@example.com',
    'Apartamento Centro',
    '2026-10-17',
    '2026-10-19',
    'pendiente',
    190.00,
    NULL,
    '2026-10-15 18:20:00'
);

-- Historial de los acontecimientos de cada reserva
INSERT INTO reserva_evento (
    reserva_id,
    tipo,
    descripcion,
    fecha_creacion
) VALUES
(
    1,
    'creada',
    'Reserva creada correctamente',
    '2026-08-01 09:00:00'
),
(
    1,
    'confirmada',
    'Reserva confirmada',
    '2026-08-02 10:30:00'
),
(
    2,
    'creada',
    'Reserva creada correctamente',
    '2026-10-10 12:15:00'
),
(
    3,
    'creada',
    'Reserva creada correctamente',
    '2026-08-20 16:15:00'
),
(
    3,
    'cancelada',
    'Reserva cancelada',
    '2026-08-21 09:00:00'
),
(
    4,
    'creada',
    'Reserva creada correctamente',
    '2026-10-01 08:00:00'
),
(
    4,
    'confirmada',
    'Reserva confirmada',
    '2026-10-02 11:20:00'
),
(
    5,
    'creada',
    'Reserva creada correctamente',
    '2026-10-15 18:20:00'
);