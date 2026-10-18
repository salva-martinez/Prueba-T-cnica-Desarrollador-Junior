<?php

require_once __DIR__ . '/config.php';

class dbConnection
{
    private static ?PDO $conexion = null;

    public static function connectDB(): PDO
    {
        if (self::$conexion instanceof PDO) {
            return self::$conexion;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $nombreBD = getenv('DB_NAME') ?: '';
        $usuario = getenv('DB_USER') ?: '';
        $password = getenv('DB_PASSWORD') ?: '';

        if ($usuario === '') {
            throw new RuntimeException(
                'La configuración de la base de datos está incompleta.'
            );
        }

        $dsn = "mysql:host={$host};dbname={$nombreBD};charset=utf8mb4";

        self::$conexion = new PDO($dsn, $usuario, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$conexion;
    }
}
