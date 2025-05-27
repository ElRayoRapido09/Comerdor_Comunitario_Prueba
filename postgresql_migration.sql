-- ================================================================
-- MIGRACIÓN DE MYSQL A POSTGRESQL PARA NEON DATABASE
-- Base de datos: Comedor Comunitario
-- ================================================================

-- Configurar extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ================================================================
-- TABLA: usuarios
-- ================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE,
    direccion TEXT,
    edad INTEGER,
    sexo VARCHAR(20) CHECK (sexo IN ('masculino', 'femenino', 'otro')),
    contrasena VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(20) NOT NULL CHECK (tipo_usuario IN ('beneficiario', 'empleado', 'admin')),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    recordar_token VARCHAR(100),
    token_recuperacion VARCHAR(100),
    token_expiracion TIMESTAMP
);

-- ================================================================
-- TABLA: secciones_menu
-- ================================================================
CREATE TABLE IF NOT EXISTS secciones_menu (
    id_seccion SERIAL PRIMARY KEY,
    nombre_seccion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50) DEFAULT '🍽️',
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE
);

-- ================================================================
-- TABLA: platillos
-- ================================================================
CREATE TABLE IF NOT EXISTS platillos (
    id_platillo SERIAL PRIMARY KEY,
    id_seccion INTEGER NOT NULL,
    nombre_platillo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ingredientes TEXT,
    es_vegetariano BOOLEAN DEFAULT FALSE,
    es_vegano BOOLEAN DEFAULT FALSE,
    contiene_gluten BOOLEAN DEFAULT FALSE,
    calorias INTEGER,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_seccion) REFERENCES secciones_menu(id_seccion) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA: menus_dia
-- ================================================================
CREATE TABLE IF NOT EXISTS menus_dia (
    id_menu SERIAL PRIMARY KEY,
    fecha DATE NOT NULL UNIQUE,
    precio DECIMAL(10,2) NOT NULL,
    notas TEXT,
    id_usuario_creador INTEGER,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_creador) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- ================================================================
-- TABLA: menu_platillos
-- ================================================================
CREATE TABLE IF NOT EXISTS menu_platillos (
    id_menu_platillo SERIAL PRIMARY KEY,
    id_menu INTEGER NOT NULL,
    id_platillo INTEGER NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    UNIQUE(id_menu, id_platillo),
    FOREIGN KEY (id_menu) REFERENCES menus_dia(id_menu) ON DELETE CASCADE,
    FOREIGN KEY (id_platillo) REFERENCES platillos(id_platillo) ON DELETE CASCADE
);

-- ================================================================
-- TABLA: reservaciones
-- ================================================================
CREATE TABLE IF NOT EXISTS reservaciones (
    id_reservacion SERIAL PRIMARY KEY,
    codigo_reservacion VARCHAR(20) NOT NULL UNIQUE,
    id_usuario INTEGER NOT NULL,
    id_menu INTEGER NOT NULL,
    fecha_reservacion DATE NOT NULL,
    hora_recogida TIME NOT NULL,
    num_porciones INTEGER NOT NULL DEFAULT 1 CHECK (num_porciones BETWEEN 1 AND 3),
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'completada', 'cancelada')),
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario_atendio INTEGER,
    fecha_atencion TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    FOREIGN KEY (id_menu) REFERENCES menus_dia(id_menu) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_atendio) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- ================================================================
