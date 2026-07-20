<?php
/**
 * =====================================================
 * FUNCIONES DE BASE DE DATOS
 * =====================================================
 * Archivo: db/funciones_bd.php
 * 
 * Contiene todas las funciones personalizadas para
 * acceder y manipular datos de la base de datos
 * =====================================================
 */

// =====================================================
// FUNCIONES DE CATEGORÍAS
// =====================================================

/**
 * Obtiene todas las categorías activas
 * @return array Array de categorías
 */
function obtener_categorias() {
    $query = "SELECT * FROM categorias WHERE estado = 'activa' ORDER BY orden ASC";
    return obtener_resultados($query);
}

/**
 * Obtiene una categoría por ID
 * @param int $id ID de la categoría
 * @return array Datos de la categoría
 */
function obtener_categoria($id) {
    $id = escapar($id);
    $query = "SELECT * FROM categorias WHERE id = $id";
    return obtener_fila($query);
}

/**
 * Crea una nueva categoría
 * @param array $datos Array con nombre, descripcion, etc
 * @return int ID de la categoría creada
 */
function crear_categoria($datos) {
    $nombre = escapar($datos['nombre']);
    $descripcion = escapar($datos['descripcion'] ?? '');
    $slug = escapar($datos['slug'] ?? strtolower(str_replace(' ', '-', $nombre)));
    
    $query = "INSERT INTO categorias (nombre, descripcion, slug, estado) 
              VALUES ('$nombre', '$descripcion', '$slug', 'activa')";
    
    ejecutar($query);
    return obtener_ultimo_id();
}

/**
 * Actualiza una categoría
 * @param int $id ID de la categoría
 * @param array $datos Array con los datos a actualizar
 * @return int Número de filas afectadas
 */
function actualizar_categoria($id, $datos) {
    $id = escapar($id);
    $nombre = escapar($datos['nombre'] ?? '');
    $descripcion = escapar($datos['descripcion'] ?? '');
    $estado = escapar($datos['estado'] ?? 'activa');
    
    $query = "UPDATE categorias SET 
              nombre = '$nombre',
              descripcion = '$descripcion',
              estado = '$estado',
              updated_at = NOW()
              WHERE id = $id";
    
    return ejecutar($query);
}

// =====================================================
// FUNCIONES DE PRODUCTOS
// =====================================================

/**
 * Obtiene todos los productos con filtros opcionales
 * @param array $filtros Array con filtros (categoria_id, marca_id, estado, etc)
 * @param int $pagina Número de página para paginación
 * @return array Array de productos
 */
function obtener_productos($filtros = [], $pagina = 1) {
    $where = "WHERE estado = 'activo'";
    
    if (!empty($filtros['categoria_id'])) {
        $categoria_id = escapar($filtros['categoria_id']);
        $where .= " AND categoria_id = $categoria_id";
    }
    
    if (!empty($filtros['marca_id'])) {
        $marca_id = escapar($filtros['marca_id']);
        $where .= " AND marca_id = $marca_id";
    }
    
    if (!empty($filtros['es_destacado'])) {
        $where .= " AND es_destacado = TRUE";
    }
    
    if (!empty($filtros['es_nuevo'])) {
        $where .= " AND es_nuevo = TRUE";
    }
    
    if (!empty($filtros['es_oferta'])) {
        $where .= " AND es_oferta = TRUE";
    }
    
    if (!empty($filtros['buscar'])) {
        $buscar = escapar($filtros['buscar']);
        $where .= " AND (nombre LIKE '%$buscar%' OR descripcion LIKE '%$buscar%')";
    }
    
    // Paginación
    $limite = PRODUCTOS_POR_PAGINA;
    $offset = ($pagina - 1) * $limite;
    
    $query = "SELECT * FROM productos $where 
              ORDER BY created_at DESC 
              LIMIT $limite OFFSET $offset";
    
    return obtener_resultados($query);
}

/**
 * Obtiene un producto por ID
 * @param int $id ID del producto
 * @return array Datos del producto
 */
function obtener_producto($id) {
    $id = escapar($id);
    $query = "SELECT p.*, 
                     c.nombre as categoria_nombre,
                     m.nombre as marca_nombre,
                     COUNT(DISTINCT o.id) as total_opiniones,
                     AVG(o.calificacion) as calificacion_promedio
              FROM productos p
              LEFT JOIN categorias c ON p.categoria_id = c.id
              LEFT JOIN marcas m ON p.marca_id = m.id
              LEFT JOIN opiniones o ON p.id = o.producto_id AND o.estado = 'aprobada'
              WHERE p.id = $id
              GROUP BY p.id";
    
    return obtener_fila($query);
}

