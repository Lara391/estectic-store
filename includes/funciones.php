<?php
/**
 * =====================================================
 * FUNCIONES GENERALES DEL SITIO
 * =====================================================
 * Archivo: includes/funciones.php
 * 
 * Funciones de uso general para toda la tienda
 * =====================================================
 */

// Incluir conexión a la BD
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// =====================================================
// VARIABLES DE SESIÓN Y AUTENTICACIÓN
// =====================================================

/**
 * Inicia la sesión si no está iniciada
 */
function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica si un usuario está autenticado
 * @return bool True si está autenticado
 */
function usuario_autenticado() {
    iniciar_sesion();
    return isset($_SESSION['usuario_id']);
}

/**
 * Obtiene el ID del usuario actual
 * @return int ID del usuario o null
 */
function obtener_usuario_actual() {
    iniciar_sesion();
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtiene los datos del usuario actual
 * @return array Datos del usuario o null
 */
function obtener_datos_usuario_actual() {
    $usuario_id = obtener_usuario_actual();
    if (!$usuario_id) {
        return null;
    }
    return obtener_usuario($usuario_id);
}

/**
 * Cierra la sesión del usuario
 */
function cerrar_sesion() {
    iniciar_sesion();
    session_destroy();
    $_SESSION = [];
}

// =====================================================
// VALIDACIONES Y SEGURIDAD
// =====================================================

/**
 * Valida un email
 * @param string $email Email a validar
 * @return bool True si es válido
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida una contraseña
 * @param string $password Contraseña
 * @return array Array con errores si los hay
 */
function validar_password($password) {
    $errores = [];
    
    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = "La contraseña debe contener al menos una mayúscula";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errores[] = "La contraseña debe contener al menos un número";
    }
    
    return $errores;
}

/**
 * Verifica una contraseña contra su hash
 * @param string $password Contraseña en texto plano
 * @param string $hash Hash de la contraseña
 * @return bool True si coinciden
 */
function verificar_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Genera un token CSRF
 * @return string Token generado
 */
