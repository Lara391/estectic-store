<?php
/**
 * =====================================================
 * RECUPERAR CONTRASEÑA
 * =====================================================
 * Archivo: usuarios/recuperar_contrasena.php
 * 
 * Sistema de recuperación de contraseña por email
 * =====================================================
 */

$titulo_pagina = "Recuperar Contraseña";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/db/funciones_bd.php';

// Si ya está autenticado, redirigir al perfil
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . 'usuarios/perfil.php');
    exit;
}

$paso = $_GET['paso'] ?? 1;
$email = $_POST['email'] ?? '';
$token = $_GET['token'] ?? '';
$errores = [];
$exito = '';

// PASO 1: Solicitar email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso == 1) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errores[] = 'Por favor ingresa tu email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no es válido.';
    } else {
        // Verificar que el email existe
        $usuario = obtener_usuario_por_email($email);
        
        if ($usuario) {
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Guardar token en la base de datos
            if (guardar_token_recuperacion($usuario['id'], $token, $fecha_expiracion)) {
                // Enviar email con enlace de recuperación
                $enlace_recuperacion = SITE_URL . 'usuarios/recuperar_contrasena.php?paso=2&token=' . $token;
                
                $asunto = 'Recupera tu contraseña - ' . NOMBRE_SITIO;
                $mensaje = "
                    <h2>Recuperación de Contraseña</h2>
                    <p>Hola " . htmlspecialchars($usuario['nombre']) . ",</p>
                    <p>Hemos recibido una solicitud para recuperar tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
                    <p>
                        <a href='" . $enlace_recuperacion . "' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Recuperar Contraseña
                        </a>
                    </p>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p>" . $enlace_recuperacion . "</p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste este cambio, ignora este email.</p>
                    <p>Saludos,<br>" . NOMBRE_SITIO . "</p>
                ";

                if (enviar_email($email, $asunto, $mensaje)) {
                    $exito = 'Se ha enviado un enlace de recuperación a tu email. Por favor revisa tu bandeja de entrada.';
                    $_SERVER['REQUEST_METHOD'] = 'GET'; // Cambiar método para mostrar mensaje
                } else {
                    $errores[] = 'Error al enviar el email. Por favor intenta más tarde.';
                }
            } else {
                $errores[] = 'Error al procesar tu solicitud. Por favor intenta más tarde.';
            }
        } else {
            // No mostrar que el email no existe por seguridad
            $exito = 'Si este email está registrado, recibirás un enlace de recuperación en breve.';
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
    }
}

