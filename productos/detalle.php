<?php
/**
 * =====================================================
 * DETALLE DEL PRODUCTO
 * =====================================================
 * Archivo: productos/detalle.php
 * 
 * Página con información completa del producto
 * =====================================================
 */

$titulo_pagina = "Detalle del Producto";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Obtener ID del producto
$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$producto_id) {
    header('Location: ' . SITE_URL . 'productos/catalogo.php');
    exit;
}

// Obtener producto
$producto = obtener_producto_por_id($producto_id);

if (!$producto) {
    header('HTTP/1.0 404 Not Found');
    $titulo_pagina = "Producto no encontrado";
    require_once dirname(__DIR__) . '/includes/header.php';
    ?>
    <div class="container py-5">
        <div class="alert alert-danger text-center">
            <h4><i class="fas fa-exclamation-triangle"></i> Producto no encontrado</h4>
            <p>El producto que buscas no existe o ha sido eliminado.</p>
            <a href="<?php echo SITE_URL; ?>productos/catalogo.php" class="btn btn-success">
                Volver al catálogo
            </a>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/includes/footer.php';
    exit;
}

// Obtener imágenes del producto
$imagenes = obtener_imagenes_producto($producto_id);

// Obtener reseñas del producto
$resenas = obtener_resenas_producto($producto_id);

// Obtener productos relacionados
$productos_relacionados = obtener_productos_relacionados($producto_id, 4);

