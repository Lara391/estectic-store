<?php
/**
 * =====================================================
 * MIS PEDIDOS - HISTORIAL Y SEGUIMIENTO
 * =====================================================
 * Archivo: pedidos/mis_pedidos.php
 * 
 * Historial de pedidos y seguimiento de compras
 * =====================================================
 */

$titulo_pagina = "Mis Pedidos";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/login.php?destino=../pedidos/mis_pedidos.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener parámetros
$filtro_estado = $_GET['estado'] ?? '';
$pagina = (int)($_GET['pagina'] ?? 1);
$por_pagina = 10;

// Obtener pedidos
$pedidos = obtener_pedidos_usuario($usuario_id, null, $filtro_estado);
$total_pedidos = count($pedidos);

// Paginar
$total_paginas = ceil($total_pedidos / $por_pagina);
$inicio = ($pagina - 1) * $por_pagina;
$pedidos_pagina = array_slice($pedidos, $inicio, $por_pagina);

// Estados disponibles
$estados = ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'];
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>usuarios/perfil.php">Mi Perfil</a>
            </li>
            <li class="breadcrumb-item active">Mis Pedidos</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2">
                <i class="fas fa-shopping-bag"></i> Mis Pedidos
            </h1>
            <p class="text-muted">Total de pedidos: <strong><?php echo $total_pedidos; ?></strong></p>
        </div>
    </div>

    <div class="row">
        <!-- Filtros -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h6>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-3">Estado del Pedido</h6>
                        <div class="list-group list-group-flush">
                            <a 
                                href="mis_pedidos.php" 
                                class="list-group-item list-group-item-action <?php echo empty($filtro_estado) ? 'active bg-success' : ''; ?>"
                            >
                                Todos
                                <span class="badge bg-light text-dark float-end">
                                    <?php echo $total_pedidos; ?>
                                </span>
                            </a>

                            <?php foreach ($estados as $estado): ?>
                                <?php 
                                $count = count(array_filter($pedidos, fn($p) => $p['estado'] === $estado));
                                ?>
                                <a 
                                    href="mis_pedidos.php?estado=<?php echo $estado; ?>" 
                                    class="list-group-item list-group-item-action <?php echo $filtro_estado === $estado ? 'active bg-success' : ''; ?>"
                                >
                                    <span class="badge bg-<?php echo obtener_color_estado($estado); ?> me-2">
                                        <?php echo ucfirst($estado); ?>
                                    </span>
                                    <span class="badge bg-light text-dark float-end">
                                        <?php echo $count; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Rango de fechas -->
                    <hr>
                    <div class="mb-3">
                        <h6 class="mb-2">Rango de Fechas</h6>
                        <form method="GET" action="mis_pedidos.php">
                            <div class="mb-2">
                                <label class="form-label small">Desde</label>
                                <input type="date" class="form-control form-control-sm" name="desde">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Hasta</label>
                                <input type="date" class="form-control form-control-sm" name="hasta">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                Filtrar
                            </button>
                        </form>
                    </div>

                    <!-- Información -->
                    <hr>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i>
                        Haz clic en cualquier pedido para ver detalles y seguimiento.
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-lg-9">
            <?php if (!empty($pedidos_pagina)): ?>
                <!-- Lista de pedidos -->
                <div class="mb-4">
                    <?php foreach ($pedidos_pagina as $pedido): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Información del pedido -->
                                    <div class="col-md-6">
                                        <h6 class="mb-2">
                                            <strong>Pedido #<?php echo $pedido['numero_pedido']; ?></strong>
                                        </h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-box"></i>
                                            <?php echo $pedido['cantidad_items']; ?> producto(s)
                                        </p>
                                    </div>

                                    <!-- Estado y total -->
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <span class="badge bg-<?php echo obtener_color_estado($pedido['estado']); ?> p-2 mb-2">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                            <p class="mb-0 fw-bold">
                                                <?php echo formato_precio($pedido['total']); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="col-md-3 text-end">
                                        <a 
                                            href="detalle.php?id=<?php echo $pedido['id']; ?>"
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </a>
                                    </div>
                                </div>

                                <!-- Barra de progreso de estado -->
                                <div class="mt-3 pt-3 border-top">
                                    <div class="progress" style="height: 6px;">
                                        <?php 
                                        $estados_orden = ['pendiente' => 20, 'confirmado' => 40, 'enviado' => 70, 'entregado' => 100];
                                        $porcentaje = $estados_orden[$pedido['estado']] ?? 0;
                                        ?>
                                        <div 
                                            class="progress-bar bg-success" 
                                            style="width: <?php echo $porcentaje; ?>%"
                                        ></div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <?php 
                                        $mensajes_estado = [
                                            'pendiente' => 'Tu pedido está siendo procesado',
                                            'confirmado' => 'Tu pedido ha sido confirmado y será enviado pronto',
                                            'enviado' => 'Tu pedido está en camino',
                                            'entregado' => 'Tu pedido ha sido entregado',
                                            'cancelado' => 'Este pedido ha sido cancelado'
                                        ];
                                        echo $mensajes_estado[$pedido['estado']] ?? 'Estado desconocido';
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="mis_pedidos.php?pagina=1<?php echo $filtro_estado ? '&estado=' . $filtro_estado : ''; ?>">
                                        Primera
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="mis_pedidos.php?pagina=<?php echo $pagina - 1; ?><?php echo $filtro_estado ? '&estado=' . $filtro_estado : ''; ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                                    <a class="page-link" href="mis_pedidos.php?pagina=<?php echo $i; ?><?php echo $filtro_estado ? '&estado=' . $filtro_estado : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagina < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="mis_pedidos.php?pagina=<?php echo $pagina + 1; ?><?php echo $filtro_estado ? '&estado=' . $filtro_estado : ''; ?>">
                                        Siguiente
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="mis_pedidos.php?pagina=<?php echo $total_paginas; ?><?php echo $filtro_estado ? '&estado=' . $filtro_estado : ''; ?>">
                                        Última
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- Sin pedidos -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                        <h4 class="mb-3">No hay pedidos</h4>
                        <p class="text-muted mb-4">
                            <?php 
                            if ($filtro_estado) {
                                echo "No tienes pedidos con estado " . ucfirst($filtro_estado);
                            } else {
                                echo "Aún no has realizado ningún pedido. ¡Comienza a comprar ahora!";
                            }
                            ?>
                        </p>
                        <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-success btn-lg">
                            <i class="fas fa-shopping-bag"></i> Ver Catálogo
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ayuda -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-question-circle"></i> ¿Necesitas ayuda?
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Seguimiento de pedidos:</strong><br>
                                Haz clic en "Ver Detalles" para ver el estado actualizado y número de seguimiento de tu pedido.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Cambios en tu pedido:</strong><br>
                                Si necesitas modificar tu pedido, hazlo antes de que sea confirmado.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="small mb-0">
                                <strong>Devoluciones:</strong><br>
                                Tienes 30 días para devolver un producto en perfecto estado.
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
// Ningún script adicional requerido por ahora
</script>

<?php
/**
 * Función auxiliar: Obtener color del estado
 */
function obtener_color_estado($estado) {
    $colores = [
        'pendiente' => 'warning',
        'confirmado' => 'info',
        'enviado' => 'primary',
        'entregado' => 'success',
        'cancelado' => 'danger'
    ];
    return $colores[$estado] ?? 'secondary';
}
?>
