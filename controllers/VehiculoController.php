<?php

require_once __DIR__ . '/../clases/Vehiculo.php';
require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Cubiculo.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';
require_once __DIR__ . '/AuthController.php';

class VehiculoController
{
    private array $errores = [];
    private array $datos = [];

    public function __construct()
    {
        AuthController::verificarSesion();
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function getDatos(): array
    {
        return $this->datos;
    }

    public function procesarRegistro(array $post): array
    {
        $this->datos = $post;

        if (!AuthController::verificarCSRF($post['csrf_token'] ?? '')) {
            $this->errores[] = 'Token de seguridad invalido. Intente nuevamente.';
            return ['exito' => false];
        }

        $this->validarDatosVehiculo($post);
        $this->validarDatosPropietario($post);
        $this->validarCubiculo($post);

        if (!empty($this->errores)) {
            return ['exito' => false];
        }

        $vehiculo = Vehiculo::buscarPorPlaca($post['placa']);

        if ($vehiculo === null) {
            $creado = Vehiculo::registrar($post);

            if (!$creado) {
                $this->errores[] = 'Error al registrar el vehiculo. Intente nuevamente.';
                return ['exito' => false];
            }

            $vehiculo = Vehiculo::buscarPorPlaca($post['placa']);
        }

        if ($vehiculo->tieneRegistroActivo()) {
            $this->errores[] = 'Este vehiculo ya esta registrado en el taller. Por favor use la pantalla de Cubiculos para gestionar su entrada/salida.';
            return ['exito' => false];
        }

        $cubiculoId = (int)$post['cubiculo_id'];
        $cubiculo = Cubiculo::buscarPorId($cubiculoId);

        if ($cubiculo === null || $cubiculo->estaOcupado()) {
            $this->errores[] = 'El cubiculo seleccionado ya no esta disponible.';
            return ['exito' => false];
        }

        $registroId = Registro::crear([
            'vehiculo_id' => $vehiculo->getId(),
            'cubiculo_id' => $cubiculoId,
            'usuario_id'  => $_SESSION['usuario_id'],
        ]);

        if ($registroId === 0) {
            $this->errores[] = 'Error al crear el registro de entrada.';
            return ['exito' => false];
        }

        $this->registrarMantenimientosIniciales($post, $registroId);

        return [
            'exito'      => true,
            'registro_id'=> $registroId,
            'vehiculo'   => $vehiculo,
        ];
    }

    private function validarDatosVehiculo(array $post): void
    {
        $placa = trim($post['placa'] ?? '');

        if (empty($placa)) {
            $this->errores[] = 'La placa es obligatoria.';
        } elseif (!preg_match('/^[A-Za-z0-9\-]{3,20}$/', $placa)) {
            $this->errores[] = 'Formato de placa invalido. Use solo letras, numeros y guiones.';
        }

        if (empty($post['marca'] ?? '')) {
            $this->errores[] = 'La marca es obligatoria.';
        }

        if (empty($post['modelo'] ?? '')) {
            $this->errores[] = 'El modelo es obligatorio.';
        }

        $anio = (int)($post['anio'] ?? 0);
        $anioActual = (int)date('Y');

        if ($anio < 1970 || $anio > $anioActual) {
            $this->errores[] = "El ano debe estar entre 1970 y $anioActual.";
        }

        if (empty($post['color'] ?? '')) {
            $this->errores[] = 'El color es obligatorio.';
        }
    }

    private function validarDatosPropietario(array $post): void
    {
        if (empty(trim($post['dueno_nombre'] ?? ''))) {
            $this->errores[] = 'El nombre del dueno es obligatorio.';
        }

        $email = $post['dueno_email'] ?? '';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El correo electronico del dueno no es valido.';
        }
    }

    private function validarCubiculo(array $post): void
    {
        $cubiculoId = (int)($post['cubiculo_id'] ?? 0);

        if ($cubiculoId <= 0) {
            $this->errores[] = 'Debe seleccionar un cubiculo disponible.';
        }
    }

    private function registrarMantenimientosIniciales(array $post, int $registroId): void
    {
        $tiposSeleccionados = $post['mantenimientos'] ?? [];

        if (!is_array($tiposSeleccionados) || empty($tiposSeleccionados)) {
            return;
        }

        $tiposValidos = array_keys(Mantenimiento::tiposDisponibles());

        foreach ($tiposSeleccionados as $tipo) {
            if (!in_array($tipo, $tiposValidos, true)) {
                continue;
            }

            $descripcion = '';

            if ($tipo === 'otro' && !empty($post['otro_descripcion'])) {
                $descripcion = $post['otro_descripcion'];
            } else {
                $etiquetas = Mantenimiento::tiposDisponibles();
                $descripcion = $etiquetas[$tipo] . ' - Registrado al ingreso.';
            }

            Mantenimiento::registrar([
                'registro_id' => $registroId,
                'tipo'        => $tipo,
                'descripcion' => $descripcion,
                'costo'       => (float)($post['costo_' . $tipo] ?? 0),
                'mecanico_id' => null,
                'estado'      => 'pendiente',
            ]);
        }
    }

    public function buscarAjax(string $termino): array
    {
        return Vehiculo::buscar($termino);
    }

    public function obtenerConEstado(string $placa): array
    {
        $vehiculo = Vehiculo::buscarPorPlaca($placa);

        if ($vehiculo === null) {
            return ['encontrado' => false];
        }

        $registroActivo = Registro::buscarActivo($vehiculo->getId());

        return [
            'encontrado'     => true,
            'vehiculo'       => $vehiculo->toArray(),
            'registro_activo'=> $registroActivo ? [
                'id'            => $registroActivo->getId(),
                'cubiculo_id'   => $registroActivo->getCubiculoId(),
                'fecha_entrada' => $registroActivo->getFechaEntrada(),
            ] : null,
        ];
    }
}
