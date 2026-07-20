<?php
/**
 * =====================================================
 * CATÁLOGO DE PRODUCTOS
 * =====================================================
 * Archivo: productos/catalogo.php
 * 
 * Listado de productos con filtros y búsqueda
 * =====================================================
 */

$titulo_pagina = "Catálogo de Productos";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Variables
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'reciente';
$precio_min = isset($_GET['precio_min']) ? (int)$_GET['precio_min'] : 0;
$precio_max = isset($_GET['precio_max']) ? (int)$_GET['precio_max'] : 10000;

// Parámetros para la búsqueda
$parametros = [
    'categoria_id' => $categoria_id,
    'buscar' => $buscar,
    'ordenar' => $ordenar,
    'precio_min' => $precio_min,
    'precio_max' => $precio_max,
    'pagina' => $pagina_actual,
    'por_pagina' => 12
];

// Obtener productos y total
$resultado = obtener_productos_filtrados($parametros);
$productos = $resultado['productos'];
$total_productos = $resultado['total'];
$total_paginas = ceil($total_productos / 12);

// Obtener categorías para el filtro
$categorias = obtener_categorias();

// URL base para paginación
$url_base = "catalogo.php?" . http_build_query(array_filter([
    'categoria' => $categoria_id,
    'buscar' => $buscar,
    'ordenar' => $ordenar,
    'precio_min' => $precio_min,
    'precio_max' => $precio_max
]));
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <!-- Sidebar - Filtros -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="catalogo.php" id="form-filtros">
                        <!-- Búsqueda -->
                        <div class="mb-4">
                            <label for="buscar" class="form-label fw-bold">Buscar</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="buscar" 
                                name="buscar"
                                value="<?php echo htmlspecialchars($buscar); ?>"
                                placeholder="Nombre del producto..."
                            >
                        </div>

                        <!-- Categoría -->
                        <div class="mb-4">
                            <label for="categoria" class="form-label fw-bold">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option 
                                        value="<?php echo $cat['id']; ?>"
                                        <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rango de Precio -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Rango de Precio</label>
                            <div class="d-flex gap-2 mb-2">
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm" 
                                    name="precio_min"
                                    value="<?php echo $precio_min; ?>"
                                    placeholder="Mín"
                                    min="0"
                                >
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm" 
                                    name="precio_max"
                                    value="<?php echo $precio_max; ?>"
                                    placeholder="Máx"
                                    min="0"
                                >
                            </div>
                            <small class="text-muted">
                                Desde: $<?php echo $precio_min; ?> hasta $<?php echo $precio_max; ?>
                            </small>
                        </div>

                        <!-- Ordenar -->
                        <div class="mb-4">
                            <label for="ordenar" class="form-label fw-bold">Ordenar por</label>
                            <select class="form-select" id="ordenar" name="ordenar">
                                <option value="reciente" <?php echo $ordenar == 'reciente' ? 'selected' : ''; ?>>
                                    Más Reciente
                                </option>
                                <option value="precio_asc" <?php echo $ordenar == 'precio_asc' ? 'selected' : ''; ?>>
                                    Precio: Menor a Mayor
                                </option>
                                <option value="precio_desc" <?php echo $ordenar == 'precio_desc' ? 'selected' : ''; ?>>
                                    Precio: Mayor a Menor
                                </option>
                                <option value="popular" <?php echo $ordenar == 'popular' ? 'selected' : ''; ?>>
                                    Más Popular
                                </option>
                                <option value="puntuacion" <?php echo $ordenar == 'puntuacion' ? 'selected' : ''; ?>>
                                    Mejor Puntuado
                                </option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                            <a href="catalogo.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenido - Productos -->
        <div class="col-md-9">
            <!-- Header del Catálogo -->
            <div class="mb-4">
                <h2 class="mb-2">
                    <i class="fas fa-th"></i> Catálogo de Productos
                </h2>
                <p class="text-muted">
                    <?php 
                    if ($buscar) {
                        echo "Resultados para: <strong>" . htmlspecialchars($buscar) . "</strong>";
                    }
                    if ($categoria_id) {
                        $categoria = array_filter($categorias, fn($c) => $c['id'] == $categoria_id);
                        $categoria = reset($categoria);
                        echo ($buscar ? " | " : "") . "Categoría: <strong>" . htmlspecialchars($categoria['nombre'] ?? '') . "</strong>";
                    }
                    echo " | <strong>" . $total_productos . "</strong> productos encontrados";
                    ?>
                </p>
            </div>

            <!-- Productos -->
            <?php if (!empty($productos)): ?>
                <div class="row">
                    <?php foreach ($productos as $producto): ?>
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
                                    
                                    <p class="card-text text-muted text-sm mb-2">
                                        <?php echo htmlspecialchars(truncar_texto($producto['descripcion'], 60)); ?>
                                    </p>

                                    <!-- Puntuación -->
                                    <div class="mb-2">
                                        <div class="text-warning">
                                            <?php 
                                            $puntuacion = (int)$producto['puntuacion'];
                                            for ($i = 0; $i < 5; $i++) {
                                                echo $i < $puntuacion ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo number_format($producto['puntuacion'], 1); ?> 
                                            (<?php echo $producto['cantidad_resenas']; ?>)
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

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <!-- Página anterior -->
                            <?php if ($pagina_actual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $url_base; ?>&pagina=<?php echo $pagina_actual - 1; ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Anterior</span>
                                </li>
                            <?php endif; ?>

                            <!-- Números de página -->
                            <?php
                            $rango_inicio = max(1, $pagina_actual - 2);
                            $rango_fin = min($total_paginas, $pagina_actual + 2);
                            
                            if ($rango_inicio > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . $url_base . '&pagina=1">1</a></li>';
                                if ($rango_inicio > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $rango_inicio; $i <= $rango_fin; $i++) {
                                $activa = $i == $pagina_actual ? 'active' : '';
                                echo '<li class="page-item ' . $activa . '"><a class="page-link" href="' . $url_base . '&pagina=' . $i . '">' . $i . '</a></li>';
                            }
                            
                            if ($rango_fin < $total_paginas) {
                                if ($rango_fin < $total_paginas - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . $url_base . '&pagina=' . $total_paginas . '">' . $total_paginas . '</a></li>';
                            }
                            ?>

                            <!-- Página siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $url_base; ?>&pagina=<?php echo $pagina_actual + 1; ?>">
                                        Siguiente
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Siguiente</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <h4>
                        <i class="fas fa-search"></i> No se encontraron productos
                    </h4>
                    <p class="text-muted mb-0">
                        Intenta con otros filtros o términos de búsqueda
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script src="<?php echo SITE_URL; ?>assets/js/productos.js"></script>
