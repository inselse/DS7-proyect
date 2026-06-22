<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ' . $baseUrl . '/dashboard.php');
    exit;
}

require_once __DIR__ . '/../controllers/UsuarioController.php';

$usuarioCtrl = new UsuarioController();
$modoEdicion = false;
$usuario = null;
$errores = [];
$exito = false;

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $usuario = Usuario::buscarPorId($id);
    if ($usuario === null) {
        header('Location: ' . $baseUrl . '/usuarios/lista.php');
        exit;
    }
    $modoEdicion = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!AuthController::verificarCSRF($_POST['csrf_token'] ?? '')) {
        $errores[] = 'Token de seguridad invalido.';
    } elseif ($modoEdicion) {
        if ($usuarioCtrl->actualizar($id, $_POST)) {
            $exito = true;
        } else {
            $errores = $usuarioCtrl->getErrores();
        }
    } else {
        if ($usuarioCtrl->crear($_POST)) {
            $exito = true;
        } else {
            $errores = $usuarioCtrl->getErrores();
        }
    }

    if ($exito) {
        header('Location: ' . $baseUrl . '/usuarios/lista.php');
        exit;
    }
}

$csrfToken = AuthController::generarCSRF();

$tituloPagina = $modoEdicion ? 'Editar Usuario' : 'Nuevo Usuario';
$seccionActiva = 'usuarios';
$cssExtra = 'forms.css';

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo"><?php echo $modoEdicion ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h2>
            <div class="header-usuario">
                <span class="header-usuario-nombre">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </span>
                <span class="header-usuario-rol">
                    Administrador
                </span>
                <a href="<?php echo $baseUrl; ?>/logout.php" class="header-logout" title="Cerrar Sesion">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <div class="contenido-interno">
            <?php if (!empty($errores)): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:600px;">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-<?php echo $modoEdicion ? 'user-edit' : 'user-plus'; ?>"></i>
                        <?php echo $modoEdicion ? 'Editar Usuario' : 'Nuevo Usuario'; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $baseUrl; ?>/usuarios/formulario.php<?php echo $modoEdicion ? '?id=' . $id : ''; ?>" id="formUsuario" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <div class="form-grid" style="grid-template-columns:1fr;">
                            <div class="campo glow-input">
                                <label for="nombre">Nombre completo *</label>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    value="<?php echo htmlspecialchars($_POST['nombre'] ?? ($usuario ? $usuario->getNombre() : '')); ?>"
                                    placeholder="Nombre del usuario"
                                    required
                                >
                            </div>

                            <div class="campo glow-input">
                                <label for="email">Correo electronico *</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ($usuario ? $usuario->getEmail() : '')); ?>"
                                    placeholder="usuario@taller.com"
                                    required
                                >
                            </div>

                            <div class="campo glow-input">
                                <label for="password">
                                    <?php echo $modoEdicion ? 'Nueva contrasena (dejar vacio para mantener)' : 'Contrasena *'; ?>
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="<?php echo $modoEdicion ? 'Nueva contrasena' : 'Minimo 6 caracteres'; ?>"
                                    <?php echo $modoEdicion ? '' : 'required'; ?>
                                    minlength="6"
                                >
                            </div>

                            <div class="campo glow-input">
                                <label for="rol">Rol *</label>
                                <select id="rol" name="rol" required>
                                    <option value="mecanico" <?php echo (($_POST['rol'] ?? ($usuario ? $usuario->getRol() : '')) === 'mecanico') ? 'selected' : ''; ?>>Mecanico</option>
                                    <option value="admin" <?php echo (($_POST['rol'] ?? ($usuario ? $usuario->getRol() : '')) === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>

                            <?php if ($modoEdicion): ?>
                                <div class="campo">
                                    <label class="filtro-checkbox" style="display:inline-flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                        <input
                                            type="checkbox"
                                            name="activo"
                                            value="1"
                                            <?php echo ($_POST['activo'] ?? ($usuario && $usuario->estaActivo() ? '1' : '')) ? 'checked' : ''; ?>
                                            style="width:1rem;height:1rem;accent-color:var(--color-primario);"
                                        >
                                        <span>Usuario activo</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions" style="margin-top:1.5rem;">
                            <button type="submit" class="btn btn-primario">
                                <i class="fas fa-save"></i>
                                <?php echo $modoEdicion ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                            </button>
                            <a href="<?php echo $baseUrl; ?>/usuarios/lista.php" class="btn btn-secundario">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
