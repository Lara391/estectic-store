-- =====================================================
-- BASE DE DATOS: TIENDA ESTÉTICA Y BELLEZA
-- =====================================================
-- Script SQL para crear la estructura completa de la BD
-- Importar en phpMyAdmin o ejecutar en MySQL
-- =====================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS estectic_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE estectic_store;

-- =====================================================
-- 1. TABLA: CATEGORÍAS
-- =====================================================
CREATE TABLE IF NOT EXISTS categorias (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion TEXT,
  slug VARCHAR(100) UNIQUE,
  imagen VARCHAR(255),
  estado ENUM('activa', 'inactiva') DEFAULT 'activa',
  orden INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_estado (estado),
  INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLA: MARCAS
-- =====================================================
CREATE TABLE IF NOT EXISTS marcas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion TEXT,
  logo VARCHAR(255),
  estado ENUM('activa', 'inactiva') DEFAULT 'activa',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABLA: PRODUCTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS productos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  descripcion LONGTEXT,
  categoria_id INT NOT NULL,
  marca_id INT NOT NULL,
  precio DECIMAL(10, 2) NOT NULL,
  precio_anterior DECIMAL(10, 2),
  descuento_porcentaje DECIMAL(5, 2) DEFAULT 0,
  stock INT DEFAULT 0,
  imagen_principal VARCHAR(255),
  slug VARCHAR(200) UNIQUE,
  estado ENUM('activo', 'inactivo') DEFAULT 'activo',
  es_destacado BOOLEAN DEFAULT FALSE,
  es_nuevo BOOLEAN DEFAULT FALSE,
  es_oferta BOOLEAN DEFAULT FALSE,
  es_mas_vendido BOOLEAN DEFAULT FALSE,
  es_ultimas_unidades BOOLEAN DEFAULT FALSE,
  vistas INT DEFAULT 0,
  ventas INT DEFAULT 0,
  calificacion_promedio DECIMAL(3, 2) DEFAULT 0,
  total_opiniones INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
  FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE,
  INDEX idx_estado (estado),
  INDEX idx_categoria (categoria_id),
  INDEX idx_marca (marca_id),
  INDEX idx_slug (slug),
  INDEX idx_destacado (es_destacado),
  INDEX idx_nuevo (es_nuevo),
  INDEX idx_oferta (es_oferta),
  INDEX idx_mas_vendido (es_mas_vendido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLA: IMÁGENES DE PRODUCTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS imagenes_productos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  producto_id INT NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  orden INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABLA: USUARIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  apellido VARCHAR(150) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  telefono VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  rol ENUM('cliente', 'admin') DEFAULT 'cliente',
  estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
  avatar VARCHAR(255),
  direccion VARCHAR(255),
  ciudad VARCHAR(100),
  provincia VARCHAR(100),
  codigo_postal VARCHAR(10),
  pais VARCHAR(100),
  genero ENUM('masculino', 'femenino', 'otro'),
  fecha_nacimiento DATE,
  token_recuperacion VARCHAR(255),
  token_recuperacion_expira TIMESTAMP NULL,
  es_newsletter BOOLEAN DEFAULT FALSE,
  ultimo_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_estado (estado),
  INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABLA: FAVORITOS
-- =====================================================
CREATE TABLE IF NOT EXISTS favoritos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  producto_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  UNIQUE KEY unique_usuario_producto (usuario_id, producto_id),
  INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABLA: CARRITO
-- =====================================================
CREATE TABLE IF NOT EXISTS carrito (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT,
  sesion_id VARCHAR(255),
  producto_id INT NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  INDEX idx_usuario (usuario_id),
  INDEX idx_sesion (sesion_id),
  INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABLA: PROMOCIONES
-- =====================================================
CREATE TABLE IF NOT EXISTS promociones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  descripcion TEXT,
  tipo ENUM('2x1', '3x2', '4x3', 'porcentaje', 'monto_fijo', 'combo', 'envio_gratis', 'regalo') NOT NULL,
  descuento_porcentaje DECIMAL(5, 2),
  descuento_monto DECIMAL(10, 2),
  cantidad_minima INT DEFAULT 1,
  cantidad_productos_bonificados INT DEFAULT 1,
  monto_minimo_envio_gratis DECIMAL(10, 2),
  categoria_id INT,
  marca_id INT,
  imagen_banner VARCHAR(255),
  estado ENUM('activa', 'inactiva') DEFAULT 'activa',
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
  FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL,
  INDEX idx_estado (estado),
  INDEX idx_tipo (tipo),
  INDEX idx_fecha (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABLA: PRODUCTOS_PROMOCIONES
-- =====================================================
CREATE TABLE IF NOT EXISTS productos_promociones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  promocion_id INT NOT NULL,
  producto_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  UNIQUE KEY unique_promo_producto (promocion_id, producto_id),
  INDEX idx_promocion (promocion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABLA: REGALOS_PROMOCION
-- =====================================================
CREATE TABLE IF NOT EXISTS regalos_promocion (
  id INT PRIMARY KEY AUTO_INCREMENT,
  promocion_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  INDEX idx_promocion (promocion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. TABLA: PEDIDOS
-- =====================================================
CREATE TABLE IF NOT EXISTS pedidos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  numero_pedido VARCHAR(50) NOT NULL UNIQUE,
  usuario_id INT NOT NULL,
  estado ENUM('pendiente', 'pagado', 'preparando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
  metodo_pago ENUM('mercado_pago', 'tarjeta_credito', 'tarjeta_debito') DEFAULT 'mercado_pago',
  subtotal DECIMAL(10, 2) NOT NULL,
  descuento_total DECIMAL(10, 2) DEFAULT 0,
  costo_envio DECIMAL(10, 2) DEFAULT 0,
  total DECIMAL(10, 2) NOT NULL,
  direccion_envio VARCHAR(255) NOT NULL,
  ciudad_envio VARCHAR(100) NOT NULL,
  provincia_envio VARCHAR(100) NOT NULL,
  codigo_postal_envio VARCHAR(10) NOT NULL,
  pais_envio VARCHAR(100) NOT NULL DEFAULT 'Argentina',
  telefono_envio VARCHAR(20),
  notas_pedido TEXT,
  numero_seguimiento VARCHAR(100),
  fecha_pago TIMESTAMP NULL,
  fecha_envio TIMESTAMP NULL,
  fecha_entrega TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_usuario (usuario_id),
  INDEX idx_estado (estado),
  INDEX idx_numero_pedido (numero_pedido),
  INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. TABLA: DETALLES_PEDIDOS
-- =====================================================
CREATE TABLE IF NOT EXISTS detalles_pedidos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  pedido_id INT NOT NULL,
  producto_id INT NOT NULL,
  nombre_producto VARCHAR(200) NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10, 2) NOT NULL,
  descuento_unitario DECIMAL(10, 2) DEFAULT 0,
  subtotal DECIMAL(10, 2) NOT NULL,
  promocion_aplicada VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. TABLA: PAGOS
-- =====================================================
CREATE TABLE IF NOT EXISTS pagos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  pedido_id INT NOT NULL,
  monto DECIMAL(10, 2) NOT NULL,
  metodo ENUM('mercado_pago', 'tarjeta_credito', 'tarjeta_debito', 'transferencia') NOT NULL,
  estado ENUM('pendiente', 'aprobado', 'rechazado', 'cancelado') DEFAULT 'pendiente',
  id_transaccion VARCHAR(255),
  referencia_transaccion VARCHAR(255),
  detalles_pago JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  INDEX idx_pedido (pedido_id),
  INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. TABLA: OPINIONES/RESEÑAS
-- =====================================================
CREATE TABLE IF NOT EXISTS opiniones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  producto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  pedido_id INT,
  calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
  titulo VARCHAR(200),
  comentario TEXT,
  es_verificado BOOLEAN DEFAULT FALSE,
  estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
  likes INT DEFAULT 0,
  dislikes INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
  INDEX idx_producto (producto_id),
  INDEX idx_usuario (usuario_id),
  INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. TABLA: HISTORIAL_STOCK
-- =====================================================
CREATE TABLE IF NOT EXISTS historial_stock (
  id INT PRIMARY KEY AUTO_INCREMENT,
  producto_id INT NOT NULL,
  cantidad_anterior INT,
  cantidad_nueva INT,
  motivo ENUM('compra', 'devolucion', 'ajuste', 'devolución_producto', 'reposicion') DEFAULT 'ajuste',
  referencia VARCHAR(100),
  pedido_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
  INDEX idx_producto (producto_id),
  INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. TABLA: HISTORIAL_VENTAS
-- =====================================================
CREATE TABLE IF NOT EXISTS historial_ventas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  pedido_id INT NOT NULL,
  usuario_id INT NOT NULL,
  total_venta DECIMAL(10, 2) NOT NULL,
  total_descuento DECIMAL(10, 2) DEFAULT 0,
  cantidad_productos INT NOT NULL,
  promociones_utilizadas JSON,
  fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_usuario (usuario_id),
  INDEX idx_fecha (fecha_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. TABLA: CONFIGURACIÓN DEL SITIO
-- =====================================================
CREATE TABLE IF NOT EXISTS configuracion (
  id INT PRIMARY KEY AUTO_INCREMENT,
  clave VARCHAR(100) NOT NULL UNIQUE,
  valor LONGTEXT,
  tipo ENUM('texto', 'numero', 'booleano', 'json') DEFAULT 'texto',
  descripcion VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. TABLA: NEWSLETTER
-- =====================================================
CREATE TABLE IF NOT EXISTS newsletter (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(200) NOT NULL UNIQUE,
  nombre VARCHAR(150),
  estado ENUM('suscrito', 'desuscrito') DEFAULT 'suscrito',
  fecha_suscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_desuscripcion TIMESTAMP NULL,
  INDEX idx_email (email),
  INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. TABLA: CONTACTO/CONSULTAS
-- =====================================================
CREATE TABLE IF NOT EXISTS contactos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(200) NOT NULL,
  telefono VARCHAR(20),
  asunto VARCHAR(200) NOT NULL,
  mensaje LONGTEXT NOT NULL,
  estado ENUM('nuevo', 'leido', 'respondido', 'cerrado') DEFAULT 'nuevo',
  respuesta LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_estado (estado),
  INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTS DE DATOS INICIALES
-- =====================================================

-- Categorías iniciales
INSERT INTO categorias (nombre, descripcion, slug, estado, orden) VALUES
('Cremas Faciales', 'Cremas especializadas para el cuidado facial', 'cremas-faciales', 'activa', 1),
('Cremas Corporales', 'Cremas para el cuidado del cuerpo', 'cremas-corporales', 'activa', 2),
('Cremas Hidratantes', 'Hidratación intensiva para piel seca', 'cremas-hidratantes', 'activa', 3),
('Cremas Antiage', 'Reducción de arrugas y signos de envejecimiento', 'cremas-antiage', 'activa', 4),
('Sérums', 'Sérums concentrados para tratamientos específicos', 'serums', 'activa', 5),
('Protectores Solares', 'Protección solar profesional', 'protectores-solares', 'activa', 6),
('Productos de Skincare', 'Línea completa de cuidado de la piel', 'skincare', 'activa', 7),
('Productos de Belleza', 'Cosméticos y productos de belleza', 'belleza', 'activa', 8),
('Cuidado Corporal', 'Productos especializados para el cuerpo', 'cuidado-corporal', 'activa', 9);

-- Marcas iniciales
INSERT INTO marcas (nombre, descripcion, estado) VALUES
('Neutrogena', 'Marca líder en dermatología', 'activa'),
('Cetaphil', 'Expertos en piel sensible', 'activa'),
('La Roche-Posay', 'Dermatología de lujo', 'activa'),
('Vichy', 'Cuidado profesional de la piel', 'activa'),
('Eucerin', 'Innovación en dermofarmacia', 'activa');

-- Usuarios de ejemplo
INSERT INTO usuarios (nombre, apellido, email, password, rol, estado, es_newsletter) VALUES
('Admin', 'Sistema', 'admin@estectic-store.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'admin', 'activo', FALSE),
('Cliente', 'Test', 'cliente@example.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'cliente', 'activo', TRUE);

-- Configuración inicial del sitio
INSERT INTO configuracion (clave, valor, tipo, descripcion) VALUES
('nombre_tienda', 'Estetic Store', 'texto', 'Nombre de la tienda'),
('email_tienda', 'info@estectic-store.com', 'texto', 'Email de contacto principal'),
('telefono_tienda', '+54 9 1234 567890', 'texto', 'Teléfono de contacto'),
('direccion_tienda', 'Av. Principal 123, Buenos Aires', 'texto', 'Dirección física'),
('costo_envio_base', '500', 'numero', 'Costo base de envío'),
('envio_gratis_desde', '5000', 'numero', 'Monto mínimo para envío gratis'),
('impuesto_venta', '21', 'numero', 'Porcentaje de IVA'),
('mostrar_precio_anterior', 'true', 'booleano', 'Mostrar precio anterior si hay descuento'),
('pagina_principal_productos_destacados', '6', 'numero', 'Cantidad de productos destacados en inicio');

-- =====================================================
-- FIN DEL SCRIPT SQL
-- =====================================================
