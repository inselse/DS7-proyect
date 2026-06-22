<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../controllers/CubiculoController.php';
require_once __DIR__ . '/../clases/Vehiculo.php';

$cubiculoCtrl = new CubiculoController();
$mensajeExito = '';
$mensajeError = '';

if (isset($_GET['registro_exitoso'])) {
    $mensajeExito = 'Vehiculo registrado y asignado al cubiculo correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'asignar') {
        $asignado = $cubiculoCtrl->asignar($_POST);

        if ($asignado) {
            $mensajeExito = 'Vehiculo asignado al cubiculo exitosamente.';
        } else {
            $mensajesError = $cubiculoCtrl->getErrores();
        }
    } elseif ($accion === 'liberar') {
        $liberado = $cubiculoCtrl->liberar($_POST);

        if ($liberado) {
            $mensajeExito = 'Cubiculo liberado y salida registrada correctamente.';
        } else {
            $mensajesError = $cubiculoCtrl->getErrores();
        }
    }
}

$cubiculos = $cubiculoCtrl->obtenerEstado();
$csrfToken = AuthController::generarCSRF();

$placaPreseleccionada = $_GET['placa'] ?? '';

$tituloPagina = 'Cubiculos';
$seccionActiva = 'cubiculos';
$cssExtra = 'cubiculos.css';
$jsExtra = ['cubiculos.js'];

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Panel de Cubiculos</h2>
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
            <?php if ($mensajeExito): ?>
                <div class="alerta alerta-exito">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($mensajeExito); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($cubiculoCtrl->getErrores())): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul>
                        <?php foreach ($cubiculoCtrl->getErrores() as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="taller-grid" id="tallerGrid">
                <?php foreach ($cubiculos as $cub): ?>
                    <div class="cubiculo-card <?php echo $cub['estado'] === 'ocupado' ? 'ocupado' : 'libre'; ?>" data-id="<?php echo $cub['cubiculo_id']; ?>">
                        <div class="cubiculo-card-header">
                            <span class="cubiculo-numero">
                                <?php echo htmlspecialchars($cub['nombre']); ?>
                            </span>
                            <span class="cubiculo-badge <?php echo $cub['estado']; ?>">
                                <?php echo $cub['estado'] === 'ocupado' ? 'OCUPADO' : 'DISPONIBLE'; ?>
                            </span>
                        </div>

                        <div class="cubiculo-card-body">
                            <?php if ($cub['estado'] === 'ocupado'): ?>
                                <div class="cubiculo-auto-svg" style="--color-vehiculo: <?php echo htmlspecialchars($cub['color'] ?? '#6B7280'); ?>;">
                                    <?php include __DIR__ . '/../assets/img/auto_ocupado.svg'; ?>
                                </div>
                                <div class="cubiculo-info-rapida">
                                    <span class="cubiculo-placa"><?php echo htmlspecialchars($cub['placa'] ?? ''); ?></span>
                                    <span class="cubiculo-dueno"><?php echo htmlspecialchars($cub['dueno_nombre'] ?? ''); ?></span>
                                    <?php if (!empty($cub['fecha_entrada'])): ?>
                                        <?php
                                        $entrada = new DateTime($cub['fecha_entrada']);
                                        $ahora = new DateTime();
                                        $diff = $entrada->diff($ahora);
                                        $horas = $diff->h + ($diff->days * 24);
                                        $minutos = $diff->i;
                                        $totalMinutos = ($horas * 60) + $minutos;
                                        $maxMinutos = 8 * 60;
                                        $porcentaje = min(100, round(($totalMinutos / $maxMinutos) * 100));
                                        ?>
                                        <span class="cubiculo-tiempo">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $horas; ?>h <?php echo $minutos; ?>m
                                        </span>
                                        <div class="cubiculo-progreso">
                                            <div class="cubiculo-progreso-bar">
                                                <div class="cubiculo-progreso-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                            </div>
                                            <div class="cubiculo-progreso-label">
                                                <span>Ingreso</span>
                                                <span>8h</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="cubiculo-hover-info">
                                    <div class="cubiculo-hover-item">
                                        <span class="cubiculo-hover-label">Marca:</span>
                                        <span><?php echo htmlspecialchars($cub['marca'] ?? ''); ?></span>
                                    </div>
                                    <div class="cubiculo-hover-item">
                                        <span class="cubiculo-hover-label">Modelo:</span>
                                        <span><?php echo htmlspecialchars($cub['modelo'] ?? ''); ?></span>
                                    </div>
                                    <div class="cubiculo-hover-item">
                                        <span class="cubiculo-hover-label">Ingreso:</span>
                                        <span><?php echo date('d/m/Y H:i', strtotime($cub['fecha_entrada'] ?? '')); ?></span>
                                    </div>
                                    <?php if (!empty($cub['mantenimientos'])): ?>
                                        <div class="cubiculo-hover-item">
                                            <span class="cubiculo-hover-label">Servicios:</span>
                                            <span class="cubiculo-mantenimientos">
                                                <?php
                                                $tipos = explode(', ', $cub['mantenimientos']);
                                                foreach ($tipos as $t): ?>
                                                    <span class="chip-mantenimiento chip-<?php echo str_replace('_', '-', $t); ?>">
                                                        <?php echo htmlspecialchars(Mantenimiento::tiposDisponibles()[$t] ?? $t); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="cubiculo-vacio-svg">
                                    <svg viewBox="0 0 120 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="30" width="100" height="35" rx="6" stroke="#4B5563" stroke-width="1.5" stroke-dasharray="4 3" fill="none"/>
                                        <rect x="30" y="15" width="60" height="20" rx="4" stroke="#4B5563" stroke-width="1.5" stroke-dasharray="4 3" fill="none"/>
                                        <circle cx="30" cy="62" r="6" stroke="#4B5563" stroke-width="1.5" stroke-dasharray="3 2" fill="none"/>
                                        <circle cx="90" cy="62" r="6" stroke="#4B5563" stroke-width="1.5" stroke-dasharray="3 2" fill="none"/>
                                        <rect x="25" y="22" width="70" height="8" rx="3" stroke="#4B5563" stroke-width="1" stroke-dasharray="3 2" fill="none"/>
                                    </svg>
                                </div>
                                <div class="cubiculo-info-rapida">
                                    <span class="cubiculo-disponible-texto">Espacio disponible</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="cubiculo-card-footer">
                            <?php if ($cub['estado'] === 'ocupado'): ?>
                                <button type="button" class="btn btn-peligro btn-sm" onclick="abrirModalLiberar(<?php echo $cub['cubiculo_id']; ?>, '<?php echo htmlspecialchars($cub['placa'] ?? ''); ?>')">
                                    <i class="fas fa-arrow-right"></i>
                                    Registrar Salida
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primario btn-sm" onclick="abrirModalAsignar(<?php echo $cub['cubiculo_id']; ?>, '<?php echo htmlspecialchars($cub['nombre']); ?>')">
                                    <i class="fas fa-plus"></i>
                                    Asignar Vehiculo
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modalAsignar" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Asignar Vehiculo</h3>
                <button type="button" class="modal-cerrar" onclick="cerrarModal('modalAsignar')">&times;</button>
            </div>
            <form method="POST" action="<?php echo $baseUrl; ?>/cubiculos/panel.php" class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="accion" value="asignar">
                <input type="hidden" name="cubiculo_id" id="asignarCubiculoId" value="">

                <div class="campo">
                    <label for="asignarCubiculoNombre">Cubiculo</label>
                    <input type="text" id="asignarCubiculoNombre" readonly class="input-readonly">
                </div>

                <div class="campo combobox-campo">
                    <label for="asignarPlaca">Vehiculo *</label>
                    <div class="combobox-wrapper">
                        <input
                            type="text"
                            id="asignarPlaca"
                            name="placa"
                            placeholder="Buscar por placa, marca o dueno..."
                            maxlength="20"
                            required
                            value="<?php echo htmlspecialchars($placaPreseleccionada); ?>"
                            autocomplete="off"
                        >
                        <input type="hidden" name="vehiculo_id" id="asignarVehiculoId" value="">
                        <div class="combobox-dropdown" id="comboDropdown"></div>
                    </div>
                    <p class="campo-ayuda" id="comboAyuda" style="display:none;"></p>
                </div>

                <div class="modal-acciones">
                    <button type="submit" class="btn btn-primario">
                        <i class="fas fa-check"></i>
                        Asignar
                    </button>
                    <button type="button" class="btn btn-secundario" onclick="cerrarModal('modalAsignar')">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalLiberar" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Registrar Salida</h3>
                <button type="button" class="modal-cerrar" onclick="cerrarModal('modalLiberar')">&times;</button>
            </div>
            <form method="POST" action="<?php echo $baseUrl; ?>/cubiculos/panel.php" class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="accion" value="liberar">
                <input type="hidden" name="cubiculo_id" id="liberarCubiculoId" value="">

                <div class="alerta alerta-advertencia">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta a punto de registrar la salida del vehiculo <strong id="liberarPlacaTexto"></strong>.
                </div>

                <div class="campo">
                    <label for="liberarObservaciones">Observaciones de salida</label>
                    <textarea
                        id="liberarObservaciones"
                        name="observaciones"
                        rows="3"
                        placeholder="Estado final del vehiculo, servicios realizados, pendientes..."
                    ></textarea>
                </div>

                <div class="modal-acciones">
                    <button type="submit" class="btn btn-peligro">
                        <i class="fas fa-check-circle"></i>
                        Confirmar Salida
                    </button>
                    <button type="button" class="btn btn-secundario" onclick="cerrarModal('modalLiberar')">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const placaPreseleccionada = '<?php echo $placaPreseleccionada; ?>';
    </script>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
