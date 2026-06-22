document.addEventListener('DOMContentLoaded', function () {

    var canvas = document.getElementById('chartMantenimientos');

    if (canvas && typeof datosMantenimientos !== 'undefined') {
        var ctx = canvas.getContext('2d');

        var etiquetasMap = {
            'cambio_aceite': 'Cambio Aceite',
            'frenos': 'Frenos',
            'filtros': 'Filtros',
            'otro': 'Otro'
        };

        var etiquetas = [];
        var cantidades = [];
        var costos = [];
        var colores = {
            'cambio_aceite': '#E87A23',
            'frenos': '#EF4444',
            'filtros': '#1A6FD4',
            'otro': '#8B5CF6'
        };
        var colorArray = [];

        datosMantenimientos.forEach(function (item) {
            etiquetas.push(etiquetasMap[item.tipo] || item.tipo);
            cantidades.push(parseInt(item.cantidad) || 0);
            costos.push(parseFloat(item.total_costo) || 0);
            colorArray.push(colores[item.tipo] || '#6B7280');
        });

        if (etiquetas.length > 0) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Cantidad',
                        data: cantidades,
                        backgroundColor: colorArray.map(function (c) { return c + '33'; }),
                        borderColor: colorArray,
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.55,
                        categoryPercentage: 0.8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1523',
                            titleFont: { family: "'Inter', sans-serif", size: 12 },
                            bodyFont: { family: "'Inter', sans-serif", size: 11 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                afterLabel: function (context) {
                                    return 'Costo total: $' + (costos[context.dataIndex] || 0).toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#6B7280'
                            },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            ticks: {
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#6B7280'
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        } else {
            canvas.parentElement.innerHTML = '<p class="sin-datos">No hay datos de mantenimientos en los ultimos 30 dias.</p>';
        }
    }

    var donutCanvas = document.getElementById('chartDonut');

    if (donutCanvas && typeof datosMantenimientos !== 'undefined' && datosMantenimientos.length > 0) {
        var donutCtx = donutCanvas.getContext('2d');

        var donutLabels = [];
        var donutData = [];
        var donutColors = [];
        var colorMap = {
            'cambio_aceite': '#E87A23',
            'frenos': '#EF4444',
            'filtros': '#1A6FD4',
            'otro': '#8B5CF6'
        };
        var etiquetasMap = {
            'cambio_aceite': 'Cambio Aceite',
            'frenos': 'Frenos',
            'filtros': 'Filtros',
            'otro': 'Otro'
        };

        datosMantenimientos.forEach(function (item) {
            var cant = parseInt(item.cantidad) || 0;
            if (cant > 0) {
                donutLabels.push(etiquetasMap[item.tipo] || item.tipo);
                donutData.push(cant);
                donutColors.push(colorMap[item.tipo] || '#6B7280');
            }
        });

        if (donutData.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: donutColors,
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: "'Inter', sans-serif", size: 10 },
                                padding: 12,
                                usePointStyle: true,
                                color: '#6B7280'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F1523',
                            titleFont: { family: "'Inter', sans-serif", size: 12 },
                            bodyFont: { family: "'Inter', sans-serif", size: 11 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.parsed + ' servicios';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            donutCanvas.parentElement.innerHTML = '<p class="sin-datos">Sin datos</p>';
        }
    }

    var formReporte = document.getElementById('formReporte');
    if (formReporte) {
        formReporte.addEventListener('submit', function (e) {
            var desde = document.getElementById('desde');
            var hasta = document.getElementById('hasta');

            if (desde && hasta) {
                if (desde.value && hasta.value && desde.value > hasta.value) {
                    e.preventDefault();
                    alert('La fecha "desde" no puede ser posterior a la fecha "hasta".');
                }
            }
        });
    }

    var colorPicker = document.getElementById('color');
    var colorTexto = document.getElementById('colorTexto');

    if (colorPicker && colorTexto) {
        var coloresNombres = {
            '#000000': 'Negro',
            '#333333': 'Gris Oscuro',
            '#6B7280': 'Gris',
            '#FFFFFF': 'Blanco',
            '#EF4444': 'Rojo',
            '#F59E0B': 'Amarillo',
            '#22C55E': 'Verde',
            '#1A6FD4': 'Azul',
            '#8B5CF6': 'Morado',
            '#EC4899': 'Rosa',
            '#78350F': 'Cafe',
            '#0F1523': 'Azul Obscuro'
        };

        colorPicker.addEventListener('input', function () {
            var hex = this.value.toUpperCase();
            colorTexto.value = coloresNombres[hex] || hex;
        });
    }
});
