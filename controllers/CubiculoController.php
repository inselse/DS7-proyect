<?php

require_once __DIR__ . '/../clases/Cubiculo.php';
require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Vehiculo.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';
require_once __DIR__ . '/AuthController.php';

class CubiculoController
{
    private array $errores = [];

    public function __construct()
    {
        AuthController::verificarSesion();
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function asignar(array $post): bool
    {
        if (!AuthController::verificarCSRF($post['csrf_token'] ?? '')) {
            $this->errores[] = 'Token de seguridad invalido.';
            return false;
        }

        $cubiculoId = (int)($post['cubiculo_id'] ?? 0);
        $placa = trim($post['placa'] ?? '');

        if ($cubiculoId <= 0 || empty($placa)) {
            $this->errores[] = 'Datos incompletos para la asignacion.';
            return false;
        }

        $cubiculo = Cubiculo::buscarPorId($cubiculoId);

        if ($cubiculo === null || $cubiculo->estaOcupado()) {
            $this->errores[] = 'El cubiculo seleccionado no esta disponible.';
            return false;
        }

        $vehiculo = Vehiculo::buscarPorPlaca($placa);

        if ($vehiculo === null) {
            $this->errores[] = 'Vehiculo no encontrado con esa placa. Debe registrarlo primero.';
            return false;
        }

        if ($vehiculo->tieneRegistroActivo()) {
            $this->errores[] = 'Este vehiculo ya esta en el taller.';
            return false;
        }

        $registroId = Registro::crear([
            'vehiculo_id' => $vehiculo->getId(),
            'cubiculo_id' => $cubiculoId,
            'usuario_id'  => $_SESSION['usuario_id'],
        ]);

        if ($registroId === 0) {
            $this->errores[] = 'Error al crear el registro de entrada.';
            return false;
        }

        return true;
    }

    public function liberar(array $post): bool
    {
        if (!AuthController::verificarCSRF($post['csrf_token'] ?? '')) {
            $this->errores[] = 'Token de seguridad invalido.';
            return false;
        }

        $cubiculoId = (int)($post['cubiculo_id'] ?? 0);

        if ($cubiculoId <= 0) {
            $this->errores[] = 'Cubiculo no especificado.';
            return false;
        }

        $cubiculo = Cubiculo::buscarPorId($cubiculoId);

        if ($cubiculo === null || !$cubiculo->estaOcupado()) {
            $this->errores[] = 'El cubiculo no esta ocupado.';
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT r.id FROM registros_entrada r
             WHERE r.cubiculo_id = ? AND r.estado = ?
             ORDER BY r.fecha_entrada DESC LIMIT 1'
        );
        $stmt->execute([$cubiculoId, 'activo']);
        $registro = $stmt->fetch();

        if (!$registro) {
            $this->errores[] = 'No se encontro un registro activo para este cubiculo.';
            return false;
        }

        $registroObj = Registro::buscarPorId((int)$registro['id']);

        if ($registroObj === null) {
            $this->errores[] = 'Error al obtener el registro.';
            return false;
        }

        $observaciones = trim($post['observaciones'] ?? '');

        return $registroObj->cerrar($observaciones);
    }

    public function obtenerEstado(): array
    {
        return Cubiculo::obtenerEstadoCompleto();
    }
}