-- TABLA: reservacion_platillos
-- ================================================================
CREATE TABLE IF NOT EXISTS reservacion_platillos (
    id_reservacion_platillo SERIAL PRIMARY KEY,
    id_reservacion INTEGER NOT NULL,
    id_platillo INTEGER NOT NULL,
    tipo_seccion VARCHAR(30) NOT NULL CHECK (tipo_seccion IN ('primer_tiempo', 'plato_principal', 'acompanamiento', 'postre', 'bebida')),
    FOREIGN KEY (id_reservacion) REFERENCES reservaciones(id_reservacion) ON DELETE CASCADE,
    FOREIGN KEY (id_platillo) REFERENCES platillos(id_platillo) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA: asistencias
-- ================================================================
CREATE TABLE IF NOT EXISTS asistencias (
    id_asistencia SERIAL PRIMARY KEY,
    fecha_asistencia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmada_por INTEGER,
    id_reservacion INTEGER NOT NULL,
    FOREIGN KEY (confirmada_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_reservacion) REFERENCES reservaciones(id_reservacion) ON DELETE CASCADE
);

-- ================================================================
-- TABLA: gastos
-- ================================================================
CREATE TABLE IF NOT EXISTS gastos (
    id_gasto SERIAL PRIMARY KEY,
    concepto VARCHAR(100) NOT NULL,
    categoria VARCHAR(30) NOT NULL CHECK (categoria IN ('ingredientes', 'personal', 'servicios', 'mantenimiento', 'otros')),
    monto DECIMAL(10,2) NOT NULL,
    fecha_gasto DATE NOT NULL,
    descripcion TEXT,
    id_usuario_registro INTEGER NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comprobante VARCHAR(255),
    FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA: inventario
-- ================================================================
CREATE TABLE IF NOT EXISTS inventario (
    id_item SERIAL PRIMARY KEY,
    nombre_item VARCHAR(100) NOT NULL,
    categoria VARCHAR(30) NOT NULL CHECK (categoria IN ('alimento', 'utensilio', 'limpieza', 'otros')),
    cantidad DECIMAL(10,2) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    nivel_minimo DECIMAL(10,2),
    costo_unitario DECIMAL(10,2),
    proveedor VARCHAR(100),
    fecha_ultima_compra DATE,
    activo BOOLEAN DEFAULT TRUE
);

-- ================================================================
-- TABLA: reportes
-- ================================================================
CREATE TABLE IF NOT EXISTS reportes (
    id_reporte SERIAL PRIMARY KEY,
    nombre_reporte VARCHAR(100) NOT NULL,
    tipo_reporte VARCHAR(30) NOT NULL CHECK (tipo_reporte IN ('general', 'reservaciones', 'menu', 'usuarios', 'financiero')),
    parametros TEXT,
    id_usuario_creador INTEGER NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_creador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
);

-- ================================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- ================================================================
CREATE INDEX IF NOT EXISTS idx_usuarios_correo ON usuarios(correo);
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);
CREATE INDEX IF NOT EXISTS idx_menus_fecha ON menus_dia(fecha);
CREATE INDEX IF NOT EXISTS idx_reservaciones_fecha ON reservaciones(fecha_reservacion);
CREATE INDEX IF NOT EXISTS idx_reservaciones_estado ON reservaciones(estado);
CREATE INDEX IF NOT EXISTS idx_reservaciones_codigo ON reservaciones(codigo_reservacion);
CREATE INDEX IF NOT EXISTS idx_platillos_seccion ON platillos(id_seccion);

-- ================================================================
-- DATOS INICIALES
-- ================================================================

-- Insertar secciones de menú
INSERT INTO secciones_menu (nombre_seccion, descripcion, icono, orden) VALUES
('Entrada', 'Platillos para comenzar la comida', '🥗', 1),
('Plato Principal', 'Platillos principales', '🍽️', 2),
('Acompañamiento', 'Guarniciones y acompañamientos', '🥘', 3),
('Postre', 'Postres y dulces', '🍰', 4),
('Bebida', 'Bebidas y líquidos', '🥤', 5)
ON CONFLICT (id_seccion) DO NOTHING;

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, apellidos, correo, contrasena, tipo_usuario, activo) VALUES
('Administrador', 'Sistema', 'admin@comedor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE)
ON CONFLICT (correo) DO NOTHING;

-- Insertar algunos platillos de ejemplo
INSERT INTO platillos (id_seccion, nombre_platillo, descripcion, es_vegetariano, orden) VALUES
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Entrada'), 'Ensalada mixta', 'Ensalada fresca con verduras de temporada', TRUE, 1),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Plato Principal'), 'Pollo guisado', 'Pollo guisado con verduras', FALSE, 1),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Plato Principal'), 'Lentejas con verduras', 'Lentejas nutritivas con verduras', TRUE, 2),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Acompañamiento'), 'Arroz blanco', 'Arroz cocido al vapor', TRUE, 1),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Acompañamiento'), 'Tortillas', 'Tortillas de maíz caseras', TRUE, 2),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Bebida'), 'Agua fresca', 'Agua fresca del día', TRUE, 1),
((SELECT id_seccion FROM secciones_menu WHERE nombre_seccion = 'Postre'), 'Fruta de temporada', 'Fruta fresca de temporada', TRUE, 1)
ON CONFLICT DO NOTHING;

-- ================================================================
-- COMENTARIOS FINALES
-- ================================================================
-- Este script migra la estructura de MySQL a PostgreSQL
-- Cambios principales realizados:
-- 1. AUTO_INCREMENT → SERIAL
-- 2. TINYINT(1) → BOOLEAN
-- 3. ENUM → VARCHAR con CHECK constraints
-- 4. ENGINE=InnoDB → Removido (no aplica en PostgreSQL)
-- 5. Agregados ON CONFLICT para manejo de duplicados
-- 6. Agregados índices para optimización
-- 7. Datos iniciales para comenzar a usar el sistema

-- ================================================================
-- VERIFICACIÓN FINAL
-- ================================================================
-- Verificar que todas las tablas fueron creadas
SELECT schemaname, tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
ORDER BY tablename;
