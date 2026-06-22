<?php

require_once __DIR__ . '/../clases/Usuario.php';

class UsuarioController
{
    private array $errores = [];

    public function listar(): array
    {
        return Usuario::listarTodos();
    }

    public function crear(array $post): bool
    {
        $this->errores = [];

        $nombre = trim($post['nombre'] ?? '');
        $email  = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $rol    = $post['rol'] ?? 'mecanico';

        if (empty($nombre)) {
            $this->errores[] = 'El nombre es obligatorio.';
        }

        if (empty($email)) {
            $this->errores[] = 'El correo electronico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El correo electronico no es valido.';
        } elseif (Usuario::buscarPorEmail($email) !== null) {
            $this->errores[] = 'Ya existe un usuario con ese correo electronico.';
        }

        if (strlen($password) < 6) {
            $this->errores[] = 'La contrasena debe tener al menos 6 caracteres.';
        }

        if (!in_array($rol, ['admin', 'mecanico'], true)) {
            $rol = 'mecanico';
        }

        if (!empty($this->errores)) {
            return false;
        }

        return Usuario::crear([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $password,
            'rol'      => $rol,
            'activo'   => 1,
        ]);
    }

    public function actualizar(int $id, array $post): bool
    {
        $this->errores = [];

        $usuario = Usuario::buscarPorId($id);

        if ($usuario === null) {
            $this->errores[] = 'Usuario no encontrado.';
            return false;
        }

        $nombre = trim($post['nombre'] ?? '');
        $email  = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $rol    = $post['rol'] ?? 'mecanico';
        $activo = isset($post['activo']) ? 1 : 0;

        if (empty($nombre)) {
            $this->errores[] = 'El nombre es obligatorio.';
        }

        if (empty($email)) {
            $this->errores[] = 'El correo electronico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El correo electronico no es valido.';
        } else {
            $existente = Usuario::buscarPorEmail($email);
            if ($existente !== null && $existente->getId() !== $id) {
                $this->errores[] = 'Ya existe otro usuario con ese correo electronico.';
            }
        }

        if (!empty($password) && strlen($password) < 6) {
            $this->errores[] = 'La contrasena debe tener al menos 6 caracteres.';
        }

        if (!in_array($rol, ['admin', 'mecanico'], true)) {
            $rol = 'mecanico';
        }

        if (!empty($this->errores)) {
            return false;
        }

        return $usuario->actualizar([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $password,
            'rol'      => $rol,
            'activo'   => $activo,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $this->errores = [];

        if ($id <= 0) {
            $this->errores[] = 'ID de usuario invalido.';
            return false;
        }

        $usuario = Usuario::buscarPorId($id);

        if ($usuario === null) {
            $this->errores[] = 'Usuario no encontrado.';
            return false;
        }

        if ($usuario->getEmail() === 'admin@taller.com') {
            $this->errores[] = 'No se puede eliminar el usuario administrador principal.';
            return false;
        }

        return Usuario::eliminar($id);
    }

    public function getErrores(): array
    {
        return $this->errores;
    }
}
