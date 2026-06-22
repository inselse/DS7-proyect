<?php

require_once __DIR__ . '/../config/Database.php';

class Vehiculo
{
    private int $id;
    private string $placa;
    private string $marca;
    private string $modelo;
    private int $anio;
    private string $color;
    private string $dueno_nombre;
    private ?string $dueno_telefono;
    private ?string $dueno_email;

    public function __construct(array $datos = [])
    {
        if (!empty($datos)) {
            $this->id              = (int)($datos['id'] ?? 0);
            $this->placa           = $datos['placa'] ?? '';
            $this->marca           = $datos['marca'] ?? '';
            $this->modelo          = $datos['modelo'] ?? '';
            $this->anio            = (int)($datos['anio'] ?? 0);
            $this->color           = $datos['color'] ?? '';
            $this->dueno_nombre    = $datos['dueno_nombre'] ?? '';
            $this->dueno_telefono  = $datos['dueno_telefono'] ?? null;
            $this->dueno_email     = $datos['dueno_email'] ?? null;
        }
    }

    public function getId(): int { return $this->id; }
    public function getPlaca(): string { return $this->placa; }
    public function getMarca(): string { return $this->marca; }
    public function getModelo(): string { return $this->modelo; }
    public function getAnio(): int { return $this->anio; }
    public function getColor(): string { return $this->color; }
    public function getDuenoNombre(): string { return $this->dueno_nombre; }
    public function getDuenoTelefono(): ?string { return $this->dueno_telefono; }
    public function getDuenoEmail(): ?string { return $this->dueno_email; }

    public static function registrar(array $datos): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO vehiculos (placa, marca, modelo, anio, color, dueno_nombre, dueno_telefono, dueno_email)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            strtoupper($datos['placa']),
            $datos['marca'],
            $datos['modelo'],
            (int)$datos['anio'],
            $datos['color'],
            $datos['dueno_nombre'],
            $datos['dueno_telefono'] ?? null,
            $datos['dueno_email'] ?? null,
        ]);
    }

    public static function buscarPorPlaca(string $placa): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM vehiculos WHERE placa = ? LIMIT 1');
        $stmt->execute([strtoupper($placa)]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function buscarPorId(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM vehiculos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function listarTodos(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM vehiculos ORDER BY creado_en DESC');
        $resultados = [];

        while ($fila = $stmt->fetch()) {
            $resultados[] = new self($fila);
        }

        return $resultados;
    }

    public static function buscar(string $termino): array
    {
        $db = Database::getInstance();
        $like = '%' . $termino . '%';
        $stmt = $db->prepare(
            'SELECT * FROM vehiculos
             WHERE placa LIKE ?
                OR marca LIKE ?
                OR modelo LIKE ?
                OR dueno_nombre LIKE ?
             ORDER BY placa ASC
             LIMIT 20'
        );
        $stmt->execute([$like, $like, $like, $like]);
        $resultados = [];

        while ($fila = $stmt->fetch()) {
            $resultados[] = new self($fila);
        }

        return $resultados;
    }

    public function actualizar(array $datos): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'UPDATE vehiculos SET
                placa = ?, marca = ?, modelo = ?, anio = ?, color = ?,
                dueno_nombre = ?, dueno_telefono = ?, dueno_email = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            strtoupper($datos['placa']),
            $datos['marca'],
            $datos['modelo'],
            (int)$datos['anio'],
            $datos['color'],
            $datos['dueno_nombre'],
            $datos['dueno_telefono'] ?? null,
            $datos['dueno_email'] ?? null,
            $this->id,
        ]);
    }

    public function tieneRegistroActivo(): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM registros_entrada
             WHERE vehiculo_id = ? AND estado = ?'
        );
        $stmt->execute([$this->id, 'activo']);

        return $stmt->fetchColumn() > 0;
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'placa'          => $this->placa,
            'marca'          => $this->marca,
            'modelo'         => $this->modelo,
            'anio'           => $this->anio,
            'color'          => $this->color,
            'dueno_nombre'   => $this->dueno_nombre,
            'dueno_telefono' => $this->dueno_telefono,
            'dueno_email'    => $this->dueno_email,
        ];
    }
}
