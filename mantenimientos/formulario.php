<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../controllers/MantenimientoController.php';
require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';
require_once __DIR__ . '/../clases/Usuario.php';

$mantCtrl = new MantenimientoController();
$registrosActivos = $mantCtrl->obtenerRegistrosActivos();
$mecanicos = $mantCtrl->obtenerMecanicos();
$csrfToken = AuthController::generarCSRF();

$registroPreseleccionado = isset($_GET['registro_id']) ? (int)$_GET['registro_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guardado = $mantCtrl->guardar($_POST);

    if ($guardado) {
        header('Location: ' . $baseUrl . '/mantenimientos/lista.php?registro_id=' . (int)$_POST['registro_id'] . '&exito=1');
        exit;
    }
}

$tituloPagina = 'Nuevo Mantenimiento';
$seccionActiva = 'mantenimientos';
$cssExtra = 'dashboard.css';

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Nuevo Mantenimiento</h2>
            <div class="header-usuario">
                <span class="header-usuario-nombre">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </span>
                <span class="header-usuario-rol">
                    <?php echo $_SESSION['rol'] === 'admin' ? 'Administrador' : 'Mecanico'; ?>
                </span>
                <a href="<?php echo $baseUrl; ?>/logout.php" class="header-logout" title="Cerrar Sesion">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <div class="contenido-interno">
            <?php if (!empty($mantCtrl->getErrores())): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul>
                        <?php foreach ($mantCtrl->getErrores() as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $baseUrl; ?>/mantenimientos/formulario.php" class="card" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> Datos del Mantenimiento</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="campo">
                            <label for="registro_id">Vehiculo en taller *</label>
                            <select id="registro_id" name="registro_id" required>
                                <option value="">Seleccione un vehiculo activo</option>
                                <?php foreach ($registrosActivos as $reg): ?>
                                    <option value="<?php echo $reg['id']; ?>" <?php echo $registroPreseleccionado === (int)$reg['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($reg['placa'] . ' - ' . $reg['marca'] . ' ' . $reg['modelo'] . ' (Cub. ' . $reg['cubiculo_nombre'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="mecanico_id">Mecanico asignado</label>
                            <select id="mecanico_id" name="mecanico_id">
                                <option value="">Sin asignar</option>
                                <?php foreach ($mecanicos as $mec): ?>
                                    <option value="<?php echo $mec->getId(); ?>">
                                        <?php echo htmlspecialchars($mec->getNombre()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="campo" style="margin-top: 1rem;">
                        <label>Tipo de mantenimiento *</label>
                        <div class="radio-tarjetas">
                            <?php foreach (Mantenimiento::tiposDisponibles() as $tipo => $etiqueta): ?>
                                <label class="radio-tarjeta">
                                    <input type="radio" name="tipo" value="<?php echo $tipo; ?>" required>
                                    <div class="radio-tarjeta-contenido">
                                        <span class="radio-tarjeta-icono">
                                            <?php if ($tipo === 'cambio_aceite'): ?>
                                                <i class="fas fa-oil-can"></i>
                                            <?php elseif ($tipo === 'frenos'): ?>
                                                <i class="fas fa-brake-warning"></i>
                                            <?php elseif ($tipo === 'filtros'): ?>
                                                <i class="fas fa-filter"></i>
                                            <?php else: ?>
                                                <i class="fas fa-wrench"></i>
                                            <?php endif; ?>
                                        </span>
                                        <span class="radio-tarjeta-texto"><?php echo $etiqueta; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="campo" style="margin-top: 1rem;">
                        <label for="descripcion">Descripcion detallada *</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            placeholder="Describa el trabajo a realizar, sintomas, observaciones..."
                            required
                        ><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-grid" style="margin-top: 1rem;">
                        <div class="campo">
                            <label for="costo">Costo estimado</label>
                            <div class="input-moneda">
                                <span class="input-moneda-simbolo">$</span>
                                <input
                                    type="number"
                                    id="costo"
                                    name="costo"
                                    value="<?php echo htmlspecialchars($_POST['costo'] ?? '0'); ?>"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado inicial</label>
                            <select id="estado" name="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </div>

                    <div class="campo" style="margin-top: 1rem;">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input
                            type="datetime-local"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            value="<?php echo htmlspecialchars($_POST['fecha_inicio'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primario">
                        <i class="fas fa-save"></i>
                        Guardar Mantenimiento
                    </button>
                    <a href="<?php echo $baseUrl; ?>/mantenimientos/lista.php" class="btn btn-secundario">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
