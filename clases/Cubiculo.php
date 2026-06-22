<?php

require_once __DIR__ . '/../config/Database.php';

class Cubiculo
{
    private int $id;
    private int $numero;
    private string $nombre;
    private string $estado;

    public function __construct(array $datos = [])
    {
        if (!empty($datos)) {
            $this->id     = (int)($datos['id'] ?? 0);
            $this->numero = (int)($datos['numero'] ?? 0);
            $this->nombre = $datos['nombre'] ?? '';
            $this->estado = $datos['estado'] ?? 'libre';
        }
    }

    public function getId(): int { return $this->id; }
    public function getNumero(): int { return $this->numero; }
    public function getNombre(): string { return $this->nombre; }
    public function getEstado(): string { return $this->estado; }

    public function estaOcupado(): bool
    {
        return $this->estado === 'ocupado';
    }

    public static function listarTodos(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM cubiculos ORDER BY numero ASC');
        $resultados = [];

        while ($fila = $stmt->fetch()) {
            $resultados[] = new self($fila);
        }

        return $resultados;
    }

    public static function buscarDisponible(): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT * FROM cubiculos WHERE estado = ? ORDER BY numero ASC LIMIT 1'
        );
        $stmt->execute(['libre']);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function buscarPorId(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM cubiculos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public function ocupar(): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE cubiculos SET estado = ? WHERE id = ? AND estado = ?');
        $stmt->execute(['ocupado', $this->id, 'libre']);

        return $stmt->rowCount() > 0;
    }

    public function liberar(): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE cubiculos SET estado = ? WHERE id = ?');
        $stmt->execute(['libre', $this->id]);

        return $stmt->rowCount() > 0;
    }

    public static function contarLibres(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM cubiculos WHERE estado = ?');
        $stmt->execute(['libre']);

        return (int)$stmt->fetchColumn();
    }

    public static function contarOcupados(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM cubiculos WHERE estado = ?');
        $stmt->execute(['ocupado']);

        return (int)$stmt->fetchColumn();
    }

    public static function obtenerEstadoCompleto(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT
                c.id AS cubiculo_id,
                c.numero,
                c.nombre,
                c.estado,
                r.id AS registro_id,
                v.id AS vehiculo_id,
                v.placa,
                v.marca,
                v.modelo,
                v.color,
                v.dueno_nombre,
                GROUP_CONCAT(DISTINCT m.tipo ORDER BY m.tipo ASC SEPARATOR ", ") AS mantenimientos,
                r.fecha_entrada
             FROM cubiculos c
             LEFT JOIN registros_entrada r ON r.cubiculo_id = c.id AND r.estado = ?
             LEFT JOIN vehiculos v ON v.id = r.vehiculo_id
             LEFT JOIN mantenimientos m ON m.registro_id = r.id AND m.estado != ?
             GROUP BY c.id, c.numero, c.nombre, c.estado, r.id, v.id, v.placa, v.marca, v.modelo, v.color, v.dueno_nombre, r.fecha_entrada
             ORDER BY c.numero ASC'
        );
        $stmt->execute(['activo', 'cancelado']);

        return $stmt->fetchAll();
    }
}
