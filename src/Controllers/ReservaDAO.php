<?php

require_once __DIR__ . '/../Models/Reserva.php';
require_once __DIR__ . '/../Models/ReservaEvento.php';
require_once __DIR__ . '/ReservaEventoDAO.php';
require_once __DIR__ . '/../../dbConnetion.php';
require_once __DIR__ . '/../../DbAccess.php';

class ReservaDAO
{

    public static function select($id): ?Reserva
    {
        $conn = DBConnection::connectDB();
        if (!is_null($conn)) {
            $stmt = $conn->prepare("SELECT id, 
                                           nombre_huesped,
                                           email_huesped,
                                           nombre_alojamiento,
                                           fecha_entrada,
                                           fecha_salida,
                                           estado,
                                           importe,
                                           notas,
                                           fecha_creacion 
                                     FROM reserva
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

    public static function insert($object): int
    {
        $conn = DBConnection::connectDB();
        if (!is_null($conn)) {
            $stmt = $conn->prepare("INSERT INTO reserva (nombre_huesped,
                                                         email_huesped,
                                                         nombre_alojamiento,
                                                         fecha_entrada,
                                                         fecha_salida,
                                                         estado,
                                                         importe,
                                                         notas)
                                           VALUES(:nombre_huesped,
                                                  :email_huesped,
                                                  :nombre_alojamiento,
                                                  :fecha_entrada,
                                                  :fecha_salida,
                                                  :estado,
                                                  :importe,
                                                  :notas)");
            $stmt->execute([
                'nombre_huesped' => $object->getNombreHuesped(),
                'email_huesped' => $object->getEmailHuesped(),
                'nombre_alojamiento' => $object->getNombreAlojamiento(),
                'fecha_entrada' => $object->getFechaEntrada(),
                'fecha_salida' => $object->getFechaSalida(),
                'estado' => $object->getEstado(),
                'importe' => $object->getImporte(),
                'notas' => $object->getNotas()
            ]);
            $reservaId = $conn->lastInsertId();

            if ($reservaId > 0) {
                $evento = new ReservaEvento();
                $evento->setReservaId($reservaId);
                $evento->setTipo("creada");
                $evento->setDescripcion("Reservado correctamente");
                ReservaEventoDAO::insert($evento);
            }

            return $reservaId;
        }
        return 0;
    }

    public static function update($object): int
    {
        $reservaAntigua = self::select($object->getId());

        $conn = DBConnection::connectDB();
        if (!is_null($conn)) {
            $stmt = $conn->prepare("UPDATE reserva
                                    SET nombre_huesped = :nombre_huesped,
                                        email_huesped = :email_huesped,
                                        nombre_alojamiento = :nombre_alojamiento,
                                        fecha_entrada = :fecha_entrada,
                                        fecha_salida = :fecha_salida,
                                        estado = :estado,
                                        importe = :importe,
                                        notas = :notas
                                    WHERE id = :id");
            $stmt->execute([
                'id' => $object->getId(),
                'nombre_huesped' => $object->getNombreHuesped(),
                'email_huesped' => $object->getEmailHuesped(),
                'nombre_alojamiento' => $object->getNombreAlojamiento(),
                'fecha_entrada' => $object->getFechaEntrada(),
                'fecha_salida' => $object->getFechaSalida(),
                'estado' => $object->getEstado(),
                'importe' => $object->getImporte(),
                'notas' => $object->getNotas()
            ]);
            $rowsAffected = $stmt->rowCount();

            if($rowsAffected > 0 && $reservaAntigua){
                if($reservaAntigua->getEstado() !== $object->getEstado()){
                    $evento = new ReservaEvento();
                    $evento->setReservaId($object->getId());
                    $evento->setTipo($object->getEstado());
                    $evento->setDescripcion('Nuevo estado cambiado a: ' . $object->getEstado());
                    ReservaEventoDAO::insert($evento);
                }
            }

            return $rowsAffected;
        }
        return 0;
    }

    public static function getAll(?string $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $nombreHuesped = null): array
    {
        $llista_reservas = [];
        $conn = DBConnection::connectDB();

        if (!is_null($conn)) {
            $sql = "SELECT id, 
                           nombre_huesped,
                           email_huesped,
                           nombre_alojamiento,
                           fecha_entrada,
                           fecha_salida,
                           estado,
                           importe,
                           notas,
                           fecha_creacion 
                    FROM reserva WHERE 1=1";

            $params = [];

            if (!empty($estado)) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $estado;
            }

            if (!empty($fechaInicio)) {
                $sql .= " AND fecha_entrada >= :fechaInicio";
                $params[':fechaInicio'] = $fechaInicio;
            }

            if (!empty($fechaFin)) {
                $sql .= " AND fecha_salida <= :fechaFin";
                $params[':fechaFin'] = $fechaFin;
            }

            if (!empty($nombreHuesped)) {
                $sql .= " AND nombre_huesped LIKE :nombreHuesped";
                $params[':nombreHuesped'] = "%" . $nombreHuesped . "%";
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Reserva');

            while ($reserva = $stmt->fetch()) {
                $llista_reservas[] = $reserva;
            }
        }
        return $llista_reservas;
    }
}
