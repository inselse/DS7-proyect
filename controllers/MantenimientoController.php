<?php

require_once __DIR__ . '/../clases/Mantenimiento.php';
require_once __DIR__ . '/../clases/Registro.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/AuthController.php';

class MantenimientoController
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

    public function guardar(array $post): bool
    {
        if (!AuthController::verificarCSRF($post['csrf_token'] ?? '')) {
            $this->errores[] = 'Token de seguridad invalido.';
            return false;
        }

        $registroId = (int)($post['registro_id'] ?? 0);
        $tipo = $post['tipo'] ?? '';
        $descripcion = trim($post['descripcion'] ?? '');

        if ($registroId <= 0) {
            $this->errores[] = 'Debe seleccionar un registro activo.';
            return false;
        }

        $tiposValidos = array_keys(Mantenimiento::tiposDisponibles());
        if (!in_array($tipo, $tiposValidos, true)) {
            $this->errores[] = 'Tipo de mantenimiento no valido.';
            return false;
        }

        if (empty($descripcion)) {
            $this->errores[] = 'La descripcion del mantenimiento es obligatoria.';
            return false;
        }

        if (!empty($this->errores)) {
            return false;
        }

        return Mantenimiento::registrar([
            'registro_id'  => $registroId,
            'tipo'         => $tipo,
            'descripcion'  => $descripcion,
            'costo'        => (float)($post['costo'] ?? 0),
            'mecanico_id'  => !empty($post['mecanico_id']) ? (int)$post['mecanico_id'] : null,
            'estado'       => $post['estado'] ?? 'pendiente',
            'fecha_inicio' => $post['fecha_inicio'] ?? null,
        ]);
    }

    public function actualizarEstado(array $post): bool
    {
        if (!AuthController::verificarCSRF($post['csrf_token'] ?? '')) {
            $this->errores[] = 'Token de seguridad invalido.';
            return false;
        }

        $id = (int)($post['id'] ?? 0);
        $estado = $post['estado'] ?? '';

        if ($id <= 0 || !in_array($estado, ['pendiente', 'en_proceso', 'completado'], true)) {
            $this->errores[] = 'Datos invalidos para actualizar el estado.';
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM mantenimientos WHERE id = ?');
        $stmt->execute([$id]);
        $datos = $stmt->fetch();

        if (!$datos) {
            $this->errores[] = 'Mantenimiento no encontrado.';
            return false;
        }

        $mantenimiento = new Mantenimiento($datos);
        return $mantenimiento->actualizarEstado($estado);
    }

    public function obtenerRegistrosActivos(): array
    {
        return Registro::listarActivos();
    }

    public function obtenerMecanicos(): array
    {
        return Usuario::listarMecanicos();
    }
}
