<?php
/**
 * =====================================================
 * PÁGINA DE REGISTRO
 * =====================================================
 * Archivo: usuarios/registro.php
 * 
 * Registro de nuevos usuarios
 * =====================================================
 */

$titulo_pagina = "Registrarse";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Si ya está autenticado, redirigir
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/perfil.php');
    exit;
}

// Variables
$nombre = '';
$email = '';
$telefono = '';
$errores = [];
$exito = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    $terminos = isset($_POST['terminos']);

    // Validaciones
    if (empty($nombre)) {
        $errores[] = 'El nombre es requerido.';
    } elseif (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if (empty($email)) {
        $errores[] = 'El correo electrónico es requerido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    } elseif (usuario_existe($email)) {
        $errores[] = 'Este correo ya está registrado.';
    }

    if (!empty($telefono) && !preg_match('/^[0-9\-\+\s\(\)]{7,}$/', $telefono)) {
        $errores[] = 'El teléfono no es válido.';
    }

    if (empty($password)) {
        $errores[] = 'La contraseña es requerida.';
    } elseif (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errores[] = 'La contraseña debe contener mayúsculas, minúsculas y números.';
    }

    if ($password !== $password_confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$terminos) {
        $errores[] = 'Debes aceptar los términos y condiciones.';
    }

    // Si no hay errores, crear usuario
    if (empty($errores)) {
        $usuario_id = crear_usuario([
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'rol' => 'cliente'
        ]);

        if ($usuario_id) {
            $exito = true;
            // Limpiar formulario
            $nombre = '';
            $email = '';
            $telefono = '';
        } else {
            $errores[] = 'Error al crear la cuenta. Intenta nuevamente.';
        }
    }
}
?>

<div class="min-vh-100 d-flex align-items-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Card de Registro -->
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-spa"></i> Estetic Store
                        </h2>
                        <p class="mb-0 mt-2 text-white-50">Crear Nueva Cuenta</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Mensaje de Éxito -->
                        <?php if ($exito): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="mb-3 text-center">
                                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                                </div>
                                <h5 class="alert-heading text-center">¡Bienvenido!</h5>
                                <p class="text-center mb-3">
                                    Tu cuenta ha sido creada correctamente. 
                                    Ahora puedes iniciar sesión con tu correo electrónico.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo SITE_URL; ?>usuarios/login.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Ir a Iniciar Sesión
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-success btn-lg">
                                        <i class="fas fa-home"></i> Volver al Inicio
                                    </a>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php else: ?>
                            <!-- Mensajes de Error -->
                            <?php if (!empty($errores)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-exclamation-circle"></i> Errores en el formulario:
                                    </h6>
                                    <ul class="mb-0 ms-3">
                                        <?php foreach ($errores as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario -->
                            <form method="POST" action="registro.php" novalidate>
                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">
                                        <i class="fas fa-user"></i> Nombre Completo
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg"
                                        id="nombre" 
                                        name="nombre"
                                        value="<?php echo htmlspecialchars($nombre); ?>"
                                        placeholder="Juan Pérez"
                                        required
                                        autofocus
                                    >
                                    <small class="form-text text-muted">
                                        Mínimo 3 caracteres
                                    </small>
                                </div>

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
                                    >
                                    <small class="form-text text-muted">
                                        Usarás este email para iniciar sesión
                                    </small>
                                </div>

                                <!-- Teléfono -->
                                <div class="mb-3">
                                    <label for="telefono" class="form-label fw-bold">
                                        <i class="fas fa-phone"></i> Teléfono (Opcional)
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control form-control-lg"
                                        id="telefono" 
                                        name="telefono"
                                        value="<?php echo htmlspecialchars($telefono); ?>"
                                        placeholder="+34 123 456 789"
                                    >
                                    <small class="form-text text-muted">
                                        Lo usaremos para contactarte sobre tus pedidos
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
                                    <small class="form-text text-muted d-block">
                                        Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números
                                    </small>
                                    <div id="password-strength" class="mt-2"></div>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="mb-3">
                                    <label for="password_confirmar" class="form-label fw-bold">
                                        <i class="fas fa-lock"></i> Confirmar Contraseña
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input 
                                            type="password" 
                                            class="form-control"
                                            id="password_confirmar" 
                                            name="password_confirmar"
                                            placeholder="••••••••"
                                            required
                                        >
                                        <button 
                                            class="btn btn-outline-secondary" 
                                            type="button"
                                            id="btn-toggle-password-confirm"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match" class="mt-2"></div>
                                </div>

                                <!-- Términos y Condiciones -->
                                <div class="form-check mb-4 p-3 bg-light rounded">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="terminos"
                                        name="terminos"
                                        required
                                    >
                                    <label class="form-check-label" for="terminos">
                                        Acepto los 
                                        <a href="<?php echo SITE_URL; ?>info/terminos.php" target="_blank" class="text-success">
                                            términos y condiciones
                                        </a>
                                        y la 
                                        <a href="<?php echo SITE_URL; ?>info/privacidad.php" target="_blank" class="text-success">
                                            política de privacidad
                                        </a>
                                    </label>
                                </div>

                                <!-- Botón de Registro -->
                                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="fas fa-user-plus"></i> Crear Cuenta
                                </button>
                            </form>

                            <!-- Divisor -->
                            <div class="position-relative mb-4">
                                <hr>
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted">o</span>
                            </div>

                            <!-- Login -->
                            <div class="text-center pt-3 border-top">
                                <p class="mb-0">
                                    ¿Ya tienes cuenta?
                                    <a href="<?php echo SITE_URL; ?>usuarios/login.php" class="fw-bold text-success text-decoration-none">
                                        Inicia sesión aquí
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información adicional -->
                <?php if (!$exito): ?>
                    <div class="mt-4">
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-shield-alt"></i> Tu información está segura
                            </h6>
                            <ul class="mb-0 ms-3">
                                <li>Usamos encriptación SSL de 256 bits</li>
                                <li>Tus datos no se comparten con terceros</li>
                                <li>Puedes cambiar tu contraseña en cualquier momento</li>
                                <li>Tienes control total sobre tu cuenta</li>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                <?php endif; ?>
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

document.getElementById('btn-toggle-password-confirm').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirmar');
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

// Validador de fortaleza de contraseña
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('password-strength');
    let strength = 0;
    let mensaje = '';
    let clase = '';

    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;

    if (strength < 3) {
        mensaje = '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Contraseña débil</small>';
        clase = 'bg-danger';
    } else if (strength < 5) {
        mensaje = '<small class="text-warning"><i class="fas fa-info-circle"></i> Contraseña normal</small>';
        clase = 'bg-warning';
    } else {
        mensaje = '<small class="text-success"><i class="fas fa-check-circle"></i> Contraseña fuerte</small>';
        clase = 'bg-success';
    }

    strengthDiv.innerHTML = mensaje;
});

// Validador de coincidencia de contraseñas
document.getElementById('password_confirmar').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    const matchDiv = document.getElementById('password-match');

    if (passwordConfirm === '') {
        matchDiv.innerHTML = '';
    } else if (password === passwordConfirm) {
        matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Las contraseñas coinciden</small>';
    } else {
        matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle"></i> Las contraseñas no coinciden</small>';
    }
});

// Cerrar alertas automáticamente después de 7 segundos
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 7000);
});
</script>
