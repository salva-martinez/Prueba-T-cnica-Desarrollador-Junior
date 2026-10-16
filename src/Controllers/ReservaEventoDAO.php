<?php

require_once  __DIR__ . '/..//Models/ReservaEvento.php';
require_once  __DIR__ . '/../../dbConnetion.php';
require_once  __DIR__ . '/../../DbAccess.php';

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

    public static function select($id): ?Reserva
    {
        $conn = DBConnection::connectDB();
        if (!is_null($conn)) {
            $stmt = $conn->prepare("SELECT reserva_id, 
                                           tipo,
                                           descripcion
                                     FROM reserva_evento
                                     WHERE id = :id");
            $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Reserva');
            $stmt->execute(['id' => $id]);
            $reserva = $stmt->fetch();
            if ($reserva) {
                return $reserva;
            }
        }
        return null;
    }
}
