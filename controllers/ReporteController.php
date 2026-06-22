<?php

require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';
require_once __DIR__ . '/../clases/Cubiculo.php';
require_once __DIR__ . '/AuthController.php';

class ReporteController
{
    public function __construct()
    {
        AuthController::verificarSesion();
    }

    public function generar(array $filtros): array
    {
        $desde = $filtros['desde'] ?? date('Y-m-01');
        $hasta = $filtros['hasta'] ?? date('Y-m-d');
        $tipos = $filtros['tipos'] ?? [];
        $estado = $filtros['estado'] ?? '';

        $registros = Registro::conMantenimientosPorFecha($desde, $hasta, $tipos, $estado);

        $agrupados = [];
        $totalVehiculos = 0;
        $totalMantenimientos = 0;
        $totalCosto = 0;

        foreach ($registros as $fila) {
            $key = $fila['registro_id'];

            if (!isset($agrupados[$key])) {
                $agrupados[$key] = [
                    'registro_id'      => $fila['registro_id'],
                    'placa'            => $fila['placa'],
                    'marca'            => $fila['marca'],
                    'modelo'           => $fila['modelo'],
                    'dueno_nombre'     => $fila['dueno_nombre'],
                    'color'            => $fila['color'],
                    'cubiculo_nombre'  => $fila['cubiculo_nombre'],
                    'fecha_entrada'    => $fila['fecha_entrada'],
                    'fecha_salida'     => $fila['fecha_salida'],
                    'registro_estado'  => $fila['registro_estado'],
                    'observaciones'    => $fila['observaciones'],
                    'mantenimientos'   => [],
                    'costo_total'      => 0,
                ];

                $totalVehiculos++;
            }

            if ($fila['mantenimiento_id']) {
                $agrupados[$key]['mantenimientos'][] = [
                    'id'          => $fila['mantenimiento_id'],
                    'tipo'        => $fila['mantenimiento_tipo'],
                    'descripcion' => $fila['mantenimiento_descripcion'],
                    'costo'       => (float)$fila['mantenimiento_costo'],
                    'estado'      => $fila['mantenimiento_estado'],
                    'mecanico'    => $fila['mecanico_nombre'],
                ];

                $costo = (float)$fila['mantenimiento_costo'];
                $agrupados[$key]['costo_total'] += $costo;
                $totalCosto += $costo;
                $totalMantenimientos++;
            }
        }

        return [
            'registros'          => array_values($agrupados),
            'total_vehiculos'    => $totalVehiculos,
            'total_mantenimientos' => $totalMantenimientos,
            'total_costo'        => $totalCosto,
            'desde'              => $desde,
            'hasta'              => $hasta,
        ];
    }

    public function obtenerResumenDashboard(): array
    {
        $db = Database::getInstance();

        $totalVehiculos = (int)$db->query('SELECT COUNT(*) FROM vehiculos')->fetchColumn();
        $autosEnTaller = Registro::contarActivos();
        $completadosHoy = Mantenimiento::completadosHoy();
        $cubiculosLibres = Cubiculo::contarLibres();
        $resumenMantenimientos = Mantenimiento::resumenPorTipo();
        $ultimasEntradas = Registro::ultimasEntradas(5);
        $cubiculos = Cubiculo::obtenerEstadoCompleto();

        return [
            'total_vehiculos'        => $totalVehiculos,
            'autos_en_taller'        => $autosEnTaller,
            'completados_hoy'        => $completadosHoy,
            'cubiculos_libres'       => $cubiculosLibres,
            'resumen_mantenimientos' => $resumenMantenimientos,
            'ultimas_entradas'       => $ultimasEntradas,
            'cubiculos'              => $cubiculos,
        ];
    }
}
