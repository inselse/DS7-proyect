<?php

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/Database.php';
require_once 'clases/Usuario.php';
require_once 'controllers/AuthController.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();

    if (!AuthController::verificarCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad invalido. Intente nuevamente.';
    } else {
        $resultado = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');

        if ($resultado === true) {
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Credenciales incorrectas. Verifique su correo y contrasena.';
    }
}

$csrfToken = AuthController::generarCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>(nombre) - Acceso al Sistema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/DS7-proyect/assets/css/login.css">
    <link rel="stylesheet" href="/DS7-proyect/assets/css/main.css">
</head>
<body class="login-body">
    <div id="bloqueo-movil">
        <i class="fas fa-rotate-left" style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.6;"></i>
        <h2>Acceso no disponible</h2>
        <p>Este sistema solo esta disponible en tablets, laptops y equipos de escritorio.</p>
        <p style="font-size: 0.85rem; opacity: 0.6; margin-top: 1rem;">Gire su dispositivo horizontalmente o acceda desde una pantalla mas grande.</p>
    </div>

    <div class="login-orb login-orb--1"></div>
    <div class="login-orb login-orb--2"></div>
    <div class="login-orb login-orb--3"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-car-side"></i>
                </div>
                <h1>(nombre)</h1>
                <p class="login-subtitle">Sistema de Control de Taller</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="campo-login">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        <span>Correo electronico</span>
                    </label>
                    <div class="campo-input-wrapper">
                        <i class="fas fa-envelope input-icono"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="usuario@taller.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="campo-login">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        <span>Contrasena</span>
                    </label>
                    <div class="campo-input-wrapper">
                        <i class="fas fa-lock input-icono"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingrese su contrasena"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-texto">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Ingresar al Sistema
                    </span>
                </button>
            </form>

            <div class="login-footer">
                    <span data-tooltip="Solicite su acceso al administrador del sistema">
                        <i class="fas fa-headset"></i>
                        Contactar al administrador
                    </span>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('btnLogin').classList.add('cargando');
    });
    </script>

    <script src="/DS7-proyect/assets/js/main.js"></script>
</body>
</html>
