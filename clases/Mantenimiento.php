<?php

require_once __DIR__ . '/../config/Database.php';

class Mantenimiento
{
    private int $id;
    private int $registro_id;
    private string $tipo;
    private string $descripcion;
    private float $costo;
    private ?int $mecanico_id;
    private string $estado;
    private ?string $fecha_inicio;
    private ?string $fecha_fin;

    private static array $mapaTipos = [
        'cambio_aceite' => 'Cambio de Aceite',
        'frenos'        => 'Frenos',
        'filtros'       => 'Filtros',
        'otro'          => 'Otro',
    ];

    public function __construct(array $datos = [])
    {
        if (!empty($datos)) {
            $this->id          = (int)($datos['id'] ?? 0);
            $this->registro_id = (int)($datos['registro_id'] ?? 0);
            $this->tipo        = $datos['tipo'] ?? 'otro';
            $this->descripcion = $datos['descripcion'] ?? '';
            $this->costo       = (float)($datos['costo'] ?? 0);
            $this->mecanico_id = isset($datos['mecanico_id']) ? (int)$datos['mecanico_id'] : null;
            $this->estado      = $datos['estado'] ?? 'pendiente';
            $this->fecha_inicio = $datos['fecha_inicio'] ?? null;
            $this->fecha_fin   = $datos['fecha_fin'] ?? null;
        }
    }

    public function getId(): int { return $this->id; }
    public function getRegistroId(): int { return $this->registro_id; }
    public function getTipo(): string { return $this->tipo; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getCosto(): float { return $this->costo; }
    public function getMecanicoId(): ?int { return $this->mecanico_id; }
    public function getEstado(): string { return $this->estado; }

    public function getTipoEtiqueta(): string
    {
        return self::$mapaTipos[$this->tipo] ?? $this->tipo;
    }

    public static function tiposDisponibles(): array
    {
        return self::$mapaTipos;
    }

    public static function registrar(array $datos): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO mantenimientos (registro_id, tipo, descripcion, costo, mecanico_id, estado, fecha_inicio)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            (int)$datos['registro_id'],
            $datos['tipo'],
            $datos['descripcion'],
            (float)($datos['costo'] ?? 0),
            !empty($datos['mecanico_id']) ? (int)$datos['mecanico_id'] : null,
            $datos['estado'] ?? 'pendiente',
            $datos['fecha_inicio'] ?? null,
        ]);
    }

    public static function listarPorRegistro(int $registro_id): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT m.*, u.nombre AS mecanico_nombre
             FROM mantenimientos m
             LEFT JOIN usuarios u ON u.id = m.mecanico_id
             WHERE m.registro_id = ?
             ORDER BY m.creado_en ASC'
        );
        $stmt->execute([$registro_id]);

        return $stmt->fetchAll();
    }

    public function actualizarEstado(string $estado): bool
    {
        $db = Database::getInstance();

        $sql = 'UPDATE mantenimientos SET estado = ?';
        $params = [$estado];

        if ($estado === 'en_proceso' && !$this->fecha_inicio) {
            $sql .= ', fecha_inicio = NOW()';
        } elseif ($estado === 'completado') {
            $sql .= ', fecha_fin = NOW()';
        }

        $sql .= ' WHERE id = ?';
        $params[] = $this->id;

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function resumenPorTipo(): array
    {
        $db = Database::getInstance();
        $fecha = date('Y-m-d', strtotime('-30 days'));
        $stmt = $db->prepare(
            'SELECT
                m.tipo,
                COUNT(*) AS cantidad,
                SUM(m.costo) AS total_costo,
                SUM(CASE WHEN m.estado = ? THEN 1 ELSE 0 END) AS completados
             FROM mantenimientos m
             WHERE m.creado_en >= ?
             GROUP BY m.tipo
             ORDER BY m.tipo ASC'
        );
        $stmt->execute(['completado', $fecha]);

        return $stmt->fetchAll();
    }

    public static function completadosHoy(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM mantenimientos
             WHERE estado = ? AND DATE(fecha_fin) = CURDATE()"
        );
        $stmt->execute(['completado']);

        return (int)$stmt->fetchColumn();
    }
}
