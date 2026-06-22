<?php

require_once __DIR__ . '/../config/Database.php';

class Registro
{
    private int $id;
    private int $vehiculo_id;
    private int $cubiculo_id;
    private int $usuario_id;
    private string $fecha_entrada;
    private ?string $fecha_salida;
    private string $estado;
    private ?string $observaciones;

    public function __construct(array $datos = [])
    {
        if (!empty($datos)) {
            $this->id            = (int)($datos['id'] ?? 0);
            $this->vehiculo_id   = (int)($datos['vehiculo_id'] ?? 0);
            $this->cubiculo_id   = (int)($datos['cubiculo_id'] ?? 0);
            $this->usuario_id    = (int)($datos['usuario_id'] ?? 0);
            $this->fecha_entrada = $datos['fecha_entrada'] ?? '';
            $this->fecha_salida  = $datos['fecha_salida'] ?? null;
            $this->estado        = $datos['estado'] ?? 'activo';
            $this->observaciones = $datos['observaciones'] ?? null;
        }
    }

    public function getId(): int { return $this->id; }
    public function getVehiculoId(): int { return $this->vehiculo_id; }
    public function getCubiculoId(): int { return $this->cubiculo_id; }
    public function getUsuarioId(): int { return $this->usuario_id; }
    public function getFechaEntrada(): string { return $this->fecha_entrada; }
    public function getFechaSalida(): ?string { return $this->fecha_salida; }
    public function getEstado(): string { return $this->estado; }
    public function getObservaciones(): ?string { return $this->observaciones; }

    public static function crear(array $datos): int
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                'INSERT INTO registros_entrada (vehiculo_id, cubiculo_id, usuario_id, estado)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                (int)$datos['vehiculo_id'],
                (int)$datos['cubiculo_id'],
                (int)$datos['usuario_id'],
                'activo',
            ]);

            $registroId = (int)$db->lastInsertId();

            $stmtCub = $db->prepare(
                'UPDATE cubiculos SET estado = ? WHERE id = ?'
            );
            $stmtCub->execute(['ocupado', (int)$datos['cubiculo_id']]);

            $db->commit();

            return $registroId;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error al crear registro: ' . $e->getMessage());
            return 0;
        }
    }

    public static function buscarActivo(int $vehiculo_id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT * FROM registros_entrada
             WHERE vehiculo_id = ? AND estado = ?
             ORDER BY fecha_entrada DESC
             LIMIT 1'
        );
        $stmt->execute([$vehiculo_id, 'activo']);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function buscarPorId(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM registros_entrada WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function listarActivos(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT r.*, v.placa, v.marca, v.modelo, v.color, v.dueno_nombre,
                    c.nombre AS cubiculo_nombre, c.numero AS cubiculo_numero
             FROM registros_entrada r
             INNER JOIN vehiculos v ON v.id = r.vehiculo_id
             INNER JOIN cubiculos c ON c.id = r.cubiculo_id
             WHERE r.estado = ?
             ORDER BY r.fecha_entrada DESC'
        );
        $stmt->execute(['activo']);

        return $stmt->fetchAll();
    }

    public static function listarTodos(array $filtros = []): array
    {
        $db = Database::getInstance();
        $sql = 'SELECT r.*, v.placa, v.marca, v.modelo, v.color, v.dueno_nombre,
                       v.dueno_telefono, c.nombre AS cubiculo_nombre
                FROM registros_entrada r
                INNER JOIN vehiculos v ON v.id = r.vehiculo_id
                INNER JOIN cubiculos c ON c.id = r.cubiculo_id
                WHERE 1=1';

        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND r.estado = ?';
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['placa'])) {
            $sql .= ' AND v.placa LIKE ?';
            $params[] = '%' . $filtros['placa'] . '%';
        }

        if (!empty($filtros['desde'])) {
            $sql .= ' AND r.fecha_entrada >= ?';
            $params[] = $filtros['desde'];
        }

        if (!empty($filtros['hasta'])) {
            $sql .= ' AND r.fecha_entrada <= ?';
            $params[] = $filtros['hasta'] . ' 23:59:59';
        }

        $sql .= ' ORDER BY r.fecha_entrada DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function cerrar(string $observaciones = ''): bool
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                'UPDATE registros_entrada
                 SET fecha_salida = NOW(), estado = ?, observaciones = ?
                 WHERE id = ? AND estado = ?'
            );
            $stmt->execute(['completado', $observaciones, $this->id, 'activo']);

            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                return false;
            }

            $stmtCub = $db->prepare(
                'UPDATE cubiculos SET estado = ? WHERE id = ?'
            );
            $stmtCub->execute(['libre', $this->cubiculo_id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error al cerrar registro: ' . $e->getMessage());
            return false;
        }
    }

    public static function conMantenimientosPorFecha(string $desde, string $hasta, array $filtrosTipo = [], string $filtroEstado = ''): array
    {
        $db = Database::getInstance();

        $sql = 'SELECT
                    r.id AS registro_id,
                    r.fecha_entrada,
                    r.fecha_salida,
                    r.estado AS registro_estado,
                    r.observaciones,
                    v.placa,
                    v.marca,
                    v.modelo,
                    v.dueno_nombre,
                    v.color,
                    c.nombre AS cubiculo_nombre,
                    m.id AS mantenimiento_id,
                    m.tipo AS mantenimiento_tipo,
                    m.descripcion AS mantenimiento_descripcion,
                    m.costo AS mantenimiento_costo,
                    m.estado AS mantenimiento_estado,
                    u.nombre AS mecanico_nombre
                FROM registros_entrada r
                INNER JOIN vehiculos v ON v.id = r.vehiculo_id
                INNER JOIN cubiculos c ON c.id = r.cubiculo_id
                INNER JOIN mantenimientos m ON m.registro_id = r.id
                LEFT JOIN usuarios u ON u.id = m.mecanico_id
                WHERE r.fecha_entrada >= ?
                  AND r.fecha_entrada <= ?';

        $params = [$desde, $hasta . ' 23:59:59'];

        if (!empty($filtrosTipo)) {
            $placeholders = implode(',', array_fill(0, count($filtrosTipo), '?'));
            $sql .= " AND m.tipo IN ($placeholders)";
            $params = array_merge($params, $filtrosTipo);
        }

        if ($filtroEstado === 'activos') {
            $sql .= ' AND r.estado = ?';
            $params[] = 'activo';
        } elseif ($filtroEstado === 'completados') {
            $sql .= ' AND r.estado = ?';
            $params[] = 'completado';
        }

        $sql .= ' ORDER BY r.fecha_entrada DESC, v.placa ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function contarActivos(): int
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) FROM registros_entrada WHERE estado = 'activo'");
        return (int)$stmt->fetchColumn();
    }

    public static function ultimasEntradas(int $limite = 5): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT r.*, v.placa, v.marca, v.modelo, v.color,
                    c.nombre AS cubiculo_nombre
             FROM registros_entrada r
             INNER JOIN vehiculos v ON v.id = r.vehiculo_id
             INNER JOIN cubiculos c ON c.id = r.cubiculo_id
             ORDER BY r.fecha_entrada DESC
             LIMIT ?'
        );
        $stmt->execute([$limite]);

        return $stmt->fetchAll();
    }
}