function generar_token_csrf() {
    iniciar_sesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica un token CSRF
 * @param string $token Token a verificar
 * @return bool True si es válido
 */
function verificar_token_csrf($token) {
    iniciar_sesion();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// =====================================================
// FORMATEO Y PRESENTACIÓN
// =====================================================

/**
 * Formatea un número como precio
 * @param float $precio Precio a formatear
 * @return string Precio formateado
 */
function formato_precio($precio) {
    return "$" . number_format($precio, 2, ',', '.');
}

/**
 * Formatea una fecha
 * @param string $fecha Fecha a formatear
 * @param string $formato Formato deseado
 * @return string Fecha formateada
 */
function formato_fecha($fecha, $formato = 'd/m/Y') {
    return date($formato, strtotime($fecha));
}

/**
 * Obtiene tiempo transcurrido desde una fecha
 * @param string $fecha Fecha a comparar
 * @return string Texto con tiempo transcurrido
 */
function tiempo_transcurrido($fecha) {
    $ahora = new DateTime();
    $fecha_obj = new DateTime($fecha);
    $intervalo = $ahora->diff($fecha_obj);
    
    if ($intervalo->y > 0) {
        return "hace {$intervalo->y} año" . ($intervalo->y > 1 ? "s" : "");
    } elseif ($intervalo->m > 0) {
        return "hace {$intervalo->m} mes" . ($intervalo->m > 1 ? "es" : "");
    } elseif ($intervalo->d > 0) {
        return "hace {$intervalo->d} día" . ($intervalo->d > 1 ? "s" : "");
    } elseif ($intervalo->h > 0) {
        return "hace {$intervalo->h} hora" . ($intervalo->h > 1 ? "s" : "");
    } elseif ($intervalo->i > 0) {
        return "hace {$intervalo->i} minuto" . ($intervalo->i > 1 ? "s" : "");
    } else {
        return "hace unos segundos";
    }
}

/**
 * Trunca un texto a una cantidad de caracteres
 * @param string $texto Texto a truncar
 * @param int $limite Límite de caracteres
 * @param string $sufijo Sufijo a agregar
 * @return string Texto truncado
 */
function truncar_texto($texto, $limite = 100, $sufijo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    return substr($texto, 0, $limite) . $sufijo;
}

/**
 * Genera un slug a partir de un texto
 * @param string $texto Texto a convertir
 * @return string Slug generado
 */
function generar_slug($texto) {
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}

// =====================================================
// CARRITO Y COMPRAS
// =====================================================

/**
 * Obtiene el ID de sesión del usuario no autenticado
 * @return string ID de sesión
 */
function obtener_sesion_id() {
    iniciar_sesion();
    if (!isset($_SESSION['sesion_id'])) {
        $_SESSION['sesion_id'] = session_id();
    }
    return $_SESSION['sesion_id'];
}

/**
 * Obtiene el total del carrito
 * @return float Total del carrito
 */
function obtener_total_carrito() {
    $usuario_id = obtener_usuario_actual();
    $sesion_id = obtener_sesion_id();
    
    $carrito = obtener_carrito($usuario_id, $sesion_id);
    
    $total = 0;
    foreach ($carrito as $item) {
        $total += $item['precio_unitario'] * $item['cantidad'];
    }
    
    return $total;
}

/**
 * Obtiene la cantidad de items en el carrito
 * @return int Cantidad de items
 */
function obtener_cantidad_carrito() {
    $usuario_id = obtener_usuario_actual();
    $sesion_id = obtener_sesion_id();
    
    $carrito = obtener_carrito($usuario_id, $sesion_id);
    
    $cantidad = 0;
    foreach ($carrito as $item) {
        $cantidad += $item['cantidad'];
    }
    
    return $cantidad;
}

// =====================================================
// NOTIFICACIONES Y MENSAJES
// =====================================================

/**
 * Agrega un mensaje de sesión
 * @param string $tipo Tipo de mensaje (success, error, warning, info)
 * @param string $mensaje Contenido del mensaje
 */
function agregar_mensaje($tipo, $mensaje) {
    iniciar_sesion();
    
    if (!isset($_SESSION['mensajes'])) {
        $_SESSION['mensajes'] = [];
    }
    
    $_SESSION['mensajes'][] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje
    ];
}

/**
 * Obtiene y elimina los mensajes
 * @return array Array de mensajes
 */
function obtener_mensajes() {
    iniciar_sesion();
    
    $mensajes = $_SESSION['mensajes'] ?? [];
    unset($_SESSION['mensajes']);
    
    return $mensajes;
}

// =====================================================
// VALIDACIONES COMUNES
// =====================================================

/**
 * Verifica que el usuario esté autenticado, sino redirige
 */
function requerir_autenticacion() {
    if (!usuario_autenticado()) {
        agregar_mensaje('error', 'Debes iniciar sesión para continuar');
        header('Location: ' . SITE_URL . 'usuarios/login.php');
        exit();
    }
}

/**
 * Verifica que el usuario sea administrador
 */
function requerir_admin() {
    if (!usuario_autenticado()) {
        header('Location: ' . SITE_URL . 'usuarios/login.php');
        exit();
    }
    
    $usuario = obtener_datos_usuario_actual();
    if ($usuario['rol'] !== 'admin') {
        header('Location: ' . SITE_URL);
        exit();
    }
}

/**
 * Valida que un número sea positivo
 * @param float $numero Número a validar
 * @return bool True si es válido
 */
function validar_numero_positivo($numero) {
    return is_numeric($numero) && $numero > 0;
}

/**
 * Limpia un texto de caracteres peligrosos
 * @param string $texto Texto a limpiar
 * @return string Texto limpio
 */
function limpiar_texto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

?>