/**
 * Obtiene imágenes de un producto
 * @param int $producto_id ID del producto
 * @return array Array de imágenes
 */
function obtener_imagenes_producto($producto_id) {
    $producto_id = escapar($producto_id);
    $query = "SELECT * FROM imagenes_productos 
              WHERE producto_id = $producto_id 
              ORDER BY orden ASC";
    
    return obtener_resultados($query);
}

/**
 * Crea un nuevo producto
 * @param array $datos Array con los datos del producto
 * @return int ID del producto creado
 */
function crear_producto($datos) {
    $nombre = escapar($datos['nombre']);
    $descripcion = escapar($datos['descripcion'] ?? '');
    $categoria_id = escapar($datos['categoria_id']);
    $marca_id = escapar($datos['marca_id']);
    $precio = escapar($datos['precio']);
    $stock = escapar($datos['stock'] ?? 0);
    $slug = escapar($datos['slug'] ?? strtolower(str_replace(' ', '-', $nombre)));
    
    $query = "INSERT INTO productos 
              (nombre, descripcion, categoria_id, marca_id, precio, stock, slug, estado) 
              VALUES ('$nombre', '$descripcion', $categoria_id, $marca_id, $precio, $stock, '$slug', 'activo')";
    
    ejecutar($query);
    return obtener_ultimo_id();
}

/**
 * Actualiza un producto
 * @param int $id ID del producto
 * @param array $datos Array con los datos a actualizar
 * @return int Número de filas afectadas
 */
function actualizar_producto($id, $datos) {
    $id = escapar($id);
    $nombre = escapar($datos['nombre'] ?? '');
    $descripcion = escapar($datos['descripcion'] ?? '');
    $precio = escapar($datos['precio'] ?? 0);
    $stock = escapar($datos['stock'] ?? 0);
    $es_destacado = isset($datos['es_destacado']) ? (int)$datos['es_destacado'] : 0;
    $es_nuevo = isset($datos['es_nuevo']) ? (int)$datos['es_nuevo'] : 0;
    $es_oferta = isset($datos['es_oferta']) ? (int)$datos['es_oferta'] : 0;
    $estado = escapar($datos['estado'] ?? 'activo');
    
    $query = "UPDATE productos SET 
              nombre = '$nombre',
              descripcion = '$descripcion',
              precio = $precio,
              stock = $stock,
              es_destacado = $es_destacado,
              es_nuevo = $es_nuevo,
              es_oferta = $es_oferta,
              estado = '$estado',
              updated_at = NOW()
              WHERE id = $id";
    
    return ejecutar($query);
}

/**
 * Disminuye el stock de un producto
 * @param int $producto_id ID del producto
 * @param int $cantidad Cantidad a restar
 * @param string $motivo Motivo del cambio
 * @param int $pedido_id ID del pedido relacionado
 */
function disminuir_stock($producto_id, $cantidad, $motivo = 'compra', $pedido_id = null) {
    $producto_id = escapar($producto_id);
    $cantidad = escapar($cantidad);
    $motivo = escapar($motivo);
    $pedido_id = $pedido_id ? escapar($pedido_id) : 'NULL';
    
    // Obtener stock actual
    $stock_actual = obtener_valor("SELECT stock FROM productos WHERE id = $producto_id");
    $stock_nuevo = $stock_actual - $cantidad;
    
    // Actualizar stock
    $query = "UPDATE productos SET stock = $stock_nuevo WHERE id = $producto_id";
    ejecutar($query);
    
    // Registrar en historial
    $query_historial = "INSERT INTO historial_stock 
                        (producto_id, cantidad_anterior, cantidad_nueva, motivo, pedido_id)
                        VALUES ($producto_id, $stock_actual, $stock_nuevo, '$motivo', $pedido_id)";
    ejecutar($query_historial);
}

// =====================================================
// FUNCIONES DE USUARIOS
// =====================================================

/**
 * Obtiene un usuario por email
 * @param string $email Email del usuario
 * @return array Datos del usuario
 */
function obtener_usuario_por_email($email) {
    $email = escapar($email);
    $query = "SELECT * FROM usuarios WHERE email = '$email'";
    return obtener_fila($query);
}

/**
 * Obtiene un usuario por ID
 * @param int $id ID del usuario
 * @return array Datos del usuario
 */
function obtener_usuario($id) {
    $id = escapar($id);
    $query = "SELECT * FROM usuarios WHERE id = $id";
    return obtener_fila($query);
}

/**
 * Crea un nuevo usuario (registro)
 * @param array $datos Array con nombre, email, password, etc
 * @return int ID del usuario creado
 */
