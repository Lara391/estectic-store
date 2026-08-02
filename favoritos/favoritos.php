<?php
/**
 * =====================================================
 * PÁGINA DE FAVORITOS
 * =====================================================
 * Archivo: favoritos/favoritos.php
 * 
 * Gestión de productos favoritos del usuario
 * =====================================================
 */

$titulo_pagina = "Mis Favoritos";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/login.php?destino=../favoritos/favoritos.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Parámetros de filtrado y ordenamiento
$orden = $_GET['orden'] ?? 'reciente';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_precio_min = (int)($_GET['precio_min'] ?? 0);
$filtro_precio_max = (int)($_GET['precio_max'] ?? 100000);

// Obtener favoritos del usuario
$favoritos = obtener_favoritos_usuario($usuario_id);

// Filtrar por rango de precio
$favoritos_filtrados = array_filter($favoritos, function($p) use ($filtro_precio_min, $filtro_precio_max) {
    $precio = $p['precio_descuento'] ?? $p['precio'];
    return $precio >= $filtro_precio_min && $precio <= $filtro_precio_max;
});

// Filtrar por categoría
if (!empty($filtro_categoria)) {
    $favoritos_filtrados = array_filter($favoritos_filtrados, function($p) use ($filtro_categoria) {
        return $p['categoria'] === $filtro_categoria;
    });
}

// Ordenar
switch ($orden) {
    case 'precio_asc':
        usort($favoritos_filtrados, fn($a, $b) => ($a['precio_descuento'] ?? $a['precio']) <=> ($b['precio_descuento'] ?? $b['precio']));
        break;
    case 'precio_desc':
        usort($favoritos_filtrados, fn($a, $b) => ($b['precio_descuento'] ?? $b['precio']) <=> ($a['precio_descuento'] ?? $a['precio']));
        break;
    case 'nombre':
        usort($favoritos_filtrados, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
        break;
    case 'rating':
        usort($favoritos_filtrados, fn($a, $b) => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0));
        break;
    case 'reciente':
    default:
        // Mantener orden original (más reciente primero)
        break;
}

// Obtener categorías para filtro
$categorias = obtener_todas_categorias();

