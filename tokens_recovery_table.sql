-- Tabla adicional para tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS tokens_recuperacion (
    id SERIAL PRIMARY KEY,
    correo VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_correo_activo UNIQUE (correo)
);

-- Índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_tokens_token ON tokens_recuperacion(token);
CREATE INDEX IF NOT EXISTS idx_tokens_correo ON tokens_recuperacion(correo);
CREATE INDEX IF NOT EXISTS idx_tokens_expiracion ON tokens_recuperacion(expiracion);

-- Función para limpiar tokens expirados (opcional)
-- Se ejecuta manualmente cuando sea necesario
DELETE FROM tokens_recuperacion WHERE expiracion < CURRENT_TIMESTAMP OR usado = TRUE;
