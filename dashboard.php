<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/controllers/ReporteController.php';

$reporteCtrl = new ReporteController();
$resumen = $reporteCtrl->obtenerResumenDashboard();

$tituloPagina = 'Panel de Control';
$seccionActiva = 'dashboard';
$cssExtra = 'dashboard.css';
$jsExtra = ['reportes.js'];

require_once __DIR__ . '/vistas/layout/header.php';
require_once __DIR__ . '/vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Panel de Control</h2>
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
            <div class="stats-grid">
                <div class="stat-card stat-card--azul">
                    <div class="stat-card-contenido">
                        <span class="stat-card-numero"><?php echo $resumen['total_vehiculos']; ?></span>
                        <span class="stat-card-etiqueta">Vehiculos Registrados</span>
                    </div>
                    <div class="stat-card-icono">
                        <i class="fas fa-car"></i>
                    </div>
                </div>

                <div class="stat-card stat-card--naranja">
                    <div class="stat-card-contenido">
                        <span class="stat-card-numero"><?php echo $resumen['autos_en_taller']; ?></span>
                        <span class="stat-card-etiqueta">Autos en Taller</span>
                    </div>
                    <div class="stat-card-icono">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <div class="stat-card stat-card--verde">
                    <div class="stat-card-contenido">
                        <span class="stat-card-numero"><?php echo $resumen['completados_hoy']; ?></span>
                        <span class="stat-card-etiqueta">Mantenimientos Completados Hoy</span>
                    </div>
                    <div class="stat-card-icono">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>

                <div class="stat-card stat-card--morado">
                    <div class="stat-card-contenido">
                        <span class="stat-card-numero"><?php echo $resumen['cubiculos_libres']; ?></span>
                        <span class="stat-card-etiqueta">Cubiculos Disponibles</span>
                    </div>
                    <div class="stat-card-icono">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
            </div>

            <div class="acciones-rapidas">
                <a href="<?php echo $baseUrl; ?>/vehiculos/registro.php" class="accion-rapida">
                    <div class="accion-rapida-icono azul">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <span class="accion-rapida-texto">Registrar Entrada</span>
                </a>
                <a href="<?php echo $baseUrl; ?>/cubiculos/panel.php" class="accion-rapida">
                    <div class="accion-rapida-icono verde">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <span class="accion-rapida-texto">Ver Taller</span>
                </a>
                <a href="<?php echo $baseUrl; ?>/mantenimientos/formulario.php" class="accion-rapida">
                    <div class="accion-rapida-icono naranja">
                        <i class="fas fa-tools"></i>
                    </div>
                    <span class="accion-rapida-texto">Nuevo Mantenimiento</span>
                </a>
                <a href="<?php echo $baseUrl; ?>/reportes/informe.php" class="accion-rapida">
                    <div class="accion-rapida-icono azul">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <span class="accion-rapida-texto">Generar Informe</span>
                </a>
            </div>

            <div class="dashboard-grid">
                <div class="card panel-cubiculos-resumen">
                    <div class="card-header">
                        <h3><i class="fas fa-warehouse"></i> Estado de Cubiculos</h3>
                        <span class="badge-vivo">En vivo</span>
                    </div>
                    <div class="card-body">
                        <div class="cubiculos-grid-mini">
                            <?php foreach ($resumen['cubiculos'] as $cub): ?>
                                <div class="cubiculo-mini <?php echo $cub['estado'] === 'ocupado' ? 'ocupado' : 'libre'; ?>">
                                    <span class="cubiculo-mini-num"><?php echo str_pad((string)$cub['numero'], 2, '0', STR_PAD_LEFT); ?></span>
                                    <?php if ($cub['estado'] === 'ocupado'): ?>
                                        <i class="fas fa-car cubiculo-mini-auto"></i>
                                        <span class="cubiculo-mini-placa"><?php echo htmlspecialchars($cub['placa'] ?? ''); ?></span>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle cubiculo-mini-libre"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Mantenimientos (30 dias)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-grid">
                            <div class="chart-container">
                                <canvas id="chartMantenimientos"></canvas>
                            </div>
                            <div class="chart-container">
                                <canvas id="chartDonut"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Ultimas Entradas al Taller</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($resumen['ultimas_entradas'])): ?>
                        <div class="sin-datos">
                            <i class="fas fa-car-side" style="font-size:2rem;opacity:0.3;margin-bottom:1rem;display:block;"></i>
                            No hay registros de entrada recientes.
                        </div>
                    <?php else: ?>
                        <div class="tabla-responsive">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Marca / Modelo</th>
                                        <th>Cubiculo</th>
                                        <th>Fecha Entrada</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resumen['ultimas_entradas'] as $entrada): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($entrada['placa']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($entrada['marca'] . ' ' . $entrada['modelo']); ?></td>
                                            <td><?php echo htmlspecialchars($entrada['cubiculo_nombre']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($entrada['fecha_entrada'])); ?></td>
                                            <td>
                                                <?php if ($entrada['estado'] === 'activo'): ?>
                                                    <span class="badge badge-activo">En taller</span>
                                                <?php elseif ($entrada['estado'] === 'completado'): ?>
                                                    <span class="badge badge-completado">Completado</span>
                                                <?php else: ?>
                                                    <span class="badge badge-cancelado">Cancelado</span>
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
        </div>
    </main>

    <script>
        const datosMantenimientos = <?php echo json_encode($resumen['resumen_mantenimientos']); ?>;
    </script>
<?php require_once __DIR__ . '/vistas/layout/footer.php'; ?>