// Variables de control
$errores = [];
$exito = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $producto_id = (int)($_POST['producto_id'] ?? 0);

    if ($accion === 'eliminar_favorito') {
        if (eliminar_favorito($usuario_id, $producto_id)) {
            $exito = 'Producto eliminado de favoritos.';
            // Recargar favoritos
            header('Location: favoritos.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
            exit;
        } else {
            $errores[] = 'Error al eliminar el producto de favoritos.';
        }
    }

    elseif ($accion === 'agregar_carrito') {
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        $producto = obtener_producto_por_id($producto_id);

        if ($producto && $cantidad > 0) {
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            // Buscar si el producto ya está en el carrito
            $existe = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id'] == $producto_id) {
                    $item['cantidad'] += $cantidad;
                    $existe = true;
                    break;
                }
            }

            if (!$existe) {
                $_SESSION['carrito'][] = [
                    'id' => $producto_id,
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'precio_descuento' => $producto['precio_descuento'] ?? null,
                    'imagen' => $producto['imagen_principal'],
                    'cantidad' => $cantidad
                ];
            }

            $exito = 'Producto agregado al carrito.';
        }
    }
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item active">Favoritos</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2">
                <i class="fas fa-heart"></i> Mis Favoritos
            </h1>
            <p class="text-muted">
                Total de productos: <strong><?php echo count($favoritos_filtrados); ?></strong>
            </p>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if ($exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">Errores encontrados:</h6>
            <ul class="mb-0 ms-3">
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Filtros y opciones -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h6>
                </div>

                <div class="card-body">
                    <form method="GET" action="favoritos.php">
                        <!-- Ordenamiento -->
                        <div class="mb-3">
                            <h6 class="mb-2">Ordenar por:</h6>
                            <select class="form-select form-select-sm" name="orden" onchange="this.form.submit()">
                                <option value="reciente" <?php echo $orden === 'reciente' ? 'selected' : ''; ?>>
                                    Más Reciente
                                </option>
                                <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>
                                    Nombre (A-Z)
                                </option>
                                <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>
                                    Menor Precio
                                </option>
                                <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>
                                    Mayor Precio
                                </option>
                                <option value="rating" <?php echo $orden === 'rating' ? 'selected' : ''; ?>>
                                    Mejor Calificación
                                </option>
                            </select>
                        </div>

                        <hr>

                        <!-- Categoría -->
                        <div class="mb-3">
                            <h6 class="mb-2">Categoría</h6>
                            <select class="form-select form-select-sm" name="categoria" onchange="this.form.submit()">
                                <option value="">Todas las Categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>

                        <!-- Rango de precio -->
                        <div class="mb-3">
                            <h6 class="mb-2">Rango de Precio</h6>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    name="precio_min"
                                    value="<?php echo $filtro_precio_min; ?>"
                                    placeholder="Mín"
                                >
                            </div>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    name="precio_max"
                                    value="<?php echo $filtro_precio_max; ?>"
                                    placeholder="Máx"
                                >
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                Filtrar por Precio
                            </button>
                        </div>

                        <hr>

                        <!-- Limpiar filtros -->
                        <a href="favoritos.php" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-times"></i> Limpiar Filtros
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-lg-9">
            <?php if (!empty($favoritos_filtrados)): ?>
                <!-- Grid de productos -->
                <div class="row">
                    <?php foreach ($favoritos_filtrados as $producto): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm producto-card">
                                <!-- Imagen del producto -->
                                <div class="position-relative overflow-hidden" style="height: 250px;">
                                    <img 
                                        src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>" 
                                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                        class="card-img-top w-100 h-100"
                                        style="object-fit: cover; transition: transform 0.3s;"
                                    >

                                    <!-- Badge de descuento -->
                                    <?php if ($producto['precio_descuento'] && $producto['precio_descuento'] < $producto['precio']): ?>
                                        <?php 
                                        $descuento = round((($producto['precio'] - $producto['precio_descuento']) / $producto['precio']) * 100);
                                        ?>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            -<?php echo $descuento; ?>%
                                        </span>
                                    <?php endif; ?>

                                    <!-- Botón de favorito -->
                                    <button 
                                        class="btn btn-danger btn-sm position-absolute bottom-0 end-0 m-2"
                                        onclick="eliminarFavorito(<?php echo $producto['id']; ?>)"
                                        title="Eliminar de favoritos"
                                    >
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <!-- Nombre del producto -->
                                    <h6 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $producto['id']; ?>" class="text-dark text-decoration-none">
                                            <?php echo htmlspecialchars(truncar_texto($producto['nombre'], 50)); ?>
                                        </a>
                                    </h6>

                                    <!-- Categoría -->
                                    <p class="text-muted small mb-2">
                                        <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>
                                    </p>

                                    <!-- Rating -->
                                    <?php if (isset($producto['rating']) && $producto['rating'] > 0): ?>
                                        <div class="mb-2">
                                            <div class="small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= round($producto['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="text-muted ms-1"><?php echo number_format($producto['rating'], 1); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Precios -->
                                    <div class="mb-3">
                                        <?php if ($producto['precio_descuento'] && $producto['precio_descuento'] < $producto['precio']): ?>
                                            <p class="mb-0">
                                                <span class="text-danger h5">
                                                    <?php echo formato_precio($producto['precio_descuento']); ?>
                                                </span>
                                                <span class="text-muted text-decoration-line-through small">
                                                    <?php echo formato_precio($producto['precio']); ?>
                                                </span>
                                            </p>
                                        <?php else: ?>
                                            <p class="mb-0">
                                                <span class="text-success h5">
                                                    <?php echo formato_precio($producto['precio']); ?>
                                                </span>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Stock -->
                                    <div class="mb-3">
                                        <?php if ($producto['stock'] > 0): ?>
                                            <span class="badge bg-success">Stock disponible</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Sin stock</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Botones -->
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <?php if ($producto['stock'] > 0): ?>
                                                <form method="POST" action="favoritos.php">
                                                    <input type="hidden" name="accion" value="agregar_carrito">
                                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-shopping-cart"></i> Sin Stock
                                                </button>
                                            <?php endif; ?>
                                            <a 
                                                href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $producto['id']; ?}"
                                                class="btn btn-outline-primary btn-sm"
                                            >
                                                <i class="fas fa-eye"></i> Ver Detalles
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Sin favoritos -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-heart fa-5x text-muted mb-3"></i>
                        <h4 class="mb-3">No hay favoritos</h4>
                        <p class="text-muted mb-4">
                            <?php 
                            if (!empty($favoritos)) {
                                echo "No hay productos que coincidan con los filtros seleccionados.";
                            } else {
                                echo "Aún no tienes productos marcados como favoritos.";
                            }
                            ?>
                        </p>
                        <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-danger btn-lg">
                            <i class="fas fa-heart"></i> Explorar Productos
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-info-circle"></i> Información sobre Favoritos
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Guardar para después:</strong><br>
                                Los productos que agregues a favoritos se guardarán aquí para que no los olvides.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Comparar precios:</strong><br>
                                Ordena por precio para comparar y encontrar las mejores ofertas.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Notificaciones:</strong><br>
                                Recibe notificaciones cuando hay cambios de precio en tus favoritos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
function eliminarFavorito(productoId) {
    if (confirm('¿Deseas eliminar este producto de tus favoritos?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'favoritos.php';
        
        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'accion';
        input1.value = 'eliminar_favorito';
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'producto_id';
        input2.value = productoId;
        
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}

// Efecto hover en las tarjetas
document.querySelectorAll('.producto-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.querySelector('img').style.transform = 'scale(1.1)';
    });
    card.addEventListener('mouseleave', function() {
        this.querySelector('img').style.transform = 'scale(1)';
    });
});

// Cerrar alertas automáticamente
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>
