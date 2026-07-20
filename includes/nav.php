<?php
/**
 * =====================================================
 * ARCHIVO DE NAVEGACIÓN
 * =====================================================
 * Archivo: includes/nav.php
 * 
 * Barra de navegación secundaria y menús contextuales
 * =====================================================
 */

require_once dirname(__DIR__) . '/includes/funciones.php';
iniciar_sesion();

$categorias = obtener_categorias();
?>

<!-- Navbar Secundario - Categorías -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container-fluid px-4">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#categoriesNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="categoriesNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>productos/catalogo.php">
                        <i class="fas fa-th"></i> Todos los productos
                    </a>
                </li>
                
                <?php if (!empty($categorias)): ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=<?php echo $categoria['id']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <li class="nav-item ms-auto">
                    <div class="input-group input-group-sm">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar productos..." 
                            id="buscador-productos"
                        >
                        <button class="btn btn-outline-success" type="button" id="btn-buscar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Scripts para navegación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda de productos
    const buscador = document.getElementById('buscador-productos');
    const btnBuscar = document.getElementById('btn-buscar');
    
    if (buscador && btnBuscar) {
        btnBuscar.addEventListener('click', function() {
            const termino = buscador.value.trim();
            if (termino) {
                window.location.href = '<?php echo SITE_URL; ?>productos/catalogo.php?buscar=' + encodeURIComponent(termino);
            }
        });
        
        buscador.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                btnBuscar.click();
            }
        });
    }
});
</script>
