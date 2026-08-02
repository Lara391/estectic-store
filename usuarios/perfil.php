<?php
/**
 * =====================================================
 * PERFIL DE USUARIO
 * =====================================================
 * Archivo: usuarios/perfil.php
 * 
 * Gestión de perfil y datos del usuario
 * =====================================================
 */

$titulo_pagina = "Mi Perfil";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/nav.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario = obtener_usuario_por_id($usuario_id);

// Obtener datos adicionales
$direcciones = obtener_direcciones_usuario($usuario_id);
$pedidos_recientes = obtener_pedidos_usuario($usuario_id, 5);
$favoritos = obtener_favoritos_usuario($usuario_id);

// Variables para procesar cambios
$tab_activa = $_GET['tab'] ?? 'inicio';
$errores = [];
$exito = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'actualizar_perfil') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($nombre)) {
            $errores[] = 'El nombre es requerido.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido.';
        }

        if (empty($errores)) {
            $actualizado = actualizar_usuario($usuario_id, [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono
            ]);

            if ($actualizado) {
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_email'] = $email;
                $usuario = obtener_usuario_por_id($usuario_id);
                $exito = 'Perfil actualizado correctamente.';
                $tab_activa = 'perfil';
            } else {
                $errores[] = 'Error al actualizar el perfil.';
            }
        }
        $tab_activa = 'perfil';
    }

    elseif ($accion === 'cambiar_password') {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirmar = $_POST['password_confirmar'] ?? '';

        if (empty($password_actual)) {
            $errores[] = 'Debes ingresar tu contraseña actual.';
        } elseif (!password_verify($password_actual, $usuario['password'])) {
            $errores[] = 'La contraseña actual es incorrecta.';
        }

        if (empty($password_nueva)) {
            $errores[] = 'La nueva contraseña es requerida.';
        } elseif (strlen($password_nueva) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password_nueva !== $password_confirmar) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errores)) {
            $actualizado = actualizar_usuario($usuario_id, [
                'password' => password_hash($password_nueva, PASSWORD_BCRYPT)
            ]);

            if ($actualizado) {
                $exito = 'Contraseña actualizada correctamente.';
            } else {
                $errores[] = 'Error al cambiar la contraseña.';
            }
        }
        $tab_activa = 'seguridad';
    }

    elseif ($accion === 'agregar_direccion') {
        $nombre = trim($_POST['nombre'] ?? '');
        $calle = trim($_POST['calle'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $codigo_postal = trim($_POST['codigo_postal'] ?? '');

        if (empty($nombre) || empty($calle) || empty($numero) || empty($ciudad) || empty($provincia) || empty($codigo_postal)) {
            $errores[] = 'Todos los campos son requeridos.';
        }

        if (empty($errores)) {
            $agregada = agregar_direccion_usuario($usuario_id, [
                'nombre' => $nombre,
                'calle' => $calle,
                'numero' => $numero,
                'piso' => $_POST['piso'] ?? '',
                'ciudad' => $ciudad,
                'provincia' => $provincia,
                'codigo_postal' => $codigo_postal
            ]);

            if ($agregada) {
                $exito = 'Dirección agregada correctamente.';
                $direcciones = obtener_direcciones_usuario($usuario_id);
            } else {
                $errores[] = 'Error al agregar la dirección.';
            }
        }
        $tab_activa = 'direcciones';
    }

    elseif ($accion === 'eliminar_direccion') {
        $direccion_id = $_POST['direccion_id'] ?? '';
        if (eliminar_direccion($direccion_id)) {
            $exito = 'Dirección eliminada correctamente.';
            $direcciones = obtener_direcciones_usuario($usuario_id);
        } else {
            $errores[] = 'Error al eliminar la dirección.';
        }
        $tab_activa = 'direcciones';
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
            <li class="breadcrumb-item active">Mi Perfil</li>
        </ol>
    </nav>

    <!-- Encabezado de perfil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-4">
                        <div>
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                            <p class="text-muted mb-2">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['email']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar"></i> Miembro desde <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                            </p>
                        </div>
                        <div class="ms-auto">
                            <a href="<?php echo SITE_URL; ?>usuarios/login.php?accion=logout" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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
        <!-- Menú lateral -->
        <div class="col-md-3">
            <div class="list-group sticky-top" style="top: 20px;">
                <a 
                    href="?tab=inicio" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'inicio' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a 
                    href="?tab=perfil" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'perfil' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-user-edit"></i> Mi Perfil
                </a>
                <a 
                    href="?tab=pedidos" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'pedidos' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-box"></i> Mis Pedidos
                </a>
                <a 
                    href="?tab=direcciones" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'direcciones' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-map-marker-alt"></i> Direcciones
                </a>
                <a 
                    href="?tab=favoritos" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'favoritos' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-heart"></i> Favoritos
                </a>
                <a 
                    href="?tab=seguridad" 
                    class="list-group-item list-group-item-action <?php echo $tab_activa === 'seguridad' ? 'active' : ''; ?>"
                >
                    <i class="fas fa-lock"></i> Seguridad
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-md-9">
            <!-- TAB: INICIO -->
            <?php if ($tab_activa === 'inicio'): ?>
                <div class="row">
                    <!-- Estadísticas -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-shopping-bag fa-2x text-success mb-3"></i>
                                <h5 class="card-title">Mis Pedidos</h5>
                                <p class="h3 text-success mb-0">
                                    <?php echo count($pedidos_recientes); ?>
                                </p>
                                <a href="?tab=pedidos" class="btn btn-sm btn-outline-success mt-3">
                                    Ver todos
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-heart fa-2x text-danger mb-3"></i>
                                <h5 class="card-title">Favoritos</h5>
                                <p class="h3 text-danger mb-0">
                                    <?php echo count($favoritos); ?>
                                </p>
                                <a href="?tab=favoritos" class="btn btn-sm btn-outline-danger mt-3">
                                    Ver todos
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-map-marker-alt fa-2x text-info mb-3"></i>
                                <h5 class="card-title">Direcciones</h5>
                                <p class="h3 text-info mb-0">
                                    <?php echo count($direcciones); ?>
                                </p>
                                <a href="?tab=direcciones" class="btn btn-sm btn-outline-info mt-3">
                                    Gestionar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-lock fa-2x text-warning mb-3"></i>
                                <h5 class="card-title">Seguridad</h5>
                                <p class="text-muted">Cambiar contraseña</p>
                                <a href="?tab=seguridad" class="btn btn-sm btn-outline-warning mt-3">
                                    Configurar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pedidos recientes -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-history"></i> Pedidos Recientes
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nº Pedido</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pedidos_recientes)): ?>
                                    <?php foreach ($pedidos_recientes as $pedido): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $pedido['numero_pedido']; ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?>
                                            </td>
                                            <td>
                                                <strong><?php echo formato_precio($pedido['total']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo obtener_color_estado($pedido['estado']); ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>pedidos/detalle.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-outline-success">
                                                    Ver detalles
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No tienes pedidos aún
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- TAB: PERFIL -->
            <?php elseif ($tab_activa === 'perfil'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit"></i> Editar Perfil
                        </h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="perfil.php?tab=perfil">
                            <input type="hidden" name="accion" value="actualizar_perfil">

                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold">Nombre Completo</label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg"
                                    id="nombre" 
                                    name="nombre"
                                    value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg"
                                    id="email" 
                                    name="email"
                                    value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Este es tu email de inicio de sesión
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                <input 
                                    type="tel" 
                                    class="form-control form-control-lg"
                                    id="telefono" 
                                    name="telefono"
                                    value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha de Registro</label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg"
                                    value="<?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?>"
                                    disabled
                                >
                            </div>

                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </form>
                    </div>
                </div>

            <!-- TAB: PEDIDOS -->
            <?php elseif ($tab_activa === 'pedidos'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-box"></i> Mis Pedidos
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nº Pedido</th>
                                    <th>Fecha</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $todos_pedidos = obtener_pedidos_usuario($usuario_id);
                                if (!empty($todos_pedidos)): 
                                ?>
                                    <?php foreach ($todos_pedidos as $pedido): ?>
                                        <tr>
                                            <td><strong>#<?php echo $pedido['numero_pedido']; ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                            <td><?php echo $pedido['cantidad_items']; ?> producto(s)</td>
                                            <td><strong><?php echo formato_precio($pedido['total']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo obtener_color_estado($pedido['estado']); ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>pedidos/detalle.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-outline-success">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No tienes pedidos aún. 
                                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php">Comenzar a comprar</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- TAB: DIRECCIONES -->
            <?php elseif ($tab_activa === 'direcciones'): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt"></i> Mis Direcciones
                                </h5>
                            </div>

                            <div class="card-body">
                                <?php if (!empty($direcciones)): ?>
                                    <div class="row">
                                        <?php foreach ($direcciones as $dir): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <i class="fas fa-home"></i> <?php echo htmlspecialchars($dir['nombre']); ?>
                                                        </h6>
                                                        <p class="card-text small mb-3">
                                                            <?php echo htmlspecialchars($dir['calle']); ?> <?php echo $dir['numero']; ?>
                                                            <?php echo !empty($dir['piso']) ? ', ' . htmlspecialchars($dir['piso']) : ''; ?><br>
                                                            <?php echo htmlspecialchars($dir['codigo_postal']); ?> 
                                                            <?php echo htmlspecialchars($dir['ciudad']); ?>, 
                                                            <?php echo htmlspecialchars($dir['provincia']); ?>
                                                        </p>
                                                        <form method="POST" action="perfil.php?tab=direcciones" style="display: inline;">
                                                            <input type="hidden" name="accion" value="eliminar_direccion">
                                                            <input type="hidden" name="direccion_id" value="<?php echo $dir['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar esta dirección?')">
                                                                <i class="fas fa-trash"></i> Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4">
                                        No tienes direcciones guardadas
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-plus"></i> Nueva Dirección
                                </h6>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="perfil.php?tab=direcciones">
                                    <input type="hidden" name="accion" value="agregar_direccion">

                                    <div class="mb-2">
                                        <label for="nombre" class="form-label small fw-bold">Nombre *</label>
                                        <input type="text" class="form-control form-control-sm" name="nombre" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="calle" class="form-label small fw-bold">Calle *</label>
                                        <input type="text" class="form-control form-control-sm" name="calle" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <label for="numero" class="form-label small fw-bold">Número *</label>
                                            <input type="text" class="form-control form-control-sm" name="numero" required>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label for="piso" class="form-label small fw-bold">Piso</label>
                                            <input type="text" class="form-control form-control-sm" name="piso">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label for="ciudad" class="form-label small fw-bold">Ciudad *</label>
                                        <input type="text" class="form-control form-control-sm" name="ciudad" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="provincia" class="form-label small fw-bold">Provincia *</label>
                                        <input type="text" class="form-control form-control-sm" name="provincia" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="codigo_postal" class="form-label small fw-bold">Código Postal *</label>
                                        <input type="text" class="form-control form-control-sm" name="codigo_postal" required>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- TAB: FAVORITOS -->
            <?php elseif ($tab_activa === 'favoritos'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-heart"></i> Mis Favoritos
                        </h5>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($favoritos)): ?>
                            <div class="row">
                                <?php foreach ($favoritos as $producto): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="position-relative overflow-hidden" style="height: 200px;">
                                                <img 
                                                    src="<?php echo SITE_URL . htmlspecialchars($producto['imagen_principal']); ?>" 
                                                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                    class="card-img-top w-100 h-100"
                                                    style="object-fit: cover;"
                                                >
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h6 class="card-title">
                                                    <?php echo htmlspecialchars(truncar_texto($producto['nombre'], 40)); ?>
                                                </h6>
                                                <p class="h6 text-success mb-3">
                                                    <?php echo formato_precio($producto['precio_descuento'] ?? $producto['precio']); ?>
                                                </p>
                                                <div class="mt-auto">
                                                    <a 
                                                        href="<?php echo SITE_URL; ?>productos/detalle.php?id=<?php echo $producto['id']; ?>"
                                                        class="btn btn-sm btn-outline-success w-100"
                                                    >
                                                        Ver Producto
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    No tienes productos favoritos aún. 
                                    <a href="<?php echo SITE_URL; ?>productos/catalogo.php">Explora nuestro catálogo</a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <!-- TAB: SEGURIDAD -->
            <?php elseif ($tab_activa === 'seguridad'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lock"></i> Cambiar Contraseña
                        </h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="perfil.php?tab=seguridad">
                            <input type="hidden" name="accion" value="cambiar_password">

                            <div class="mb-3">
                                <label for="password_actual" class="form-label fw-bold">Contraseña Actual</label>
                                <div class="input-group input-group-lg">
                                    <input 
                                        type="password" 
                                        class="form-control"
                                        id="password_actual" 
                                        name="password_actual"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_actual')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_nueva" class="form-label fw-bold">Nueva Contraseña</label>
                                <div class="input-group input-group-lg">
                                    <input 
                                        type="password" 
                                        class="form-control"
                                        id="password_nueva" 
                                        name="password_nueva"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_nueva')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números
                                </small>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmar" class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                                <div class="input-group input-group-lg">
                                    <input 
                                        type="password" 
                                        class="form-control"
                                        id="password_confirmar" 
                                        name="password_confirmar"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmar')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-key"></i> Cambiar Contraseña
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt"></i> Seguridad de la Cuenta
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i> 
                                <strong>Contraseña segura:</strong> Última actualización hace más de 1 año
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i> 
                                <strong>Verificación de Email:</strong> Tu email ha sido verificado
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i> 
                                <strong>Encriptación SSL:</strong> Todas tus transacciones están protegidas
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Cerrar alertas automáticamente
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>

<?php
// Funciones auxiliares
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
