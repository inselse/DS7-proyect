<?php

class AuthController
{
    private const TIEMPO_EXPIRACION = 7200;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(string $email, string $password): bool
    {
        $usuario = Usuario::buscarPorEmail($email);

        if ($usuario === null || !$usuario->estaActivo()) {
            return false;
        }

        if (!$usuario->verificarPassword($password)) {
            return false;
        }

        $_SESSION['usuario_id']   = $usuario->getId();
        $_SESSION['nombre']       = $usuario->getNombre();
        $_SESSION['rol']          = $usuario->getRol();
        $_SESSION['ultimo_acceso'] = time();

        return true;
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    public static function verificarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        if (isset($_SESSION['ultimo_acceso'])) {
            $inactividad = time() - $_SESSION['ultimo_acceso'];
            if ($inactividad > self::TIEMPO_EXPIRACION) {
                session_unset();
                session_destroy();
                header('Location: login.php?expirado=1');
                exit;
            }
        }

        $_SESSION['ultimo_acceso'] = time();
    }

    public static function generarCSRF(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    public static function verificarCSRF(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        $valido = hash_equals($_SESSION['csrf_token'], $token);
        unset($_SESSION['csrf_token']);

        return $valido;
    }
}
