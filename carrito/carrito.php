<?php
/**
 * =====================================================
 * CARRITO DE COMPRAS
 * =====================================================
 * Archivo: carrito/carrito.php
 * 
 * Gestión del carrito de compras
 * =====================================================
 */

$titulo_pagina = "Carrito de Compras";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Obtener carrito de la sesión
$carrito = $_SESSION['carrito'] ?? [];
$total_items = 0;
$subtotal = 0;
$impuesto = 0;
$total = 0;

// Procesar acciones
$accion = $_GET['accion'] ?? '';
$producto_id = $_GET['id'] ?? null;

if ($accion === 'eliminar' && $producto_id) {
    eliminar_del_carrito($producto_id);
    header('Location: carrito.php');
    exit;
}

if ($accion === 'limpiar') {
    $_SESSION['carrito'] = [];
    header('Location: carrito.php');
    exit;
}

// Procesar actualización de cantidades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_carrito'])) {
    foreach ($_POST['cantidad'] ?? [] as $prod_id => $cantidad) {
        $cantidad = (int)$cantidad;
        if ($cantidad > 0) {
            actualizar_cantidad_carrito($prod_id, $cantidad);
        }
    }
    header('Location: carrito.php');
    exit;
}

// Recalcular totales
if (!empty($carrito)) {
    foreach ($carrito as $item) {
        $producto = obtener_producto_por_id($item['id']);
        if ($producto) {
            $precio = $item['precio_descuento'] ?? $item['precio'];
            $subtotal_item = $precio * $item['cantidad'];
            $subtotal += $subtotal_item;
            $total_items += $item['cantidad'];
        }
    }
    
    // Calcular impuesto (21% IVA)
    $impuesto = $subtotal * 0.21;
    $total = $subtotal + $impuesto;
}

