<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../clases/Vehiculo.php';
require_once __DIR__ . '/../clases/Registro.php';

$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 10;
$busqueda = trim($_GET['buscar'] ?? '');

$vehiculos = $busqueda !== '' ? Vehiculo::buscar($busqueda) : Vehiculo::listarTodos();

$total = count($vehiculos);
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;
$vehiculosPagina = array_slice($vehiculos, $offset, $porPagina);

$tituloPagina = 'Vehiculos';
$seccionActiva = 'lista-vehiculos';
$cssExtra = 'dashboard.css';

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Vehiculos Registrados</h2>
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
                    <h3><i class="fas fa-truck"></i> Listado de Vehiculos</h3>
                    <div class="card-acciones">
                        <a href="<?php echo $baseUrl; ?>/vehiculos/registro.php" class="btn btn-primario btn-sm">
                            <i class="fas fa-plus"></i>
                            Nuevo Vehiculo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="buscador">
                        <i class="fas fa-search buscador-icono"></i>
                        <input
                            type="text"
                            id="buscarVehiculo"
                            placeholder="Buscar por placa, marca, modelo o dueno..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            class="buscador-input"
                        >
                        <?php if ($busqueda !== ''): ?>
                            <a href="<?php echo $baseUrl; ?>/vehiculos/lista.php" class="buscador-limpiar">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($vehiculos)): ?>
                        <p class="sin-datos">No se encontraron vehiculos.</p>
                    <?php else: ?>
                        <div class="tabla-responsive">
                            <table class="tabla">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Anio</th>
                                        <th>Color</th>
                                        <th>Dueno</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculosPagina as $v): ?>
                                        <?php $tieneActivo = $v->tieneRegistroActivo(); ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($v->getPlaca()); ?></strong></td>
                                            <td><?php echo htmlspecialchars($v->getMarca()); ?></td>
                                            <td><?php echo htmlspecialchars($v->getModelo()); ?></td>
                                            <td><?php echo $v->getAnio(); ?></td>
                                            <td>
                                                <span class="chip-color" style="background-color: <?php echo htmlspecialchars($v->getColor()); ?>;"></span>
                                                <?php echo htmlspecialchars($v->getColor()); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($v->getDuenoNombre()); ?></td>
                                            <td>
                                                <?php if ($tieneActivo): ?>
                                                    <span class="badge badge-activo">En taller</span>
                                                <?php else: ?>
                                                    <span class="badge badge-completado">Fuera del taller</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="acciones">
                                                <a href="<?php echo $baseUrl; ?>/cubiculos/panel.php?placa=<?php echo urlencode($v->getPlaca()); ?>" class="btn-accion" title="Nueva entrada">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </a>
                                                <button type="button" class="btn-accion" title="Ver historial" onclick="verHistorial(<?php echo $v->getId(); ?>)">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPaginas > 1): ?>
                            <div class="paginacion">
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <a
                                        href="<?php echo $baseUrl; ?>/vehiculos/lista.php?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>"
                                        class="pagina <?php echo $i === $pagina ? 'activa' : ''; ?>"
                                    >
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        const busquedaInput = document.getElementById('buscarVehiculo');

        if (busquedaInput) {
            let timeoutBusqueda = null;

            busquedaInput.addEventListener('input', function () {
                clearTimeout(timeoutBusqueda);
                timeoutBusqueda = setTimeout(() => {
                    const termino = this.value.trim();
                    if (termino.length >= 2 || termino.length === 0) {
                        window.location.href = '<?php echo $baseUrl; ?>/vehiculos/lista.php?buscar=' + encodeURIComponent(termino);
                    }
                }, 400);
            });
        }

        function verHistorial(vehiculoId) {
            window.location.href = '<?php echo $baseUrl; ?>/reportes/informe.php?vehiculo_id=' + vehiculoId;
        }
    </script>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