// PASO 2: Validar token y cambiar contraseña
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso == 2) {
    $token = $_POST['token'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';

    if (empty($token)) {
        $errores[] = 'Token inválido o expirado.';
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
        // Validar token
        $token_valido = validar_token_recuperacion($token);

        if ($token_valido && $token_valido['usuario_id']) {
            // Actualizar contraseña
            $actualizado = actualizar_usuario($token_valido['usuario_id'], [
                'password' => password_hash($password_nueva, PASSWORD_BCRYPT)
            ]);

            if ($actualizado) {
                // Eliminar token
                eliminar_token_recuperacion($token);
                
                $exito = 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión con tu nueva contraseña.';
                $paso = 3; // Mostrar confirmación
            } else {
                $errores[] = 'Error al actualizar la contraseña. Por favor intenta más tarde.';
            }
        } else {
            $errores[] = 'El enlace de recuperación es inválido o ha expirado. Por favor solicita uno nuevo.';
        }
    }
}

// Validar token si es paso 2
if ($paso == 2 && !empty($token)) {
    $token_valido = validar_token_recuperacion($token);
    if (!$token_valido) {
        $errores[] = 'El enlace de recuperación es inválido o ha expirado.';
        $paso = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - <?php echo NOMBRE_SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .recuperar-container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0 !important;
            padding: 30px 20px;
            text-align: center;
        }
        .card-header h3 {
            margin: 0;
            font-size: 1.8rem;
        }
        .card-header i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-recuperar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .btn-recuperar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-recuperar:active {
            transform: translateY(0);
        }
        .progress-paso {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        .paso-item {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .paso-numero {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #495057;
        }
        .paso-numero.activo {
            background: #667eea;
            color: white;
        }
        .paso-numero.completado {
            background: #28a745;
            color: white;
        }
        .paso-linea {
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #e9ecef;
        }
        .paso-linea.activo {
            background: #667eea;
        }
        .enlace-secundario {
            color: #667eea;
            text-decoration: none;
            transition: color 0.2s;
        }
        .enlace-secundario:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        .confirmacion-exito {
            text-align: center;
            padding: 30px 20px;
        }
        .confirmacion-exito i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .mensaje-seguridad {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="recuperar-container">
        <!-- Alertas -->
        <?php if ($exito && $paso !== 3): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Errores encontrados:</h6>
                <ul class="mb-0 ms-3">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Card principal -->
        <div class="card">
            <!-- Encabezado -->
            <div class="card-header text-white">
                <?php if ($paso == 1): ?>
                    <i class="fas fa-lock"></i>
                    <h3>Recuperar Contraseña</h3>
                    <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Paso 1 de 2</p>
                <?php elseif ($paso == 2): ?>
                    <i class="fas fa-key"></i>
                    <h3>Nueva Contraseña</h3>
                    <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Paso 2 de 2</p>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i>
                    <h3>¡Éxito!</h3>
                    <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Contraseña actualizada</p>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <!-- PASO 1: Solicitar email -->
                <?php if ($paso == 1): ?>
                    <div class="progress-paso">
                        <div class="paso-item">
                            <div class="paso-numero activo">1</div>
                            <small>Verificar Email</small>
                        </div>
                        <div class="paso-item">
                            <div class="paso-numero">2</div>
                            <div class="paso-linea"></div>
                            <small>Nueva Contraseña</small>
                        </div>
                    </div>

                    <p class="text-muted text-center mb-4">
                        Ingresa tu email para recibir un enlace de recuperación
                    </p>

                    <form method="POST" action="recuperar_contrasena.php?paso=1">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg"
                                id="email" 
                                name="email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                placeholder="tu@email.com"
                                required
                                autofocus
                            >
                            <small class="form-text text-muted">
                                Usa el email con el que creaste tu cuenta
                            </small>
                        </div>

                        <button type="submit" class="btn btn-recuperar btn-lg w-100 text-white mb-3">
                            <i class="fas fa-envelope"></i> Enviar Enlace de Recuperación
                        </button>
                    </form>

                    <div class="mensaje-seguridad">
                        <i class="fas fa-shield-alt"></i> <strong>Seguridad:</strong>
                        Solo recibirás el enlace si el email está registrado en nuestra plataforma.
                    </div>

                    <hr class="my-4">

                    <p class="text-center text-muted">
                        <a href="<?php echo SITE_URL; ?>usuarios/login.php" class="enlace-secundario">
                            <i class="fas fa-arrow-left"></i> Volver a Iniciar Sesión
                        </a>
                    </p>

                <?php elseif ($paso == 2): ?>
                    <div class="progress-paso">
                        <div class="paso-item">
                            <div class="paso-numero completado">
                                <i class="fas fa-check" style="margin: 0;"></i>
                            </div>
                            <small>Verificar Email</small>
                        </div>
                        <div class="paso-item">
                            <div class="paso-numero activo">2</div>
                            <div class="paso-linea activo"></div>
                            <small>Nueva Contraseña</small>
                        </div>
                    </div>

                    <p class="text-muted text-center mb-4">
                        Crea una nueva contraseña segura
                    </p>

                    <form method="POST" action="recuperar_contrasena.php?paso=2">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="mb-3">
                            <label for="password_nueva" class="form-label fw-bold">Nueva Contraseña</label>
                            <div class="input-group input-group-lg">
                                <input 
                                    type="password" 
                                    class="form-control"
                                    id="password_nueva" 
                                    name="password_nueva"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                    autofocus
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_nueva')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> 
                                Usa una combinación de mayúsculas, minúsculas, números y símbolos
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmar" class="form-label fw-bold">Confirmar Contraseña</label>
                            <div class="input-group input-group-lg">
                                <input 
                                    type="password" 
                                    class="form-control"
                                    id="password_confirmar" 
                                    name="password_confirmar"
                                    placeholder="Repite la contraseña"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmar')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-recuperar btn-lg w-100 text-white mb-3">
                            <i class="fas fa-key"></i> Actualizar Contraseña
                        </button>
                    </form>

                    <div class="mensaje-seguridad">
                        <i class="fas fa-shield-alt"></i> <strong>Requisitos:</strong>
                        <ul class="mb-0 mt-2 ms-2">
                            <li>Mínimo 8 caracteres</li>
                            <li>Incluir mayúsculas y minúsculas</li>
                            <li>Incluir números y símbolos si es posible</li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- PASO 3: Confirmación de éxito -->
                    <div class="confirmacion-exito">
                        <i class="fas fa-check-circle"></i>
                        <h4 class="mb-3">¡Contraseña Actualizada!</h4>
                        <p class="text-muted mb-4">
                            Tu contraseña ha sido cambiada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.
                        </p>
                        
                        <a href="<?php echo SITE_URL; ?>usuarios/login.php" class="btn btn-recuperar btn-lg text-white w-100">
                            <i class="fas fa-sign-in-alt"></i> Ir a Iniciar Sesión
                        </a>

                        <div class="mensaje-seguridad mt-4">
                            <i class="fas fa-lightbulb"></i> <strong>Consejo:</strong>
                            Guarda tu nueva contraseña en un lugar seguro y no la compartas con nadie.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pie de página -->
            <div class="card-footer bg-light text-center py-3">
                <p class="mb-0 text-muted small">
                    <i class="fas fa-copyright"></i> 
                    <?php echo date('Y'); ?> <?php echo NOMBRE_SITIO; ?>. Todos los derechos reservados.
                </p>
            </div>
        </div>

        <!-- Enlaces adicionales -->
        <?php if ($paso !== 3): ?>
            <div class="text-center mt-4">
                <p class="text-white">
                    ¿No tienes cuenta? 
                    <a href="<?php echo SITE_URL; ?>usuarios/registro.php" class="enlace-secundario" style="color: #fff; text-decoration: underline;">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = event.target.closest('button').querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Cerrar alertas automáticamente
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 6000);
        });

        // Validación en tiempo real de contraseñas
        const passwordNueva = document.getElementById('password_nueva');
        const passwordConfirmar = document.getElementById('password_confirmar');

        if (passwordNueva && passwordConfirmar) {
            passwordConfirmar.addEventListener('input', function() {
                if (this.value !== passwordNueva.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    </script>
</body>
</html>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
