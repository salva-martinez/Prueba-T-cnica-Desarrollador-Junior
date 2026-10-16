<?php

require_once './src/Models/ReservaEvento.php';
require_once './dbConnetion.php';
require_once './DbAccess.php';

class ReservaEventoDAO
{
    public static function insert($object): int
    {
        $conn = DBConnection::connectDB();
        if (!is_null($conn)) {
            $stmt = $conn->prepare("INSERT INTO reserva_evento (reserva_id, 
                                                                tipo, 
                                                                descripcion)
                                    VALUES (:reserva_id, 
                                    :tipo, 
                                    :descripcion)");
            $stmt->execute([
                'reserva_id' => $object->getReservaId(),
                'tipo' => $object->getTipo(),
                'descripcion' => $object->getDescripcion()
            ]);
            return $conn->lastInsertId();
        }
        return 0;
    }
}
