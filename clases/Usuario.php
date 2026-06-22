<?php

require_once __DIR__ . '/../config/Database.php';

class Usuario
{
    private int $id;
    private string $nombre;
    private string $email;
    private string $password;
    private string $rol;
    private bool $activo;
    private string $creadoEn;

    public function __construct(array $datos = [])
    {
        if (!empty($datos)) {
            $this->id       = (int)($datos['id'] ?? 0);
            $this->nombre   = $datos['nombre'] ?? '';
            $this->email    = $datos['email'] ?? '';
            $this->password = $datos['password'] ?? '';
            $this->rol      = $datos['rol'] ?? 'mecanico';
            $this->activo   = (bool)($datos['activo'] ?? true);
            $this->creadoEn = $datos['creado_en'] ?? '';
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function estaActivo(): bool
    {
        return $this->activo;
    }

    public function getCreadoEn(): string
    {
        return $this->creadoEn;
    }

    public static function buscarPorId(int $id): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public static function buscarPorEmail(string $email): ?self
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $datos = $stmt->fetch();

        return $datos ? new self($datos) : null;
    }

    public function verificarPassword(string $input): bool
    {
        return password_verify($input, $this->password);
    }

    public static function crear(array $datos): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO usuarios (nombre, email, password, rol, activo)
             VALUES (?, ?, ?, ?, ?)'
        );

        $hash = password_hash($datos['password'], PASSWORD_BCRYPT);

        return $stmt->execute([
            $datos['nombre'],
            $datos['email'],
            $hash,
            $datos['rol'] ?? 'mecanico',
            $datos['activo'] ?? 1,
        ]);
    }

    public static function listarTodos(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM usuarios ORDER BY nombre ASC');
        $resultados = [];

        while ($fila = $stmt->fetch()) {
            $resultados[] = new self($fila);
        }

        return $resultados;
    }

    public static function listarMecanicos(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT * FROM usuarios WHERE rol = ? AND activo = 1 ORDER BY nombre ASC'
        );
        $stmt->execute(['mecanico']);
        $resultados = [];

        while ($fila = $stmt->fetch()) {
            $resultados[] = new self($fila);
        }

        return $resultados;
    }

    public function actualizar(array $datos): bool
    {
        $db = Database::getInstance();

        if (!empty($datos['password'])) {
            $hash = password_hash($datos['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare(
                'UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol = ?, activo = ? WHERE id = ?'
            );
            return $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $hash,
                $datos['rol'] ?? 'mecanico',
                $datos['activo'] ?? 1,
                $this->id,
            ]);
        }

        $stmt = $db->prepare(
            'UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ? WHERE id = ?'
        );
        return $stmt->execute([
            $datos['nombre'],
            $datos['email'],
            $datos['rol'] ?? 'mecanico',
            $datos['activo'] ?? 1,
            $this->id,
        ]);
    }

    public static function eliminar(int $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM usuarios WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'nombre'    => $this->nombre,
            'email'     => $this->email,
            'rol'       => $this->rol,
            'activo'    => $this->activo,
            'creado_en' => $this->creadoEn,
        ];
    }
}
