<?php
/**
 * =====================================================
 * HEADER DEL SITIO
 * =====================================================
 * Archivo: includes/header.php
 * 
 * Encabezado común para todas las páginas
 * =====================================================
 */

// Iniciar sesión y funciones
require_once dirname(__DIR__) . '/includes/funciones.php';
iniciar_sesion();

// Obtener datos del usuario actual si está autenticado
$usuario_actual = obtener_datos_usuario_actual();
$cantidad_carrito = obtener_cantidad_carrito();
$mensajes = obtener_mensajes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Estetic Store - Tienda de productos de estética y belleza">
    <meta name="keywords" content="cremas, skincare, belleza, estética, productos">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?>Estetic Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    
    <?php if (isset($estilos_adicionales)): ?>
        <?php foreach ($estilos_adicionales as $archivo): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL . $archivo; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <!-- Logo -->
            <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-spa text-success"></i> Estetic Store
            </a>
            
            <!-- Toggle para móvil -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menú -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>productos/catalogo.php">Catálogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>#promociones">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>#contacto">Contacto</a>
                    </li>
                </ul>
            </div>
            
            <!-- Carrito y Usuario -->
            <div class="d-flex align-items-center gap-3 ms-3">
                <!-- Carrito -->
                <a href="<?php echo SITE_URL; ?>carrito/carrito.php" class="position-relative text-decoration-none text-dark">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <?php if ($cantidad_carrito > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cantidad_carrito; ?>
                        </span>
                    <?php endif; ?>
                </a>
                
                <!-- Usuario -->
                <?php if ($usuario_actual): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $usuario_actual['nombre']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>usuarios/perfil.php">
                                    <i class="fas fa-user-circle"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>pedidos/mis_pedidos.php">
                                    <i class="fas fa-shopping-bag"></i> Mis Pedidos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>usuarios/favoritos.php">
                                    <i class="fas fa-heart"></i> Favoritos
                                </a>
                            </li>
                            <?php if ($usuario_actual['rol'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/dashboard.php">
                                        <i class="fas fa-cog"></i> Panel Admin
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>usuarios/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>usuarios/login.php" class="btn btn-sm btn-success">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="<?php echo SITE_URL; ?>usuarios/registro.php" class="btn btn-sm btn-outline-success">
                        Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mensajes de alerta -->
    <?php if (!empty($mensajes)): ?>
        <div class="container mt-3">
            <?php foreach ($mensajes as $msg): ?>
                <?php 
                    $clase_alerta = [
                        'success' => 'alert-success',
                        'error' => 'alert-danger',
                        'warning' => 'alert-warning',
                        'info' => 'alert-info'
                    ][$msg['tipo']] ?? 'alert-info';
                ?>
                <div class="alert <?php echo $clase_alerta; ?> alert-dismissible fade show" role="alert">
                    <?php echo $msg['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Contenedor principal -->
    <main class="py-4">
