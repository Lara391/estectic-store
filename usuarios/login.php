<?php
/**
 * =====================================================
 * PÁGINA DE LOGIN
 * =====================================================
 * Archivo: usuarios/login.php
 * 
 * Autenticación de usuarios
 * =====================================================
 */

$titulo_pagina = "Iniciar Sesión";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Si ya está autenticado, redirigir
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/perfil.php');
    exit;
}

// Variables para el formulario
$email = '';
$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $recuerdame = isset($_POST['recuerdame']);

    // Validaciones
    if (empty($email)) {
        $error = 'El correo electrónico es requerido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (empty($password)) {
        $error = 'La contraseña es requerida.';
    } else {
        // Buscar usuario
        $usuario = obtener_usuario_por_email($email);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Login exitoso
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Recordarme (30 días)
            if ($recuerdame) {
                setcookie(
                    'usuario_recordado',
                    $usuario['id'],
                    time() + (30 * 24 * 60 * 60),
                    '/',
                    '',
                    false,
                    true
                );
            }

            // Registrar último acceso
            actualizar_ultimo_acceso_usuario($usuario['id']);

            // Redirigir
            $destino = $_GET['destino'] ?? 'perfil.php';
            header('Location: ' . SITE_URL . 'usuarios/' . $destino);
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

// Mensaje de sesión cerrada
if (isset($_GET['cerrado'])) {
    $exito = 'Has cerrado sesión correctamente.';
}
?>

<div class="min-vh-100 d-flex align-items-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <!-- Card de Login -->
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-spa"></i> Estetic Store
                        </h2>
                        <p class="mb-0 mt-2 text-white-50">Iniciar Sesión</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Mensajes -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($exito): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form method="POST" action="login.php" novalidate>
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">
                                    <i class="fas fa-envelope"></i> Correo Electrónico
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg"
                                    id="email" 
                                    name="email"
                                    value="<?php echo htmlspecialchars($email); ?>"
                                    placeholder="tu@correo.com"
                                    required
                                    autofocus
                                >
                                <small class="form-text text-muted">
                                    Usamos tu email para acceder a tu cuenta
                                </small>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-lock"></i> Contraseña
                                </label>
                                <div class="input-group input-group-lg">
                                    <input 
                                        type="password" 
                                        class="form-control"
                                        id="password" 
                                        name="password"
                                        placeholder="••••••••"
                                        required
                                    >
                                    <button 
                                        class="btn btn-outline-secondary" 
                                        type="button"
                                        id="btn-toggle-password"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Introduce la contraseña de tu cuenta
                                </small>
                            </div>

                            <!-- Recordarme y Olvidé contraseña -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="recuerdame"
                                        name="recuerdame"
                                    >
                                    <label class="form-check-label" for="recuerdame">
                                        Recuérdame
                                    </label>
                                </div>
                                <a href="<?php echo SITE_URL; ?>usuarios/recuperar_contrasena.php" class="text-success text-decoration-none">
                                    ¿Olvidé mi contraseña?
                                </a>
                            </div>

                            <!-- Botón de Login -->
                            <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </button>
                        </form>

                        <!-- Divisor -->
                        <div class="position-relative mb-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted">o</span>
                        </div>

                        <!-- Redes Sociales (opcional) -->
                        <div class="d-grid gap-2 mb-4">
                            <button class="btn btn-outline-primary btn-lg" type="button">
                                <i class="fab fa-facebook"></i> Facebook
                            </button>
                            <button class="btn btn-outline-danger btn-lg" type="button">
                                <i class="fab fa-google"></i> Google
                            </button>
                        </div>

                        <!-- Registro -->
                        <div class="text-center pt-3 border-top">
                            <p class="mb-0">
                                ¿No tienes cuenta?
                                <a href="<?php echo SITE_URL; ?>usuarios/registro.php" class="fw-bold text-success text-decoration-none">
                                    Registrate aquí
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="mt-4">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle"></i> ¿Por qué crear una cuenta?
                        </h6>
                        <ul class="mb-0 ms-3">
                            <li>Acceso rápido a tu historial de compras</li>
                            <li>Guarda tus direcciones de envío</li>
                            <li>Recibe ofertas y promociones exclusivas</li>
                            <li>Administra tus favoritos</li>
                            <li>Mejor experiencia de compra</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
// Mostrar/ocultar contraseña
document.getElementById('btn-toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Cerrar alertas automáticamente después de 5 segundos
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>
