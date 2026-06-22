<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../controllers/ReporteController.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';

$reporteCtrl = new ReporteController();
$datosReporte = null;

$generar = isset($_GET['generar']) && $_GET['generar'] === '1';

if ($generar) {
    $filtros = [
        'desde'  => $_GET['desde'] ?? date('Y-m-01'),
        'hasta'  => $_GET['hasta'] ?? date('Y-m-d'),
        'tipos'  => isset($_GET['tipos']) ? (array)$_GET['tipos'] : [],
        'estado' => $_GET['estado'] ?? '',
    ];

    $datosReporte = $reporteCtrl->generar($filtros);
}

$tiposMantenimiento = Mantenimiento::tiposDisponibles();

$tituloPagina = 'Informe de Mantenimientos';
$seccionActiva = 'reportes';
$cssExtra = 'reportes.css';
$jsExtra = ['reportes.js'];

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Informe de Mantenimientos</h2>
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
                    <h3><i class="fas fa-filter"></i> Filtros del Informe</h3>
                </div>
                <div class="card-body">
                        <form method="GET" action="<?php echo $baseUrl; ?>/reportes/informe.php" id="formReporte" class="form-reporte">
                        <input type="hidden" name="generar" value="1">

                        <div class="form-grid form-reporte-grid">
                            <div class="campo">
                                <label for="desde">Fecha desde</label>
                                <input
                                    type="date"
                                    id="desde"
                                    name="desde"
                                    value="<?php echo htmlspecialchars($_GET['desde'] ?? date('Y-m-01')); ?>"
                                    required
                                >
                            </div>

                            <div class="campo">
                                <label for="hasta">Fecha hasta</label>
                                <input
                                    type="date"
                                    id="hasta"
                                    name="hasta"
                                    value="<?php echo htmlspecialchars($_GET['hasta'] ?? date('Y-m-d')); ?>"
                                    required
                                >
                            </div>

                            <div class="campo">
                                <label for="estado">Estado</label>
                                <select id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="activos" <?php echo ($_GET['estado'] ?? '') === 'activos' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="completados" <?php echo ($_GET['estado'] ?? '') === 'completados' ? 'selected' : ''; ?>>Completados</option>
                                </select>
                            </div>
                        </div>

                        <div class="campo" style="margin-top: 1rem;">
                            <label>Tipos de mantenimiento</label>
                            <div class="filtro-checkboxes">
                                <?php foreach ($tiposMantenimiento as $tipo => $etiqueta): ?>
                                    <label class="filtro-checkbox">
                                        <input
                                            type="checkbox"
                                            name="tipos[]"
                                            value="<?php echo $tipo; ?>"
                                            <?php echo (isset($_GET['tipos']) && in_array($tipo, (array)$_GET['tipos'], true)) ? 'checked' : ''; ?>
                                        >
                                        <span class="chip-mantenimiento chip-<?php echo str_replace('_', '-', $tipo); ?>">
                                            <?php echo $etiqueta; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-acciones" style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primario">
                                <i class="fas fa-search"></i>
                                Generar Informe
                            </button>
                            <?php if ($datosReporte !== null): ?>
                                <button type="button" class="btn btn-secundario" onclick="window.print()">
                                    <i class="fas fa-print"></i>
                                    Imprimir / PDF
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($datosReporte !== null): ?>
                <div class="card" style="margin-top: 1.5rem;" id="reporteResultados">
                    <div class="print-header print-only">
                        <h2>(nombre) — Informe de Mantenimientos</h2>
                        <p>
                            Periodo: <?php echo date('d/m/Y', strtotime($datosReporte['desde'])); ?>
                            al <?php echo date('d/m/Y', strtotime($datosReporte['hasta'])); ?>
                            &nbsp;|&nbsp; Generado: <?php echo date('d/m/Y H:i'); ?>
                        </p>
                    </div>
                    <div class="card-header reporte-encabezado">
                        <div class="reporte-titulo-imprimir">
                            <h3><i class="fas fa-file-alt"></i> Informe de Mantenimientos</h3>
                            <p class="reporte-periodo">
                                Periodo: <?php echo date('d/m/Y', strtotime($datosReporte['desde'])); ?>
                                al <?php echo date('d/m/Y', strtotime($datosReporte['hasta'])); ?>
                            </p>
                        </div>
                        <span class="reporte-fecha-generacion">
                            Generado: <?php echo date('d/m/Y H:i'); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datosReporte['registros'])): ?>
                            <p class="sin-datos">No se encontraron registros para el periodo seleccionado.</p>
                        <?php else: ?>
                            <div class="tabla-responsive">
                                <table class="tabla tabla-informe">
                                    <thead>
                                        <tr>
                                            <th>Placa</th>
                                            <th>Vehiculo</th>
                                            <th>Dueno</th>
                                            <th>Cubiculo</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Mantenimientos</th>
                                            <th>Costo Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datosReporte['registros'] as $reg): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($reg['placa']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($reg['marca'] . ' ' . $reg['modelo']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['dueno_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['cubiculo_nombre']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($reg['fecha_entrada'])); ?></td>
                                                <td><?php echo $reg['fecha_salida'] ? date('d/m/Y H:i', strtotime($reg['fecha_salida'])) : '-'; ?></td>
                                                <td class="celda-mantenimientos">
                                                    <?php foreach ($reg['mantenimientos'] as $m): ?>
                                                        <span class="chip-mantenimiento chip-<?php echo str_replace('_', '-', $m['tipo']); ?>">
                                                            <?php echo htmlspecialchars($tiposMantenimiento[$m['tipo']] ?? $m['tipo']); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td class="celda-costo">$<?php echo number_format($reg['costo_total'], 2); ?></td>
                                                <td>
                                                    <?php if ($reg['registro_estado'] === 'activo'): ?>
                                                        <span class="badge badge-activo">Activo</span>
                                                    <?php elseif ($reg['registro_estado'] === 'completado'): ?>
                                                        <span class="badge badge-completado">Completado</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-cancelado">Cancelado</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fila-totales">
                                            <td colspan="6" class="text-right">
                                                <strong>Totales:</strong>
                                                <?php echo $datosReporte['total_vehiculos']; ?> vehiculos,
                                                <?php echo $datosReporte['total_mantenimientos']; ?> mantenimientos
                                            </td>
                                            <td></td>
                                            <td class="celda-costo">
                                                <strong>$<?php echo number_format($datosReporte['total_costo'], 2); ?></strong>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
