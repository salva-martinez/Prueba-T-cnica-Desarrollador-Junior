<?php

class dbConnection
{

    private static $servidor = "localhost";
    private static $db = "Reservas";
    private static $user = "salva";
    private static $pass = "1234";

    public static function connectDB(): ?PDO
    {

        $servername = self::$servidor;
        $db = self::$db;
        $user = self::$user;
        $pass = self::$pass;

        try {
            $pdo = new PDO(
                "mysql:host=$servername;
            dbname=$db;
            charset=utf8",
                $user,
                $pass
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (PDOException $e) {
            echo "Conexión fallida: " . $e->getMessage();
            return null;
        }
    }
}

dbConnection::connectDB();
