<?php
/**
 * =====================================================
 * CONFIGURACIÓN Y CONEXIÓN A BASE DE DATOS
 * =====================================================
 * Archivo: config/conexion.php
 * 
 * Este archivo contiene la configuración de conexión
 * a la base de datos MySQL. NO debe ser subido a GitHub
 * por razones de seguridad (credenciales sensibles).
 * 
 * Debe estar en .gitignore
 * =====================================================
 */

// =====================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// =====================================================

// Datos de conexión a MySQL
define('DB_HOST', 'localhost');      // Servidor MySQL
define('DB_USER', 'root');           // Usuario MySQL (por defecto en XAMPP es 'root')
define('DB_PASS', '');               // Contraseña MySQL (por defecto en XAMPP está vacía)
define('DB_NAME', 'estectic_store'); // Nombre de la base de datos creada

// Charset para evitar problemas con caracteres especiales
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// CONFIGURACIÓN DEL SITIO
// =====================================================

// URL base del sitio
define('SITE_URL', 'http://localhost/estectic-store/');

// Nombre de la tienda
define('SITE_NAME', 'Estetic Store');

// Email de contacto
define('SITE_EMAIL', 'info@estectic-store.com');

// Ruta absoluta del servidor
define('BASE_PATH', dirname(dirname(__FILE__)) . '/');

// Ruta de uploads
define('UPLOAD_PATH', BASE_PATH . 'uploads/');

// Ruta de productos
define('UPLOAD_PRODUCTOS_PATH', UPLOAD_PATH . 'productos/');

// Ruta de banners
define('UPLOAD_BANNERS_PATH', UPLOAD_PATH . 'banners/');

// =====================================================
// CONFIGURACIÓN DE SEGURIDAD
// =====================================================

// Clave secreta para tokens (CAMBIAR EN PRODUCCIÓN)
define('SECRET_KEY', 'estectic-store-2024-clave-secreta-super-segura-cambiar-en-produccion');

// Tiempo de sesión en segundos (1 hora)
define('SESSION_TIMEOUT', 3600);

// =====================================================
// CONFIGURACIÓN DE PAGINACIÓN
// =====================================================

// Productos por página
define('PRODUCTOS_POR_PAGINA', 12);

// Órdenes por página
define('ORDENES_POR_PAGINA', 20);

// =====================================================
// CREAR CONEXIÓN A LA BASE DE DATOS
// =====================================================

try {
    // Crear conexión MySQLi
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Configurar charset
    $conexion->set_charset(DB_CHARSET);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }
    
    // Configurar zona horaria
    $conexion->query("SET time_zone = '-03:00'"); // Zona horaria de Argentina
    
    // La conexión se guardará en la variable global $conexion
    // Se puede usar en cualquier archivo que incluya este archivo
    
} catch (Exception $e) {
    // Si hay error, mostrar mensaje
    die("
        <div style='background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px; font-family: Arial;'>
            <h2>Error de Conexión a Base de Datos</h2>
            <p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>
            <p>Verifica que:</p>
            <ul>
                <li>MySQL está corriendo en XAMPP</li>
                <li>Los datos de conexión en config/conexion.php son correctos</li>
                <li>La base de datos 'estectic_store' existe</li>
            </ul>
        </div>
    ");
}

/**
 * =====================================================
 * FUNCIONES ÚTILES DE CONEXIÓN
 * =====================================================
 */

/**
 * Escapa caracteres especiales para prevenir inyección SQL
 * @param string $string Texto a escapar
 * @return string Texto escapado
 */
function escapar($string) {
    global $conexion;
    return $conexion->real_escape_string($string);
}

/**
 * Ejecuta una consulta SELECT y retorna los resultados
 * @param string $query Consulta SQL
 * @return array Array con los resultados
 */
function obtener_resultados($query) {
    global $conexion;
    $resultado = $conexion->query($query);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    $datos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
    
    return $datos;
}

/**
 * Obtiene una única fila de la consulta
 * @param string $query Consulta SQL
 * @return array Una fila con los datos
 */
function obtener_fila($query) {
    global $conexion;
    $resultado = $conexion->query($query);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    return $resultado->fetch_assoc();
}

/**
 * Obtiene un valor único de la consulta
 * @param string $query Consulta SQL
 * @return mixed El valor obtenido
 */
function obtener_valor($query) {
    global $conexion;
    $resultado = $conexion->query($query);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    $fila = $resultado->fetch_assoc();
    
    if ($fila) {
        $valores = array_values($fila);
        return $valores[0];
    }
    
    return null;
}

/**
 * Ejecuta una consulta INSERT, UPDATE o DELETE
 * @param string $query Consulta SQL
 * @return int Número de filas afectadas
 */
function ejecutar($query) {
    global $conexion;
    
    if (!$conexion->query($query)) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    return $conexion->affected_rows;
}

/**
 * Obtiene el ID del último insert
 * @return int ID del último registro insertado
 */
function obtener_ultimo_id() {
    global $conexion;
    return $conexion->insert_id;
}

/**
 * Inicia una transacción
 */
function iniciar_transaccion() {
    global $conexion;
    $conexion->begin_transaction();
}

/**
 * Confirma una transacción
 */
function confirmar_transaccion() {
    global $conexion;
    $conexion->commit();
}

/**
 * Revierte una transacción
 */
function revertir_transaccion() {
    global $conexion;
    $conexion->rollback();
}

?>
