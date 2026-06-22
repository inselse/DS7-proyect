document.addEventListener('DOMContentLoaded', function () {

    if (typeof placaPreseleccionada !== 'undefined' && placaPreseleccionada) {
        const primerCubiculoLibre = document.querySelector('.cubiculo-card.libre');
        if (primerCubiculoLibre) {
            const btnAsignar = primerCubiculoLibre.querySelector('.btn-primario');
            if (btnAsignar) {
                btnAsignar.click();
            }
        }
    }

    window.abrirModalAsignar = function (cubiculoId, cubiculoNombre) {
        document.getElementById('asignarCubiculoId').value = cubiculoId;
        document.getElementById('asignarCubiculoNombre').value = cubiculoNombre;
        document.getElementById('asignarPlaca').value = '';
        document.getElementById('asignarVehiculoId').value = '';
        document.getElementById('comboAyuda').style.display = 'none';
        ocultarDropdown();

        document.getElementById('modalAsignar').style.display = 'flex';

        setTimeout(function () {
            document.getElementById('asignarPlaca').focus();
        }, 150);
    };

    window.abrirModalLiberar = function (cubiculoId, placa) {
        document.getElementById('liberarCubiculoId').value = cubiculoId;
        document.getElementById('liberarPlacaTexto').textContent = placa;
        document.getElementById('liberarObservaciones').value = '';
        document.getElementById('modalLiberar').style.display = 'flex';
    };

    window.cerrarModal = function (modalId) {
        document.getElementById(modalId).style.display = 'none';
    };

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(function (m) {
                m.style.display = 'none';
            });
        }
    });

    var placaInput = document.getElementById('asignarPlaca');
    var dropdown = document.getElementById('comboDropdown');
    var vehiculoIdHidden = document.getElementById('asignarVehiculoId');
    var comboAyuda = document.getElementById('comboAyuda');
    var timeoutBusqueda = null;
    var items = [];
    var indiceDestacado = -1;

    function ocultarDropdown() {
        if (dropdown) {
            dropdown.classList.remove('visible');
        }
        indiceDestacado = -1;
    }

    function mostrarDropdown() {
        if (dropdown && items.length > 0) {
            dropdown.classList.add('visible');
        }
    }

    function seleccionarItem(itemData) {
        placaInput.value = itemData.placa;
        vehiculoIdHidden.value = itemData.id;
        comboAyuda.textContent = itemData.marca + ' ' + itemData.modelo + ' (' + itemData.dueno_nombre + ')';
        comboAyuda.style.display = 'block';
        ocultarDropdown();
    }

    function renderizarDropdown(resultados) {
        dropdown.innerHTML = '';
        items = [];
        indiceDestacado = -1;

        if (resultados.length === 0) {
            dropdown.innerHTML = '<div class="combobox-vacio">No se encontraron vehiculos</div>';
            dropdown.classList.add('visible');
            return;
        }

        resultados.forEach(function (v, i) {
            items.push(v);
            var item = document.createElement('div');
            item.className = 'combobox-item';
            item.dataset.index = i;
            item.innerHTML =
                '<span class="combobox-item-placa">' + v.placa + '</span>' +
                '<span class="combobox-item-detalle">' + v.marca + ' ' + v.modelo + ' &middot; ' + v.dueno_nombre + '</span>';
            item.addEventListener('click', function () {
                seleccionarItem(v);
            });
            dropdown.appendChild(item);
        });

        dropdown.classList.add('visible');
    }

    if (placaInput && dropdown) {
        placaInput.addEventListener('input', function () {
            clearTimeout(timeoutBusqueda);
            var termino = this.value.trim();

            if (vehiculoIdHidden.value) {
                if (termino === '') {
                    vehiculoIdHidden.value = '';
                    comboAyuda.style.display = 'none';
                }
            }

            if (termino.length < 1) {
                ocultarDropdown();
                return;
            }

            timeoutBusqueda = setTimeout(function () {
                fetch('../api/vehiculos_buscar.php?q=' + encodeURIComponent(termino))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            renderizarDropdown(data.data);
                        } else {
                            ocultarDropdown();
                        }
                    })
                    .catch(function () {
                        ocultarDropdown();
                    });
            }, 250);
        });

        placaInput.addEventListener('keydown', function (e) {
            if (!dropdown.classList.contains('visible')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                indiceDestacado = Math.min(indiceDestacado + 1, items.length - 1);
                actualizarDestacado();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                indiceDestacado = Math.max(indiceDestacado - 1, -1);
                actualizarDestacado();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (indiceDestacado >= 0 && indiceDestacado < items.length) {
                    seleccionarItem(items[indiceDestacado]);
                }
            } else if (e.key === 'Escape') {
                ocultarDropdown();
            }
        });

        function actualizarDestacado() {
            var elementos = dropdown.querySelectorAll('.combobox-item');
            elementos.forEach(function (el, i) {
                el.classList.toggle('destacado', i === indiceDestacado);
                if (i === indiceDestacado) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        }

        placaInput.addEventListener('blur', function () {
            setTimeout(function () { ocultarDropdown(); }, 200);
        });

        placaInput.addEventListener('focus', function () {
            if (dropdown.children.length > 0 && placaInput.value.trim().length > 0) {
                dropdown.classList.add('visible');
            }
        });
    }

    function actualizarCubiculos() {
        const tallerGrid = document.getElementById('tallerGrid');
        if (!tallerGrid) return;

        fetch('../api/cubiculos.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) return;

                const cards = document.querySelectorAll('.cubiculo-card');
                let cambios = false;

                data.data.forEach(function (cub, index) {
                    if (index >= cards.length) return;
                    const card = cards[index];
                    const estadoActual = card.classList.contains('ocupado') ? 'ocupado' : 'libre';
                    if (cub.estado !== estadoActual) {
                        cambios = true;
                    }
                });

                if (cambios) {
                    location.reload();
                }
            })
            .catch(function () {});
    }

    setInterval(actualizarCubiculos, 30000);
});
