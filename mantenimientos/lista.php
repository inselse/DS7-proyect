<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';

$registrosActivos = Registro::listarActivos();

$registroSeleccionado = isset($_GET['registro_id']) ? (int)$_GET['registro_id'] : 0;

$mantenimientos = [];
$vehiculoInfo = null;

if ($registroSeleccionado > 0) {
    $mantenimientos = Mantenimiento::listarPorRegistro($registroSeleccionado);

    foreach ($registrosActivos as $r) {
        if ((int)$r['id'] === $registroSeleccionado) {
            $vehiculoInfo = $r;
            break;
        }
    }
}

$tituloPagina = 'Mantenimientos';
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
            <h2 class="header-titulo">Mantenimientos</h2>
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
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Registros Activos en Taller</h3>
                    <div class="card-acciones">
                        <a href="<?php echo $baseUrl; ?>/mantenimientos/formulario.php" class="btn btn-primario btn-sm">
                            <i class="fas fa-plus"></i>
                            Nuevo Mantenimiento
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($registrosActivos)): ?>
                        <p class="sin-datos">No hay vehiculos actualmente en el taller.</p>
                    <?php else: ?>
                        <div class="tabla-responsive">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Vehiculo</th>
                                        <th>Dueno</th>
                                        <th>Cubiculo</th>
                                        <th>Ingreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrosActivos as $reg): ?>
                                        <tr class="<?php echo $registroSeleccionado === (int)$reg['id'] ? 'fila-seleccionada' : ''; ?>">
                                            <td><strong><?php echo htmlspecialchars($reg['placa']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($reg['marca'] . ' ' . $reg['modelo']); ?></td>
                                            <td><?php echo htmlspecialchars($reg['dueno_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($reg['cubiculo_nombre']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($reg['fecha_entrada'])); ?></td>
                                            <td>
                                                <a href="<?php echo $baseUrl; ?>/mantenimientos/lista.php?registro_id=<?php echo $reg['id']; ?>" class="btn btn-primario btn-xs">
                                                    <i class="fas fa-eye"></i> Ver Mantenimientos
                                                </a>
                                                <a href="<?php echo $baseUrl; ?>/mantenimientos/formulario.php?registro_id=<?php echo $reg['id']; ?>" class="btn btn-exito btn-xs">
                                                    <i class="fas fa-plus"></i> Agregar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($vehiculoInfo !== null): ?>
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-tools"></i>
                            Mantenimientos de <?php echo htmlspecialchars($vehiculoInfo['placa']); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mantenimientos)): ?>
                            <p class="sin-datos">Este vehiculo no tiene mantenimientos registrados.</p>
                        <?php else: ?>
                            <div class="tabla-responsive">
                                <table class="tabla">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Descripcion</th>
                                            <th>Costo</th>
                                            <th>Mecanico</th>
                                            <th>Estado</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mantenimientos as $m): ?>
                                            <tr>
                                                <td>
                                                    <span class="chip-mantenimiento chip-<?php echo str_replace('_', '-', $m['tipo']); ?>">
                                                        <?php echo htmlspecialchars(Mantenimiento::tiposDisponibles()[$m['tipo']] ?? $m['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($m['descripcion']); ?></td>
                                                <td>$<?php echo number_format((float)$m['costo'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($m['mecanico_nombre'] ?? 'Sin asignar'); ?></td>
                                                <td>
                                                    <?php if ($m['estado'] === 'pendiente'): ?>
                                                        <span class="badge badge-pendiente">Pendiente</span>
                                                    <?php elseif ($m['estado'] === 'en_proceso'): ?>
                                                        <span class="badge badge-proceso">En proceso</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-completado">Completado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $m['fecha_inicio'] ? date('d/m/Y H:i', strtotime($m['fecha_inicio'])) : '-'; ?></td>
                                                <td><?php echo $m['fecha_fin'] ? date('d/m/Y H:i', strtotime($m['fecha_fin'])) : '-'; ?></td>
                                                <td>
                                                    <?php if (in_array($_SESSION['rol'], ['admin', 'mecanico'], true)): ?>
                                                        <form method="POST" action="<?php echo $baseUrl; ?>/mantenimientos/actualizar_estado.php" style="display:inline;">
                                                            <input type="hidden" name="csrf_token" value="<?php echo AuthController::generarCSRF(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                                            <input type="hidden" name="registro_id" value="<?php echo $registroSeleccionado; ?>">
                                                            <select name="estado" onchange="this.form.submit()" class="select-estado">
                                                                <option value="pendiente" <?php echo $m['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                                <option value="en_proceso" <?php echo $m['estado'] === 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                                                <option value="completado" <?php echo $m['estado'] === 'completado' ? 'selected' : ''; ?>>Completado</option>
                                                            </select>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