function crear_usuario($datos) {
    $nombre = escapar($datos['nombre']);
    $apellido = escapar($datos['apellido'] ?? '');
    $email = escapar($datos['email']);
    $password = password_hash($datos['password'], PASSWORD_BCRYPT);
    $telefono = escapar($datos['telefono'] ?? '');
    
    $query = "INSERT INTO usuarios (nombre, apellido, email, password, telefono, rol, estado) 
              VALUES ('$nombre', '$apellido', '$email', '$password', '$telefono', 'cliente', 'activo')";
    
    ejecutar($query);
    return obtener_ultimo_id();
}

/**
 * Verifica si un usuario existe
 * @param string $email Email a verificar
 * @return bool True si existe
 */
function usuario_existe($email) {
    $email = escapar($email);
    $resultado = obtener_valor("SELECT COUNT(*) FROM usuarios WHERE email = '$email'");
    return $resultado > 0;
}

/**
 * Actualiza el perfil de un usuario
 * @param int $id ID del usuario
 * @param array $datos Datos a actualizar
 * @return int Número de filas afectadas
 */
function actualizar_perfil_usuario($id, $datos) {
    $id = escapar($id);
    $nombre = escapar($datos['nombre'] ?? '');
    $apellido = escapar($datos['apellido'] ?? '');
    $telefono = escapar($datos['telefono'] ?? '');
    $direccion = escapar($datos['direccion'] ?? '');
    $ciudad = escapar($datos['ciudad'] ?? '');
    $provincia = escapar($datos['provincia'] ?? '');
    $codigo_postal = escapar($datos['codigo_postal'] ?? '');
    
    $query = "UPDATE usuarios SET 
              nombre = '$nombre',
              apellido = '$apellido',
              telefono = '$telefono',
              direccion = '$direccion',
              ciudad = '$ciudad',
              provincia = '$provincia',
              codigo_postal = '$codigo_postal',
              updated_at = NOW()
              WHERE id = $id";
    
    return ejecutar($query);
}

// =====================================================
// FUNCIONES DE CARRITO
// =====================================================

/**
 * Obtiene el carrito de un usuario o sesión
 * @param int $usuario_id ID del usuario (null si no autenticado)
 * @param string $sesion_id ID de la sesión
 * @return array Array con items del carrito
 */
function obtener_carrito($usuario_id = null, $sesion_id = null) {
    $where = "";
    
    if ($usuario_id) {
        $usuario_id = escapar($usuario_id);
        $where = "usuario_id = $usuario_id";
    } elseif ($sesion_id) {
        $sesion_id = escapar($sesion_id);
        $where = "sesion_id = '$sesion_id' AND usuario_id IS NULL";
    }
    
    if (empty($where)) {
        return [];
    }
    
    $query = "SELECT c.*, p.nombre, p.imagen_principal, p.precio 
              FROM carrito c
              JOIN productos p ON c.producto_id = p.id
              WHERE $where
              ORDER BY c.created_at DESC";
    
    return obtener_resultados($query);
}

/**
 * Agrega un producto al carrito
 * @param int $producto_id ID del producto
 * @param int $cantidad Cantidad a agregar
 * @param int $usuario_id ID del usuario (opcional)
 * @param string $sesion_id ID de sesión (opcional)
 */
function agregar_al_carrito($producto_id, $cantidad = 1, $usuario_id = null, $sesion_id = null) {
    $producto_id = escapar($producto_id);
    $cantidad = escapar($cantidad);
    
    // Obtener precio actual del producto
    $precio = obtener_valor("SELECT precio FROM productos WHERE id = $producto_id");
    
    $valores = "($producto_id, $cantidad, $precio";
    
    if ($usuario_id) {
        $usuario_id = escapar($usuario_id);
        $valores = "($usuario_id, NULL, $producto_id, $cantidad, $precio";
    } elseif ($sesion_id) {
        $sesion_id = escapar($sesion_id);
        $valores = "(NULL, '$sesion_id', $producto_id, $cantidad, $precio";
    }
    
    $valores .= ")";
    
    $query = "INSERT INTO carrito (usuario_id, sesion_id, producto_id, cantidad, precio_unitario) 
              VALUES $valores";
    
    ejecutar($query);
}

/**
 * Elimina un item del carrito
 * @param int $carrito_id ID del item en el carrito
 */
function eliminar_del_carrito($carrito_id) {
    $carrito_id = escapar($carrito_id);
    $query = "DELETE FROM carrito WHERE id = $carrito_id";
    ejecutar($query);
}

/**
 * Vacía el carrito de un usuario
 * @param int $usuario_id ID del usuario
 */
function vaciar_carrito($usuario_id) {
    $usuario_id = escapar($usuario_id);
    $query = "DELETE FROM carrito WHERE usuario_id = $usuario_id";
    ejecutar($query);
}

