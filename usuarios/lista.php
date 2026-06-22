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
$mensajeExito = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    if (!AuthController::verificarCSRF($_POST['csrf_token'] ?? '')) {
        $mensajeError = 'Token de seguridad invalido.';
    } else {
        $id = (int)($_POST['usuario_id'] ?? 0);
        if ($usuarioCtrl->eliminar($id)) {
            $mensajeExito = 'Usuario eliminado correctamente.';
        } else {
            $errores = $usuarioCtrl->getErrores();
            $mensajeError = !empty($errores) ? implode(' ', $errores) : 'Error al eliminar el usuario.';
        }
    }
}

$usuarios = $usuarioCtrl->listar();
$csrfToken = AuthController::generarCSRF();

$tituloPagina = 'Usuarios';
$seccionActiva = 'usuarios';
$cssExtra = 'dashboard.css';

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Gestion de Usuarios</h2>
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
            <?php if ($mensajeExito): ?>
                <div class="alerta alerta-exito">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($mensajeExito); ?>
                </div>
            <?php endif; ?>

            <?php if ($mensajeError): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($mensajeError); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Listado de Usuarios</h3>
                    <div class="card-acciones">
                        <a href="<?php echo $baseUrl; ?>/usuarios/formulario.php" class="btn btn-primario btn-sm">
                            <i class="fas fa-plus"></i>
                            Nuevo Usuario
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($usuarios)): ?>
                        <p class="sin-datos">No hay usuarios registrados.</p>
                    <?php else: ?>
                        <div class="tabla-responsive">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php $esAdminPrincipal = $u->getEmail() === 'admin@taller.com'; ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($u->getNombre()); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($u->getEmail()); ?></td>
                                            <td>
                                                <span class="badge <?php echo $u->getRol() === 'admin' ? 'badge-pendiente' : 'badge-proceso'; ?>">
                                                    <?php echo $u->getRol() === 'admin' ? 'Administrador' : 'Mecanico'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($u->estaActivo()): ?>
                                                    <span class="badge badge-completado">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-cancelado">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $u->getCreadoEn() ? date('d/m/Y', strtotime($u->getCreadoEn())) : '-'; ?>
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:0.5rem;">
                                                    <a href="<?php echo $baseUrl; ?>/usuarios/formulario.php?id=<?php echo $u->getId(); ?>" class="btn btn-secundario btn-xs">
                                                        <i class="fas fa-edit"></i>
                                                        Editar
                                                    </a>
                                                    <?php if ($esAdminPrincipal): ?>
                                                        <span class="btn btn-xs" style="background:var(--color-fondo-main);color:var(--color-texto-secundario);cursor:default;border:1px solid var(--color-borde);">
                                                            <i class="fas fa-shield-alt"></i>
                                                            Protegido
                                                        </span>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-peligro btn-xs" onclick="confirmarEliminar(<?php echo $u->getId(); ?>, '<?php echo htmlspecialchars($u->getNombre(), ENT_QUOTES); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                            Eliminar
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modalEliminar" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Confirmar Eliminacion</h3>
                <button type="button" class="modal-cerrar" onclick="cerrarModalEliminar()">&times;</button>
            </div>
            <form method="POST" action="<?php echo $baseUrl; ?>/usuarios/lista.php" class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="usuario_id" id="eliminarUsuarioId" value="">

                <div class="alerta alerta-advertencia">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta a punto de eliminar al usuario <strong id="eliminarUsuarioNombre"></strong>.
                    Esta accion no se puede deshacer.
                </div>

                <div class="modal-acciones">
                    <button type="submit" class="btn btn-peligro">
                        <i class="fas fa-trash"></i>
                        Eliminar Usuario
                    </button>
                    <button type="button" class="btn btn-secundario" onclick="cerrarModalEliminar()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmarEliminar(id, nombre) {
            document.getElementById('eliminarUsuarioId').value = id;
            document.getElementById('eliminarUsuarioNombre').textContent = nombre;
            document.getElementById('modalEliminar').style.display = 'flex';
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').style.display = 'none';
        }

        document.getElementById('modalEliminar').addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarModalEliminar();
            }
        });
    </script>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
