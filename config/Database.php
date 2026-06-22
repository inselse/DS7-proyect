<?php

class Database
{
    private static ?PDO $instancia = null;
    private static string $host = 'localhost';
    private static string $dbname = 'taller_db';
    private static string $user = 'root';
    private static string $password = '';
    private static string $charset = 'utf8mb4';

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            try {
                self::$instancia = new PDO($dsn, self::$user, self::$password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Error de conexion a BD: ' . $e->getMessage());
                die('Error de conexion a la base de datos. Contacte al administrador.');
            }
        }

        return self::$instancia;
    }
}
