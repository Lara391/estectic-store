<?php
/**
 * =====================================================
 * PÁGINA DE CHECKOUT/PAGO
 * =====================================================
 * Archivo: pedidos/checkout.php
 * 
 * Procesamiento de pedidos y pagos
 * =====================================================
 */

$titulo_pagina = "Checkout - Finalizar Compra";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/login.php?destino=../pedidos/checkout.php');
    exit;
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$usuario = obtener_usuario_por_id($usuario_id);

// Obtener carrito
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    header('Location: ' . SITE_URL . 'carrito/carrito.php');
    exit;
}

// Obtener direcciones del usuario
$direcciones = obtener_direcciones_usuario($usuario_id);

// Variables
$paso_actual = isset($_GET['paso']) ? (int)$_GET['paso'] : 1;
$errores = [];
$exito = false;

// Procesar formularios según el paso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($paso_actual === 1) {
        // Validar información de envío
        $nombre_envio = trim($_POST['nombre_envio'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $calle = trim($_POST['calle'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $piso = trim($_POST['piso'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $codigo_postal = trim($_POST['codigo_postal'] ?? '');
        $pais = trim($_POST['pais'] ?? '');

        if (empty($nombre_envio)) $errores[] = 'El nombre es requerido.';
        if (empty($telefono)) $errores[] = 'El teléfono es requerido.';
        if (empty($calle)) $errores[] = 'La calle es requerida.';
        if (empty($numero)) $errores[] = 'El número es requerido.';
        if (empty($ciudad)) $errores[] = 'La ciudad es requerida.';
        if (empty($provincia)) $errores[] = 'La provincia es requerida.';
        if (empty($codigo_postal)) $errores[] = 'El código postal es requerido.';

        if (empty($errores)) {
            $_SESSION['direccion_envio'] = [
                'nombre' => $nombre_envio,
                'telefono' => $telefono,
                'email' => $email,
                'calle' => $calle,
                'numero' => $numero,
                'piso' => $piso,
                'ciudad' => $ciudad,
                'provincia' => $provincia,
                'codigo_postal' => $codigo_postal,
                'pais' => $pais
            ];
            header('Location: checkout.php?paso=2');
            exit;
        }
    } elseif ($paso_actual === 2) {
        // Validar método de envío
        $metodo_envio = $_POST['metodo_envio'] ?? '';
        if (empty($metodo_envio)) {
            $errores[] = 'Debes seleccionar un método de envío.';
        } else {
            $_SESSION['metodo_envio'] = $metodo_envio;
            header('Location: checkout.php?paso=3');
            exit;
        }
    } elseif ($paso_actual === 3) {
        // Validar método de pago
        $metodo_pago = $_POST['metodo_pago'] ?? '';
        if (empty($metodo_pago)) {
            $errores[] = 'Debes seleccionar un método de pago.';
        } else {
            $_SESSION['metodo_pago'] = $metodo_pago;
            header('Location: checkout.php?paso=4');
            exit;
        }
    }
}

// Calcular totales
$subtotal = 0;
$total_items = 0;
foreach ($carrito as $item) {
    $producto = obtener_producto_por_id($item['id']);
    if ($producto) {
        $precio = $item['precio_descuento'] ?? $item['precio'];
        $subtotal += $precio * $item['cantidad'];
        $total_items += $item['cantidad'];
    }
}

$impuesto = $subtotal * 0.21;
$envio = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $impuesto + $envio;

// Datos de la dirección de envío
$direccion_envio = $_SESSION['direccion_envio'] ?? [];
$metodo_envio = $_SESSION['metodo_envio'] ?? '';
$metodo_pago = $_SESSION['metodo_pago'] ?? '';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>carrito/carrito.php">Carrito</a>
            </li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="fas fa-credit-card"></i> Finalizar Compra
    </h1>

    <!-- Indicador de progreso -->
    <div class="mb-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between mb-3">
                    <div class="text-center flex-grow-1">
                        <div class="d-inline-block mb-2">
                            <div class="rounded-circle bg-<?php echo $paso_actual >= 1 ? 'success' : 'light'; ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <p class="small fw-bold">Dirección</p>
                    </div>
                    <div class="text-center flex-grow-1">
                        <div class="d-inline-block mb-2">
                            <div class="rounded-circle bg-<?php echo $paso_actual >= 2 ? 'success' : 'light'; ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                        <p class="small fw-bold">Envío</p>
                    </div>
                    <div class="text-center flex-grow-1">
                        <div class="d-inline-block mb-2">
                            <div class="rounded-circle bg-<?php echo $paso_actual >= 3 ? 'success' : 'light'; ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <p class="small fw-bold">Pago</p>
                    </div>
                    <div class="text-center flex-grow-1">
                        <div class="d-inline-block mb-2">
                            <div class="rounded-circle bg-<?php echo $paso_actual >= 4 ? 'success' : 'light'; ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <p class="small fw-bold">Confirmación</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- PASO 1: DIRECCIÓN DE ENVÍO -->
            <?php if ($paso_actual === 1): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Mensajes de error -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <h6 class="alert-heading">Errores encontrados:</h6>
                                <ul class="mb-0 ms-3">
                                    <?php foreach ($errores as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Direcciones guardadas -->
                        <?php if (!empty($direcciones)): ?>
                            <div class="mb-4">
                                <h6 class="mb-3">Tus direcciones guardadas:</h6>
                                <div class="row">
                                    <?php foreach ($direcciones as $dir): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-2 cursor-pointer" onclick="cargarDireccion(<?php echo htmlspecialchars(json_encode($dir)); ?>)">
                                                <div class="card-body">
                                                    <p class="mb-1">
                                                        <strong><?php echo htmlspecialchars($dir['nombre']); ?></strong>
                                                    </p>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($dir['calle']); ?> <?php echo $dir['numero']; ?>,
                                                        <?php echo htmlspecialchars($dir['ciudad']); ?>, 
                                                        <?php echo htmlspecialchars($dir['provincia']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <hr>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de dirección -->
                        <form method="POST" action="checkout.php?paso=1">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="nombre_envio" class="form-label fw-bold">Nombre completo</label>
                                    <input type="text" class="form-control form-control-lg" id="nombre_envio" name="nombre_envio" value="<?php echo htmlspecialchars($direccion_envio['nombre'] ?? $usuario['nombre']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                    <input type="tel" class="form-control form-control-lg" id="telefono" name="telefono" value="<?php echo htmlspecialchars($direccion_envio['telefono'] ?? $usuario['telefono'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Correo electrónico</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?php echo htmlspecialchars($direccion_envio['email'] ?? $usuario['email']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="calle" class="form-label fw-bold">Calle</label>
                                    <input type="text" class="form-control form-control-lg" id="calle" name="calle" value="<?php echo htmlspecialchars($direccion_envio['calle'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="numero" class="form-label fw-bold">Número</label>
                                    <input type="text" class="form-control form-control-lg" id="numero" name="numero" value="<?php echo htmlspecialchars($direccion_envio['numero'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="piso" class="form-label fw-bold">Piso/Depto (Opcional)</label>
                                    <input type="text" class="form-control form-control-lg" id="piso" name="piso" value="<?php echo htmlspecialchars($direccion_envio['piso'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="codigo_postal" class="form-label fw-bold">Código Postal</label>
                                    <input type="text" class="form-control form-control-lg" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($direccion_envio['codigo_postal'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label fw-bold">Ciudad</label>
                                    <input type="text" class="form-control form-control-lg" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($direccion_envio['ciudad'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label fw-bold">Provincia</label>
                                    <input type="text" class="form-control form-control-lg" id="provincia" name="provincia" value="<?php echo htmlspecialchars($direccion_envio['provincia'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="pais" class="form-label fw-bold">País</label>
                                <select class="form-select form-select-lg" id="pais" name="pais" required>
                                    <option value="">Seleccionar país...</option>
                                    <option value="Argentina" <?php echo ($direccion_envio['pais'] ?? '') === 'Argentina' ? 'selected' : ''; ?>>Argentina</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Uruguay">Uruguay</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="<?php echo SITE_URL; ?>carrito/carrito.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Volver al Carrito
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    Continuar al Envío <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <!-- PASO 2: MÉTODO DE ENVÍO -->
            <?php elseif ($paso_actual === 2): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-truck"></i> Método de Envío
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Resumen de dirección -->
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2">Dirección de envío:</h6>
                            <p class="mb-0">
                                <?php echo htmlspecialchars($direccion_envio['nombre']); ?><br>
                                <?php echo htmlspecialchars($direccion_envio['calle']); ?> <?php echo $direccion_envio['numero']; ?>
                                <?php echo !empty($direccion_envio['piso']) ? ', ' . htmlspecialchars($direccion_envio['piso']) : ''; ?><br>
                                <?php echo htmlspecialchars($direccion_envio['codigo_postal']); ?> 
                                <?php echo htmlspecialchars($direccion_envio['ciudad']); ?>, 
                                <?php echo htmlspecialchars($direccion_envio['provincia']); ?>
                            </p>
                        </div>

                        <!-- Mensajes de error -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php foreach ($errores as $error): ?>
                                    <p class="mb-0"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Opciones de envío -->
                        <form method="POST" action="checkout.php?paso=2">
                            <h6 class="mb-3">Selecciona tu método de envío:</h6>

                            <div class="mb-3">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_envio" id="envio_estandar" value="estandar" <?php echo ($metodo_envio === 'estandar' || empty($metodo_envio)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="envio_estandar">
                                        <strong>Envío Estándar</strong><br>
                                        <small class="text-muted">Entrega en 3-5 días hábiles</small><br>
                                        <span class="fw-bold text-success">$50</span>
                                        <?php if ($subtotal >= 500): ?>
                                            <span class="badge bg-success ms-2">GRATIS (por compra mayor a $500)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_envio" id="envio_express" value="express" <?php echo $metodo_envio === 'express' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="envio_express">
                                        <strong>Envío Express</strong><br>
                                        <small class="text-muted">Entrega en 24-48 horas (CABA y GBA)</small><br>
                                        <span class="fw-bold text-success">$150</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_envio" id="envio_retiro" value="retiro" <?php echo $metodo_envio === 'retiro' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="envio_retiro">
                                        <strong>Retiro en Sucursal</strong><br>
                                        <small class="text-muted">Retira tu pedido en nuestras sucursales</small><br>
                                        <span class="fw-bold text-success">GRATIS</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="history.back()">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    Continuar al Pago <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <!-- PASO 3: MÉTODO DE PAGO -->
            <?php elseif ($paso_actual === 3): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-wallet"></i> Método de Pago
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Mensajes de error -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php foreach ($errores as $error): ?>
                                    <p class="mb-0"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Opciones de pago -->
                        <form method="POST" action="checkout.php?paso=3">
                            <h6 class="mb-3">Selecciona tu método de pago:</h6>

                            <div class="mb-3">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="pago_tarjeta" value="tarjeta" <?php echo ($metodo_pago === 'tarjeta' || empty($metodo_pago)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pago_tarjeta">
                                        <strong><i class="fab fa-cc-visa"></i> Tarjeta de Crédito/Débito</strong><br>
                                        <small class="text-muted">Visa, Mastercard, American Express</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="pago_paypal" value="paypal" <?php echo $metodo_pago === 'paypal' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pago_paypal">
                                        <strong><i class="fab fa-cc-paypal"></i> PayPal</strong><br>
                                        <small class="text-muted">Pago seguro a través de PayPal</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="pago_transferencia" value="transferencia" <?php echo $metodo_pago === 'transferencia' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pago_transferencia">
                                        <strong><i class="fas fa-university"></i> Transferencia Bancaria</strong><br>
                                        <small class="text-muted">Transferencia directa a nuestra cuenta</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check border rounded p-3">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="pago_efectivo" value="efectivo" <?php echo $metodo_pago === 'efectivo' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pago_efectivo">
                                        <strong><i class="fas fa-money-bill-wave"></i> Efectivo al Recibir</strong><br>
                                        <small class="text-muted">Paga cuando recibas tu pedido (solo CABA y alrededores)</small>
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-lock"></i> <strong>Pago Seguro:</strong> Tus datos de pago están protegidos con encriptación SSL de 256 bits.
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="history.back()">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    Revisar Pedido <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <!-- PASO 4: CONFIRMACIÓN -->
            <?php elseif ($paso_actual === 4): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle"></i> Revisa tu Pedido
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Resumen de dirección -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-1">
                                    <strong><?php echo htmlspecialchars($direccion_envio['nombre']); ?></strong>
                                </p>
                                <p class="mb-1">
                                    <?php echo htmlspecialchars($direccion_envio['calle']); ?> <?php echo $direccion_envio['numero']; ?>
                                    <?php echo !empty($direccion_envio['piso']) ? ', ' . htmlspecialchars($direccion_envio['piso']) : ''; ?>
                                </p>
                                <p class="mb-1">
                                    <?php echo htmlspecialchars($direccion_envio['codigo_postal']); ?> 
                                    <?php echo htmlspecialchars($direccion_envio['ciudad']); ?>, 
                                    <?php echo htmlspecialchars($direccion_envio['provincia']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($direccion_envio['telefono']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Resumen de envío -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-truck"></i> Método de Envío
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <?php 
                                $envio_textos = [
                                    'estandar' => 'Envío Estándar (3-5 días hábiles)',
                                    'express' => 'Envío Express (24-48 horas)',
                                    'retiro' => 'Retiro en Sucursal'
                                ];
                                echo $envio_textos[$metodo_envio] ?? 'No especificado';
                                ?>
                            </div>
                        </div>

                        <!-- Resumen de pago -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-wallet"></i> Método de Pago
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <?php 
                                $pago_textos = [
                                    'tarjeta' => 'Tarjeta de Crédito/Débito',
                                    'paypal' => 'PayPal',
                                    'transferencia' => 'Transferencia Bancaria',
                                    'efectivo' => 'Efectivo al Recibir'
                                ];
                                echo $pago_textos[$metodo_pago] ?? 'No especificado';
                                ?>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-box"></i> Productos
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($carrito as $item): ?>
                                            <?php $producto = obtener_producto_por_id($item['id']); ?>
                                            <?php if ($producto): ?>
                                                <?php $precio = $item['precio_descuento'] ?? $item['precio']; ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(truncar_texto($producto['nombre'], 40)); ?></td>
                                                    <td><?php echo $item['cantidad']; ?></td>
                                                    <td><?php echo formato_precio($precio); ?></td>
                                                    <td><?php echo formato_precio($precio * $item['cantidad']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Términos y condiciones -->
                        <div class="form-check mb-4 p-3 bg-light rounded">
                            <input class="form-check-input" type="checkbox" id="terminos_pedido" required>
                            <label class="form-check-label" for="terminos_pedido">
                                Acepto los <a href="#" target="_blank">términos y condiciones</a> 
                                y la <a href="#" target="_blank">política de privacidad</a>
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="history.back()">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                            <a 
                                href="<?php echo SITE_URL; ?>pedidos/procesar_pago.php" 
                                class="btn btn-success btn-lg"
                                id="btn-confirmar-pedido"
                            >
                                <i class="fas fa-check"></i> Confirmar Pedido
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Resumen lateral -->
        <div class="col-lg-4">
            <div class="card shadow-lg sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-receipt"></i> Resumen
                    </h6>
                </div>

                <div class="card-body">
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
                            <span>Envío:</span>
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

                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Total:</h6>
                            <h6 class="mb-0 text-success">
                                <?php echo formato_precio($total); ?>
                            </h6>
                        </div>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> 
                        Tienes <strong><?php echo $total_items; ?></strong> 
                        artículo(s) en tu pedido
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <a 
                        href="<?php echo SITE_URL; ?>carrito/carrito.php"
                        class="btn btn-sm btn-outline-secondary w-100"
                    >
                        <i class="fas fa-edit"></i> Editar Carrito
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
function cargarDireccion(direccion) {
    document.getElementById('nombre_envio').value = direccion.nombre;
    document.getElementById('telefono').value = direccion.telefono;
    document.getElementById('email').value = direccion.email;
    document.getElementById('calle').value = direccion.calle;
    document.getElementById('numero').value = direccion.numero;
    document.getElementById('piso').value = direccion.piso || '';
    document.getElementById('ciudad').value = direccion.ciudad;
    document.getElementById('provincia').value = direccion.provincia;
    document.getElementById('codigo_postal').value = direccion.codigo_postal;
    document.getElementById('pais').value = direccion.pais;
}

document.getElementById('btn-confirmar-pedido')?.addEventListener('click', function(e) {
    if (!document.getElementById('terminos_pedido').checked) {
        e.preventDefault();
        alert('Debes aceptar los términos y condiciones');
    }
});
</script>