// Incrementar contador de visualizaciones
incrementar_visualizaciones_producto($producto_id);
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo SITE_URL; ?>productos/catalogo.php">Catálogo</a>
            </li>
            <li class="breadcrumb-item active">
                <?php echo htmlspecialchars($producto['nombre']); ?>
            </li>
        </ol>
    </nav>

    <div class="row">
        <!-- Galería de imágenes -->
        <div class="col-md-6 mb-4">
            <div class="card border-0">
                <!-- Imagen principal -->
                <div class="position-relative mb-3">
                    <img 
                        id="imagen-principal"
                        src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>" 
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                        class="img-fluid rounded"
                        style="height: 400px; object-fit: cover; width: 100%;"
                    >
                    
                    <?php if ($producto['descuento'] > 0): ?>
                        <span class="position-absolute top-0 end-0 badge bg-danger p-3 m-3" style="font-size: 1.2rem;">
                            -<?php echo $producto['descuento']; ?>%
                        </span>
                    <?php endif; ?>

                    <?php if ($producto['stock'] <= 0): ?>
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 rounded d-flex align-items-center justify-content-center">
                            <span class="text-white fw-bold" style="font-size: 1.5rem;">AGOTADO</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Miniaturas -->
                <?php if (!empty($imagenes)): ?>
                    <div class="d-flex gap-2 overflow-auto pb-2">
                        <!-- Primera imagen (principal) -->
                        <img 
                            src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>"
                            alt="Miniatura"
                            class="img-thumbnail cursor-pointer rounded"
                            style="height: 80px; width: 80px; object-fit: cover; cursor: pointer;"
                            onclick="cambiarImagen(this.src)"
                        >
                        
                        <?php foreach ($imagenes as $img): ?>
                            <img 
                                src="<?php echo SITE_URL . htmlspecialchars($img['url']); ?>"
                                alt="Miniatura"
                                class="img-thumbnail cursor-pointer rounded"
                                style="height: 80px; width: 80px; object-fit: cover; cursor: pointer;"
                                onclick="cambiarImagen(this.src)"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información del producto -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <!-- Nombre y puntuación -->
                <h1 class="mb-2">
                    <?php echo htmlspecialchars($producto['nombre']); ?>
                </h1>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div>
                        <div class="text-warning">
                            <?php 
                            $puntuacion = (int)$producto['puntuacion'];
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $puntuacion ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <span class="fw-bold"><?php echo number_format($producto['puntuacion'], 1); ?> / 5</span>
                        <span class="text-muted">(<?php echo $producto['cantidad_resenas']; ?> reseñas)</span>
                    </div>
                </div>

                <hr>

                <!-- Descripción -->
                <p class="text-muted mb-4">
                    <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                </p>

                <!-- Precios -->
                <div class="bg-light p-3 rounded mb-4">
                    <?php if ($producto['descuento'] > 0): ?>
                        <div class="mb-2">
                            <span class="text-muted text-decoration-line-through">
                                Precio Original: <?php echo formato_precio($producto['precio']); ?>
                            </span>
                        </div>
                        <div>
                            <span class="display-5 text-success fw-bold">
                                <?php echo formato_precio($producto['precio_descuento']); ?>
                            </span>
                            <span class="badge bg-danger ms-2">
                                Ahorra: <?php echo formato_precio($producto['precio'] - $producto['precio_descuento']); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="display-5 text-success fw-bold">
                            <?php echo formato_precio($producto['precio']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <div class="mb-4">
                    <p class="mb-2">
                        <strong>Disponibilidad:</strong>
                        <?php if ($producto['stock'] > 10): ?>
                            <span class="badge bg-success">En stock (<?php echo $producto['stock']; ?> disponibles)</span>
                        <?php elseif ($producto['stock'] > 0): ?>
                            <span class="badge bg-warning">Stock limitado (<?php echo $producto['stock']; ?> disponibles)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Agotado</span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Características -->
                <?php if (!empty($producto['caracteristicas'])): ?>
                    <div class="mb-4">
                        <h5 class="mb-3">Características</h5>
                        <ul class="list-unstyled">
                            <?php 
                            $caracteristicas = json_decode($producto['caracteristicas'], true);
                            if (is_array($caracteristicas)) {
                                foreach ($caracteristicas as $carac => $valor) {
                                    echo '<li class="mb-2"><i class="fas fa-check text-success"></i> <strong>' . htmlspecialchars($carac) . ':</strong> ' . htmlspecialchars($valor) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Cantidad y botones -->
                <div class="mb-4">
                    <label for="cantidad" class="form-label fw-bold">Cantidad</label>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group" style="width: 120px;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-menos">-</button>
                            <input 
                                type="number" 
                                class="form-control text-center" 
                                id="cantidad" 
                                value="1" 
                                min="1"
                                max="<?php echo $producto['stock']; ?>"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="btn-mas">+</button>
                        </div>
                        <small class="text-muted">
                            (Máximo: <?php echo $producto['stock']; ?>)
                        </small>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-grid gap-2 mb-3">
                    <?php if ($producto['stock'] > 0): ?>
                        <button 
                            class="btn btn-success btn-lg"
                            id="btn-agregar-carrito"
                            data-producto-id="<?php echo $producto['id']; ?>"
                        >
                            <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-ban"></i> Producto Agotado
                        </button>
                    <?php endif; ?>
                    
                    <button 
                        class="btn btn-outline-danger btn-lg"
                        id="btn-agregar-favorito"
                        data-producto-id="<?php echo $producto['id']; ?>"
                    >
                        <i class="far fa-heart"></i> Agregar a Favoritos
                    </button>
                </div>

                <!-- Información adicional -->
                <div class="alert alert-info">
                    <p class="mb-0">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Envío gratis</strong> en compras mayores a $500. 
                        <strong>Garantía de 30 días</strong> en todos nuestros productos.
                    </p>
                </div>

                <!-- Compartir -->
                <div class="pt-3 border-top">
                    <p class="mb-2"><strong>Compartir:</strong></p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-primary">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-success">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-link"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Descripción, Ingredientes, Reseñas -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#descripcion" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Descripción Completa
                    </button>
                </li>
                <?php if (!empty($producto['ingredientes'])): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ing-tab" data-bs-toggle="tab" data-bs-target="#ingredientes" type="button" role="tab">
                            <i class="fas fa-flask"></i> Ingredientes
                        </button>
                    </li>
                <?php endif; ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="res-tab" data-bs-toggle="tab" data-bs-target="#resenas" type="button" role="tab">
                        <i class="fas fa-comments"></i> Reseñas (<?php echo count($resenas); ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Descripción -->
                <div class="tab-pane fade show active" id="descripcion" role="tabpanel">
                    <div class="card border-0 p-4">
                        <h5 class="mb-3">Descripción Completa</h5>
                        <p class="text-muted">
                            <?php echo nl2br(htmlspecialchars($producto['descripcion_completa'] ?? $producto['descripcion'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Ingredientes -->
                <?php if (!empty($producto['ingredientes'])): ?>
                    <div class="tab-pane fade" id="ingredientes" role="tabpanel">
                        <div class="card border-0 p-4">
                            <h5 class="mb-3">Ingredientes</h5>
                            <ul class="list-unstyled">
                                <?php 
                                $ingredientes = explode(',', $producto['ingredientes']);
                                foreach ($ingredientes as $ingrediente) {
                                    echo '<li class="mb-2"><i class="fas fa-droplet text-success"></i> ' . trim($ingrediente) . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reseñas -->
                <div class="tab-pane fade" id="resenas" role="tabpanel">
                    <div class="card border-0">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Reseñas de Clientes</h5>

                            <?php if (!empty($resenas)): ?>
                                <?php foreach ($resenas as $resena): ?>
                                    <div class="mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($resena['nombre_cliente']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo tiempo_transcurrido($resena['fecha_creacion']); ?>
                                                </small>
                                            </div>
                                            <div class="text-warning">
                                                <?php 
                                                $puntuacion_res = (int)$resena['puntuacion'];
                                                for ($i = 0; $i < 5; $i++) {
                                                    echo $i < $puntuacion_res ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <h6><?php echo htmlspecialchars($resena['titulo']); ?></h6>
                                        <p class="mb-0 text-muted">
                                            <?php echo nl2br(htmlspecialchars($resena['comentario'])); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">
                                    No hay reseñas aún. <a href="#">Sé el primero en comentar</a>
                                </p>
                            <?php endif; ?>

                            <!-- Formulario para agregar reseña -->
                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="mb-3">Deja tu Reseña</h6>
                                    <form method="POST" action="<?php echo SITE_URL; ?>productos/agregar_resena.php">
                                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="puntuacion" class="form-label">Puntuación</label>
                                            <select class="form-select" id="puntuacion" name="puntuacion" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                                <option value="3">⭐⭐⭐ Bueno</option>
                                                <option value="2">⭐⭐ Aceptable</option>
                                                <option value="1">⭐ Malo</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="titulo" class="form-label">Título</label>
                                            <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Resumen de tu experiencia">
                                        </div>

                                        <div class="mb-3">
                                            <label for="comentario" class="form-label">Comentario</label>
                                            <textarea class="form-control" id="comentario" name="comentario" rows="4" required placeholder="Cuéntanos tu experiencia..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane"></i> Publicar Reseña
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="mt-4 pt-4 border-top alert alert-info">
                                    <p class="mb-0">
                                        <a href="<?php echo SITE_URL; ?>usuarios/login.php">Inicia sesión</a> 
                                        para dejar tu reseña
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Relacionados -->
    <?php if (!empty($productos_relacionados)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">
                    <i class="fas fa-link"></i> Productos Relacionados
                </h3>
            </div>
            <?php foreach ($productos_relacionados as $relacionado): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            <img 
                                src="<?php echo SITE_URL . htmlspecialchars($relacionado['imagen_principal']); ?>" 
                                alt="<?php echo htmlspecialchars($relacionado['nombre']); ?>"
                                class="card-img-top w-100 h-100 object-fit-cover"
                            >
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <?php echo htmlspecialchars(truncar_texto($relacionado['nombre'], 40)); ?>
                            </h6>
                            <div class="text-warning mb-2">
                                <?php 
                                $pun = (int)$relacionado['puntuacion'];
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $pun ? '<i class="fas fa-star fa-sm"></i>' : '<i class="far fa-star fa-sm"></i>';
                                }
                                ?>
                            </div>
                            <p class="h6 text-success mb-3">
                                <?php echo formato_precio($relacionado['precio_descuento'] ?? $relacionado['precio']); ?>
                            </p>
                            <a 
                                href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $relacionado['id']; ?>"
                                class="btn btn-sm btn-outline-success mt-auto"
                            >
                                Ver Producto
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
// Cambiar imagen principal
function cambiarImagen(src) {
    document.getElementById('imagen-principal').src = src;
}

// Controles de cantidad
document.getElementById('btn-menos').addEventListener('click', function() {
    let cantidad = parseInt(document.getElementById('cantidad').value);
    if (cantidad > 1) {
        document.getElementById('cantidad').value = cantidad - 1;
    }
});

document.getElementById('btn-mas').addEventListener('click', function() {
    let cantidad = parseInt(document.getElementById('cantidad').value);
    let maximo = parseInt(document.getElementById('cantidad').max);
    if (cantidad < maximo) {
        document.getElementById('cantidad').value = cantidad + 1;
    }
});
</script>
<script src="<?php echo SITE_URL; ?>assets/js/productos.js"></script>
