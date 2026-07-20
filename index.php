<?php
/**
 * =====================================================
 * PÁGINA DE INICIO
 * =====================================================
 * Archivo: index.php
 * 
 * Página principal del sitio
 * =====================================================
 */

$titulo_pagina = "Inicio";
require_once 'includes/header.php';
require_once 'includes/nav.php';
require_once 'db/funciones_bd.php';

// Obtener productos destacados
$productos_destacados = obtener_productos_destacados(6);

// Obtener promociones activas
$promociones = obtener_promociones_activas();
?>

<!-- Hero Banner -->
<section class="hero-banner bg-success text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-3">
                    Bienvenido a Estetic Store
                </h1>
                <p class="lead mb-4">
                    Descubre nuestra colección de productos de estética y belleza de calidad premium
                </p>
                <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-light btn-lg">
                    <i class="fas fa-shopping-bag"></i> Ver Catálogo
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?php echo SITE_URL; ?>assets/img/hero-banner.jpg" alt="Productos de belleza" class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>

<!-- Promociones -->
<?php if (!empty($promociones)): ?>
<section id="promociones" class="mb-5">
    <div class="container">
        <h2 class="mb-4 text-center">
            <i class="fas fa-tag text-danger"></i> Promociones Especiales
        </h2>
        <div class="row">
            <?php foreach ($promociones as $promo): ?>
                <div class="col-md-4 mb-3">
                    <div class="card border-danger h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($promo['nombre']); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($promo['descripcion']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-danger">
                                    <?php echo $promo['descuento']; ?>% OFF
                                </span>
                                <small class="text-muted">
                                    Hasta: <?php echo formato_fecha($promo['fecha_fin']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Productos Destacados -->
<section class="mb-5">
    <div class="container">
        <h2 class="mb-4 text-center">
            <i class="fas fa-star text-warning"></i> Productos Destacados
        </h2>
        
        <?php if (!empty($productos_destacados)): ?>
            <div class="row">
                <?php foreach ($productos_destacados as $producto): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm hover-shadow transition">
                            <!-- Imagen del producto -->
                            <div class="position-relative overflow-hidden" style="height: 250px;">
                                <img 
                                    src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>" 
                                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                    class="card-img-top w-100 h-100 object-fit-cover"
                                >
                                <?php if ($producto['descuento'] > 0): ?>
                                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">
                                        -<?php echo $producto['descuento']; ?>%
                                    </span>
                                <?php endif; ?>
                                <?php if ($producto['stock'] <= 0): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                                        <span class="text-white fw-bold">AGOTADO</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Detalles -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars(truncar_texto($producto['nombre'], 50)); ?>
                                </h5>
                                
                                <p class="card-text text-muted text-sm mb-3">
                                    <?php echo htmlspecialchars(truncar_texto($producto['descripcion'], 80)); ?>
                                </p>

                                <!-- Puntuación -->
                                <div class="mb-2">
                                    <i class="fas fa-star text-warning"></i>
                                    <small class="text-muted">
                                        <?php echo number_format($producto['puntuacion'], 1); ?> 
                                        (<?php echo $producto['cantidad_resenas']; ?> reseñas)
                                    </small>
                                </div>

                                <!-- Precios -->
                                <div class="mb-3">
                                    <?php if ($producto['descuento'] > 0): ?>
                                        <span class="h5 text-success">
                                            <?php echo formato_precio($producto['precio_descuento']); ?>
                                        </span>
                                        <span class="text-muted text-decoration-line-through ms-2">
                                            <?php echo formato_precio($producto['precio']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="h5 text-success">
                                            <?php echo formato_precio($producto['precio']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Botones -->
                                <div class="d-grid gap-2 mt-auto">
                                    <a 
                                        href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $producto['id']; ?>"
                                        class="btn btn-outline-success btn-sm"
                                    >
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                    <?php if ($producto['stock'] > 0): ?>
                                        <button 
                                            class="btn btn-success btn-sm agregar-carrito"
                                            data-producto-id="<?php echo $producto['id']; ?>"
                                        >
                                            <i class="fas fa-shopping-cart"></i> Agregar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            Agotado
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-lg btn-outline-success">
                    Ver Todos los Productos
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p>No hay productos disponibles en este momento.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Beneficios -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-truck fa-3x text-success"></i>
                </div>
                <h5>Envío Rápido</h5>
                <p class="text-muted">Entrega en 24-48 horas a todo el país</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-lock fa-3x text-success"></i>
                </div>
                <h5>Compra Segura</h5>
                <p class="text-muted">Protegemos tus datos con encriptación SSL</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-undo fa-3x text-success"></i>
                </div>
                <h5>Devoluciones</h5>
                <p class="text-muted">Garantía de satisfacción o tu dinero de vuelta</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-headset fa-3x text-success"></i>
                </div>
                <h5>Soporte 24/7</h5>
                <p class="text-muted">Atendemos tus dudas en horario extendido</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section id="contacto" class="bg-success text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-3">Suscríbete a nuestro Newsletter</h3>
                <p>Recibe las mejores ofertas, tips de belleza y promociones exclusivas</p>
            </div>
            <div class="col-md-6">
                <form action="<?php echo SITE_URL; ?>newsletter/suscribir.php" method="POST">
                    <div class="input-group">
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="Tu correo electrónico"
                            required
                        >
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-paper-plane"></i> Suscribir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="<?php echo SITE_URL; ?>assets/js/productos.js"></script>