// =====================================================
// FUNCIONES DE PEDIDOS
// =====================================================

/**
 * Crea un nuevo pedido
 * @param int $usuario_id ID del usuario
 * @param array $datos Array con datos del pedido
 * @return int ID del pedido creado
 */
function crear_pedido($usuario_id, $datos) {
    $usuario_id = escapar($usuario_id);
    $numero_pedido = "PED-" . date('Ymd') . "-" . strtoupper(substr(md5(time()), 0, 6));
    $subtotal = escapar($datos['subtotal']);
    $descuento = escapar($datos['descuento'] ?? 0);
    $costo_envio = escapar($datos['costo_envio'] ?? 0);
    $total = $subtotal - $descuento + $costo_envio;
    
    $direccion = escapar($datos['direccion']);
    $ciudad = escapar($datos['ciudad']);
    $provincia = escapar($datos['provincia']);
    $codigo_postal = escapar($datos['codigo_postal']);
    $telefono = escapar($datos['telefono'] ?? '');
    
    $query = "INSERT INTO pedidos 
              (numero_pedido, usuario_id, subtotal, descuento_total, costo_envio, total, 
               direccion_envio, ciudad_envio, provincia_envio, codigo_postal_envio, 
               telefono_envio, estado, metodo_pago)
              VALUES ('$numero_pedido', $usuario_id, $subtotal, $descuento, $costo_envio, $total,
                      '$direccion', '$ciudad', '$provincia', '$codigo_postal', '$telefono',
                      'pendiente', 'mercado_pago')";
    
    ejecutar($query);
    return obtener_ultimo_id();
}

/**
 * Obtiene un pedido por ID
 * @param int $id ID del pedido
 * @return array Datos del pedido
 */
function obtener_pedido($id) {
    $id = escapar($id);
    $query = "SELECT * FROM pedidos WHERE id = $id";
    return obtener_fila($query);
}

/**
 * Obtiene los pedidos de un usuario
 * @param int $usuario_id ID del usuario
 * @return array Array de pedidos
 */
function obtener_pedidos_usuario($usuario_id) {
    $usuario_id = escapar($usuario_id);
    $query = "SELECT * FROM pedidos WHERE usuario_id = $usuario_id ORDER BY created_at DESC";
    return obtener_resultados($query);
}

/**
 * Actualiza el estado de un pedido
 * @param int $id ID del pedido
 * @param string $estado Nuevo estado
 */
function actualizar_estado_pedido($id, $estado) {
    $id = escapar($id);
    $estado = escapar($estado);
    
    $actualizaciones = "estado = '$estado'";
    
    if ($estado == 'pagado') {
        $actualizaciones .= ", fecha_pago = NOW()";
    } elseif ($estado == 'enviado') {
        $actualizaciones .= ", fecha_envio = NOW()";
    } elseif ($estado == 'entregado') {
        $actualizaciones .= ", fecha_entrega = NOW()";
    }
    
    $query = "UPDATE pedidos SET $actualizaciones WHERE id = $id";
    ejecutar($query);
}

// =====================================================
// FUNCIONES DE OPINIONES
// =====================================================

/**
 * Obtiene las opiniones de un producto
 * @param int $producto_id ID del producto
 * @return array Array de opiniones
 */
function obtener_opiniones_producto($producto_id) {
    $producto_id = escapar($producto_id);
    $query = "SELECT o.*, u.nombre, u.apellido 
              FROM opiniones o
              JOIN usuarios u ON o.usuario_id = u.id
              WHERE o.producto_id = $producto_id AND o.estado = 'aprobada'
              ORDER BY o.created_at DESC";
    
    return obtener_resultados($query);
}

/**
 * Crea una nueva opinión
 * @param array $datos Array con datos de la opinión
 * @return int ID de la opinión creada
 */
function crear_opinion($datos) {
    $producto_id = escapar($datos['producto_id']);
    $usuario_id = escapar($datos['usuario_id']);
    $calificacion = escapar($datos['calificacion']);
    $titulo = escapar($datos['titulo'] ?? '');
    $comentario = escapar($datos['comentario'] ?? '');
    $pedido_id = isset($datos['pedido_id']) ? escapar($datos['pedido_id']) : 'NULL';
    
    $query = "INSERT INTO opiniones 
              (producto_id, usuario_id, pedido_id, calificacion, titulo, comentario, es_verificado)
              VALUES ($producto_id, $usuario_id, $pedido_id, $calificacion, '$titulo', '$comentario', TRUE)";
    
    ejecutar($query);
    return obtener_ultimo_id();
}

?>
