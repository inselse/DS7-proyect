<?php

$baseUrl = '/DS7-proyect';

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../controllers/VehiculoController.php';
require_once __DIR__ . '/../clases/Cubiculo.php';

$vehiculoCtrl = new VehiculoController();
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $vehiculoCtrl->procesarRegistro($_POST);

    if ($resultado['exito']) {
        header('Location: ' . $baseUrl . '/cubiculos/panel.php?registro_exitoso=1');
        exit;
    }
}

$cubiculos = Cubiculo::listarTodos();
$csrfToken = AuthController::generarCSRF();

$tituloPagina = 'Registrar Vehiculo';
$seccionActiva = 'registro-vehiculo';
$cssExtra = 'forms.css';

require_once __DIR__ . '/../vistas/layout/header.php';
require_once __DIR__ . '/../vistas/layout/sidebar.php';
?>
    <main class="contenido-principal">
        <header class="header-superior">
            <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Alternar menu">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="header-titulo">Registrar Vehiculo</h2>
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
            <?php if (!empty($vehiculoCtrl->getErrores())): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul>
                        <?php foreach ($vehiculoCtrl->getErrores() as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div id="alertaAjax"></div>

            <form method="POST" action="<?php echo $baseUrl; ?>/vehiculos/registro.php" id="formRegistroVehiculo" novalidate class="form-wizard">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="wizard-steps">
                    <div class="wizard-step active" data-step="1">
                        <div class="wizard-step-circle">1</div>
                        <span class="wizard-step-label">Vehiculo</span>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-circle">2</div>
                        <span class="wizard-step-label">Propietario</span>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-circle">3</div>
                        <span class="wizard-step-label">Cubiculo + Servicios</span>
                    </div>
                </div>

                <div class="wizard-slide active" data-step="1">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-car"></i> Datos del Vehiculo</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="campo glow-input">
                                    <label for="placa">Placa *</label>
                                    <input
                                        type="text"
                                        id="placa"
                                        name="placa"
                                        value="<?php echo htmlspecialchars($_POST['placa'] ?? ''); ?>"
                                        placeholder="ABC-123"
                                        maxlength="20"
                                        required
                                    >
                                </div>

                                <div class="campo glow-input">
                                    <label for="marca">Marca *</label>
                                    <select id="marca" name="marca" required>
                                        <option value="">Seleccione una marca</option>
                                        <?php
                                        $marcas = ['Toyota', 'Chevrolet', 'Ford', 'Honda', 'Nissan', 'Hyundai', 'Kia', 'Volkswagen', 'Mazda', 'Suzuki', 'Renault', 'Peugeot', 'Fiat', 'Otro'];
                                        $marcaSel = $_POST['marca'] ?? '';
                                        foreach ($marcas as $m): ?>
                                            <option value="<?php echo $m; ?>" <?php echo $marcaSel === $m ? 'selected' : ''; ?>>
                                                <?php echo $m; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="campo glow-input">
                                    <label for="modelo">Modelo *</label>
                                    <input
                                        type="text"
                                        id="modelo"
                                        name="modelo"
                                        value="<?php echo htmlspecialchars($_POST['modelo'] ?? ''); ?>"
                                        placeholder="Corolla, Civic, etc."
                                        required
                                    >
                                </div>

                                <div class="campo glow-input">
                                    <label for="anio">Anio *</label>
                                    <input
                                        type="number"
                                        id="anio"
                                        name="anio"
                                        value="<?php echo htmlspecialchars($_POST['anio'] ?? ''); ?>"
                                        min="1970"
                                        max="<?php echo date('Y'); ?>"
                                        placeholder="2020"
                                        required
                                    >
                                </div>

                                <div class="campo campo-color glow-input">
                                    <label for="color">Color *</label>
                                    <div class="color-picker-wrapper">
                                        <input
                                            type="color"
                                            id="color"
                                            name="color"
                                            value="<?php echo htmlspecialchars($_POST['color'] ?? '#333333'); ?>"
                                        >
                                        <input
                                            type="text"
                                            id="colorTexto"
                                            value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>"
                                            placeholder="Azul, Rojo, etc."
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard-slide" data-step="2">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Datos del Propietario</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="campo glow-input">
                                    <label for="dueno_nombre">Nombre completo *</label>
                                    <input
                                        type="text"
                                        id="dueno_nombre"
                                        name="dueno_nombre"
                                        value="<?php echo htmlspecialchars($_POST['dueno_nombre'] ?? ''); ?>"
                                        placeholder="Juan Perez"
                                        required
                                    >
                                </div>

                                <div class="campo glow-input">
                                    <label for="dueno_telefono">Telefono</label>
                                    <input
                                        type="tel"
                                        id="dueno_telefono"
                                        name="dueno_telefono"
                                        value="<?php echo htmlspecialchars($_POST['dueno_telefono'] ?? ''); ?>"
                                        placeholder="555-0100"
                                    >
                                </div>

                                <div class="campo glow-input">
                                    <label for="dueno_email">Correo electronico</label>
                                    <input
                                        type="email"
                                        id="dueno_email"
                                        name="dueno_email"
                                        value="<?php echo htmlspecialchars($_POST['dueno_email'] ?? ''); ?>"
                                        placeholder="correo@ejemplo.com"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard-slide" data-step="3">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-warehouse"></i> Asignacion de Cubiculo</h3>
                        </div>
                        <div class="card-body">
                            <p class="campo-ayuda">Seleccione un cubiculo disponible para asignar al vehiculo.</p>
                            <div class="cubiculos-selector" id="cubiculosSelector">
                                <?php foreach ($cubiculos as $cub): ?>
                                    <div
                                        class="cubiculo-opcion <?php echo $cub->estaOcupado() ? 'ocupado' : 'libre'; ?>"
                                        data-id="<?php echo $cub->getId(); ?>"
                                        data-disponible="<?php echo $cub->estaOcupado() ? 'false' : 'true'; ?>"
                                        onclick="seleccionarCubiculo(this)"
                                    >
                                        <span class="cubiculo-opcion-numero"><?php echo htmlspecialchars($cub->getNombre()); ?></span>
                                        <?php if ($cub->estaOcupado()): ?>
                                            <i class="fas fa-car"></i>
                                            <span class="cubiculo-opcion-estado">Ocupado</span>
                                        <?php else: ?>
                                            <i class="fas fa-check-circle"></i>
                                            <span class="cubiculo-opcion-estado">Disponible</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="cubiculo_id" id="cubiculoSeleccionado" value="">
                            <p id="cubiculoMensaje" class="campo-ayuda" style="margin-top: 0.75rem; color: var(--color-primario);"></p>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 1rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-tools"></i> Mantenimientos Iniciales</h3>
                        </div>
                        <div class="card-body">
                            <p class="campo-ayuda">Seleccione los servicios a realizar (opcional al ingreso).</p>
                            <div class="mantenimientos-check-grid">
                                <?php foreach (Mantenimiento::tiposDisponibles() as $tipo => $etiqueta): ?>
                                    <label class="mantenimiento-card">
                                        <input
                                            type="checkbox"
                                            name="mantenimientos[]"
                                            value="<?php echo $tipo; ?>"
                                            data-tipo="<?php echo $tipo; ?>"
                                            onchange="toggleMantenimiento(this)"
                                        >
                                        <div class="mantenimiento-card-content">
                                            <span class="mantenimiento-card-icono">
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
                                            <span class="mantenimiento-card-titulo"><?php echo $etiqueta; ?></span>
                                        </div>
                                        <div class="mantenimiento-card-costo" id="costo_<?php echo $tipo; ?>" style="display: none;">
                                            <input
                                                type="number"
                                                name="costo_<?php echo $tipo; ?>"
                                                placeholder="Costo estimado"
                                                min="0"
                                                step="0.01"
                                                class="input-costo"
                                            >
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div id="otroDescripcion" class="campo" style="display: none; margin-top: 1rem;">
                                <label for="otro_descripcion">Describa el servicio requerido:</label>
                                <textarea
                                    id="otro_descripcion"
                                    name="otro_descripcion"
                                    rows="3"
                                    placeholder="Describa detalladamente el mantenimiento a realizar..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard-buttons">
                    <button type="button" class="btn btn-secundario" id="wizardPrev" style="visibility: hidden;">
                        <i class="fas fa-arrow-left"></i>
                        Anterior
                    </button>
                    <button type="button" class="btn btn-primario" id="wizardNext">
                        Siguiente
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-primario btn-lg" id="wizardSubmit" style="display: none;">
                        <i class="fas fa-save"></i>
                        Registrar Entrada
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function mostrarAlerta(tipo, contenido) {
            var icono = tipo === 'advertencia' ? 'fa-exclamation-triangle' :
                        tipo === 'exito' ? 'fa-check-circle' : 'fa-info-circle';
            document.getElementById('alertaAjax').innerHTML =
                '<div class="alerta alerta-' + tipo + '"><i class="fas ' + icono + '"></i> ' + contenido + '</div>';
        }

        function limpiarAlerta() {
            document.getElementById('alertaAjax').innerHTML = '';
        }

        function toggleMantenimiento(checkbox) {
            const tipo = checkbox.dataset.tipo;
            const costDiv = document.getElementById('costo_' + tipo);
            if (costDiv) {
                costDiv.style.display = checkbox.checked ? 'block' : 'none';
            }
            const otroDesc = document.getElementById('otroDescripcion');
            const otroCheck = document.querySelector('input[value="otro"]');
            if (otroDesc && otroCheck) {
                otroDesc.style.display = otroCheck.checked ? 'block' : 'none';
            }
        }

        function seleccionarCubiculo(elemento) {
            if (elemento.dataset.disponible !== 'true') return;
            document.querySelectorAll('.cubiculo-opcion').forEach(el => {
                el.classList.remove('seleccionado');
            });
            elemento.classList.add('seleccionado');
            document.getElementById('cubiculoSeleccionado').value = elemento.dataset.id;
            document.getElementById('cubiculoMensaje').textContent =
                'Cubiculo ' + elemento.querySelector('.cubiculo-opcion-numero').textContent + ' seleccionado.';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var pasoActual = 1;
            var totalPasos = 3;
            var verificando = false;

            function actualizarWizard(paso) {
                document.querySelectorAll('.wizard-slide').forEach(function (s) {
                    s.classList.remove('active');
                });
                document.querySelectorAll('.wizard-step').forEach(function (s) {
                    s.classList.remove('active');
                    s.classList.remove('completed');
                });

                document.querySelector('.wizard-slide[data-step="' + paso + '"]').classList.add('active');

                for (var i = 1; i <= totalPasos; i++) {
                    var step = document.querySelector('.wizard-step[data-step="' + i + '"]');
                    if (i < paso) step.classList.add('completed');
                    else if (i === paso) step.classList.add('active');
                }

                document.getElementById('wizardPrev').style.visibility = paso === 1 ? 'hidden' : 'visible';
                document.getElementById('wizardNext').style.display = paso === totalPasos ? 'none' : '';
                document.getElementById('wizardSubmit').style.display = paso === totalPasos ? '' : 'none';
            }

            function avanzarPaso() {
                if (pasoActual < totalPasos) {
                    pasoActual++;
                    actualizarWizard(pasoActual);
                }
            }

            function llenarDatosVehiculo(v) {
                if (v.marca) {
                    document.getElementById('marca').value = v.marca;
                }
                if (v.modelo) {
                    document.getElementById('modelo').value = v.modelo;
                }
                if (v.anio) {
                    document.getElementById('anio').value = v.anio;
                }
                if (v.color) {
                    document.getElementById('color').value = v.color;
                    document.getElementById('colorTexto').value = v.color;
                }
                if (v.dueno_nombre) {
                    document.getElementById('dueno_nombre').value = v.dueno_nombre;
                }
                if (v.dueno_telefono) {
                    document.getElementById('dueno_telefono').value = v.dueno_telefono;
                }
                if (v.dueno_email) {
                    document.getElementById('dueno_email').value = v.dueno_email;
                }
            }

            document.getElementById('wizardNext').addEventListener('click', function () {
                if (verificando) return;

                var slide = document.querySelector('.wizard-slide.active');
                var inputs = slide.querySelectorAll('[required]');
                var valido = true;
                inputs.forEach(function (inp) {
                    if (!inp.checkValidity()) {
                        inp.reportValidity();
                        valido = false;
                    }
                });
                if (!valido) return;
                if (pasoActual >= totalPasos) return;

                if (pasoActual === 1) {
                    verificando = true;
                    var placa = document.getElementById('placa').value.trim();

                    if (placa === '') {
                        verificando = false;
                        avanzarPaso();
                        return;
                    }

                    var formData = new FormData();
                    formData.append('placa', placa);

                    fetch('<?php echo $baseUrl; ?>/vehiculos/verificar_placa.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.encontrado && data.vehiculo) {
                            if (data.registro_activo) {
                                mostrarAlerta('advertencia',
                                    'Este vehiculo (<strong>' + placa + '</strong>) ya se encuentra en el taller desde el <strong>' +
                                    data.registro_activo.fecha_entrada +
                                    '</strong>. Por favor use la <a href="<?php echo $baseUrl; ?>/cubiculos/panel.php" style="font-weight:600;text-decoration:underline;">pantalla de Cubiculos</a> para gestionar su estado.'
                                );
                            } else {
                                mostrarAlerta('info',
                                    'El vehiculo con placa <strong>' + placa + '</strong> ya existe. Los datos se han cargado automaticamente.'
                                );
                            }
                            llenarDatosVehiculo(data.vehiculo);
                        } else {
                            limpiarAlerta();
                        }
                        verificando = false;
                        avanzarPaso();
                    })
                    .catch(function () {
                        verificando = false;
                        avanzarPaso();
                    });
                } else {
                    avanzarPaso();
                }
            });

            document.getElementById('wizardPrev').addEventListener('click', function () {
                if (pasoActual > 1) {
                    pasoActual--;
                    actualizarWizard(pasoActual);
                }
            });

            actualizarWizard(1);
        });
    </script>
<?php require_once __DIR__ . '/../vistas/layout/footer.php'; ?>