// Envío
$envio = 0;
if ($total > 500) {
    $envio = 0; // Envío gratis
} elseif ($total > 0) {
    $envio = 50; // Envío estándar
}
$total += $envio;
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item active">Carrito de Compras</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="fas fa-shopping-cart"></i> Carrito de Compras
    </h1>

    <?php if (empty($carrito)): ?>
        <!-- Carrito vacío -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center shadow-lg">
                    <div class="card-body py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                        <h4 class="card-title">Tu carrito está vacío</h4>
                        <p class="card-text text-muted mb-4">
                            Aún no has agregado productos a tu carrito. 
                            ¡Comienza a comprar ahora!
                        </p>
                        <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-success btn-lg">
                            <i class="fas fa-shopping-bag"></i> Ver Productos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Productos en el carrito -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-box"></i> Productos en tu carrito (<?php echo $total_items; ?>)
                        </h5>
                    </div>

                    <form method="POST" action="carrito.php">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th style="width: 120px;">Cantidad</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($carrito as $item): ?>
                                        <?php $producto = obtener_producto_por_id($item['id']); ?>
                                        <?php if ($producto): ?>
                                            <?php $precio = $item['precio_descuento'] ?? $item['precio']; ?>
                                            <?php $subtotal_item = $precio * $item['cantidad']; ?>
                                            <tr>
                                                <!-- Producto -->
                                                <td>
                                                    <div class="d-flex gap-3">
                                                        <img 
                                                            src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>" 
                                                            alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;"
                                                        >
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $producto['id']; ?>" class="text-dark text-decoration-none">
                                                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                                                </a>
                                                            </h6>
                                                            <small class="text-muted">
                                                                ID: <?php echo $producto['id']; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Precio -->
                                                <td>
                                                    <span class="fw-bold">
                                                        <?php echo formato_precio($precio); ?>
                                                    </span>
                                                    <?php if ($item['precio_descuento'] ?? false): ?>
                                                        <br>
                                                        <small class="text-muted text-decoration-line-through">
                                                            <?php echo formato_precio($item['precio']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Cantidad -->
                                                <td>
                                                    <div class="input-group input-group-sm" style="width: 100px;">
                                                        <button 
                                                            class="btn btn-outline-secondary" 
                                                            type="button"
                                                            onclick="disminuirCantidad(this)"
                                                        >-</button>
                                                        <input 
                                                            type="number" 
                                                            class="form-control text-center" 
                                                            name="cantidad[<?php echo $item['id']; ?>]"
                                                            value="<?php echo $item['cantidad']; ?>"
                                                            min="1"
                                                            max="<?php echo $producto['stock']; ?>"
                                                        >
                                                        <button 
                                                            class="btn btn-outline-secondary" 
                                                            type="button"
                                                            onclick="aumentarCantidad(this)"
                                                        >+</button>
                                                    </div>
                                                </td>

                                                <!-- Subtotal -->
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        <?php echo formato_precio($subtotal_item); ?>
                                                    </span>
                                                </td>

                                                <!-- Eliminar -->
                                                <td>
                                                    <a 
                                                        href="carrito.php?accion=eliminar&id=<?php echo $item['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Deseas eliminar este producto del carrito?')"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-light d-flex justify-content-between">
                            <a 
                                href="<?php echo SITE_URL; ?>productos/catalogo.php"
                                class="btn btn-outline-success"
                            >
                                <i class="fas fa-arrow-left"></i> Seguir Comprando
                            </a>
                            <div class="d-flex gap-2">
                                <button 
                                    type="submit" 
                                    name="actualizar_carrito"
                                    class="btn btn-warning"
                                >
                                    <i class="fas fa-sync"></i> Actualizar Carrito
                                </button>
                                <a 
                                    href="carrito.php?accion=limpiar"
                                    class="btn btn-danger"
                                    onclick="return confirm('¿Deseas vaciar todo el carrito?')"
                                >
                                    <i class="fas fa-trash"></i> Vaciar Carrito
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Cupón de descuento -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-ticket-alt"></i> ¿Tienes un cupón de descuento?
                        </h6>
                        <form method="POST" action="<?php echo SITE_URL; ?>carrito/aplicar_cupon.php" class="d-flex gap-2">
                            <input 
                                type="text" 
                                class="form-control"
                                name="codigo_cupon"
                                placeholder="Ingresa el código..."
                            >
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-check"></i> Aplicar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resumen del pedido -->
            <div class="col-lg-4">
                <div class="card shadow-lg sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt"></i> Resumen del Pedido
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Detalles -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo formato_precio($subtotal); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Impuesto (21%):</span>
                                <span><?php echo formato_precio($impuesto); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span>
                                    <?php 
                                    if ($envio === 0) {
                                        echo '<i class="fas fa-check-circle text-success"></i> Envío:';
                                    } else {
                                        echo 'Envío:';
                                    }
                                    ?>
                                </span>
                                <span>
                                    <?php 
                                    if ($envio === 0) {
                                        echo '<span class="badge bg-success">GRATIS</span>';
                                    } else {
                                        echo formato_precio($envio);
                                    }
                                    ?>
                                </span>
                            </div>

                            <!-- Total -->
                            <div class="d-flex justify-content-between mb-3">
                                <h5 class="mb-0">Total:</h5>
                                <h5 class="mb-0 text-success">
                                    <?php echo formato_precio($total); ?>
                                </h5>
                            </div>
                        </div>

                        <!-- Información de envío -->
                        <?php if ($subtotal < 500 && $subtotal > 0): ?>
                            <div class="alert alert-info mb-3">
                                <small>
                                    <strong>¡Compra por $<?php echo 500 - (int)$subtotal; ?> más</strong> 
                                    para obtener envío gratis
                                </small>
                            </div>
                        <?php elseif ($subtotal >= 500): ?>
                            <div class="alert alert-success mb-3">
                                <small>
                                    <i class="fas fa-check-circle"></i> 
                                    <strong>¡Envío gratis!</strong>
                                </small>
                            </div>
                        <?php endif; ?>

                        <!-- Botón de pago -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <a 
                                href="<?php echo SITE_URL; ?>pedidos/checkout.php"
                                class="btn btn-success btn-lg w-100 mb-2"
                            >
                                <i class="fas fa-credit-card"></i> Ir al Pago
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning mb-3">
                                <small>
                                    Debes <a href="<?php echo SITE_URL; ?>usuarios/login.php">iniciar sesión</a> 
                                    para procesar tu pedido
                                </small>
                            </div>
                            <a 
                                href="<?php echo SITE_URL; ?>usuarios/login.php?destino=../carrito/carrito.php"
                                class="btn btn-success btn-lg w-100 mb-2"
                            >
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        <?php endif; ?>

                        <button 
                            class="btn btn-outline-secondary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDetalles"
                        >
                            <i class="fas fa-info-circle"></i> Ver Detalles
                        </button>
                    </div>

                    <!-- Métodos de pago -->
                    <div class="card-footer bg-light">
                        <p class="mb-2 text-center fw-bold text-muted" style="font-size: 0.9rem;">
                            Métodos de Pago
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <i class="fab fa-cc-visa fa-2x text-primary"></i>
                            <i class="fab fa-cc-mastercard fa-2x text-danger"></i>
                            <i class="fab fa-cc-paypal fa-2x text-info"></i>
                            <i class="fab fa-cc-amex fa-2x text-success"></i>
                        </div>
                    </div>
                </div>

                <!-- Ofertas relacionadas -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-star"></i> Ofertas Especiales
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-truck"></i> Envío gratis en compras mayores a $500
                        </p>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-undo"></i> Devoluciones sin costo en 30 días
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-headset"></i> Soporte al cliente 24/7
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de detalles -->
        <div class="modal fade" id="modalDetalles" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Desglose de precios</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end"><?php echo formato_precio($subtotal); ?></td>
                                </tr>
                                <tr>
                                    <td>Impuesto (21%)</td>
                                    <td class="text-end"><?php echo formato_precio($impuesto); ?></td>
                                </tr>
                                <tr>
                                    <td>Envío</td>
                                    <td class="text-end">
                                        <?php 
                                        echo $envio === 0 
                                            ? '<span class="badge bg-success">GRATIS</span>' 
                                            : formato_precio($envio);
                                        ?>
                                    </td>
                                </tr>
                                <tr class="fw-bold border-top">
                                    <td>Total a Pagar</td>
                                    <td class="text-end text-success"><?php echo formato_precio($total); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="mb-3">
                            <h6>Información de envío</h6>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check"></i> Envío a toda la Argentina</li>
                                <li><i class="fas fa-check"></i> Entrega en 2-5 días hábiles</li>
                                <li><i class="fas fa-check"></i> Seguimiento del envío</li>
                                <li><i class="fas fa-check"></i> Asegurado durante el tránsito</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
function aumentarCantidad(button) {
    const input = button.previousElementSibling;
    input.value = parseInt(input.value) + 1;
}

function disminuirCantidad(button) {
    const input = button.nextElementSibling;
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>
