<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
verificarApiKey();

try {

    $estado = $_GET['estado'] ?? null;
    $nombre = $_GET['nombre'] ?? null;
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;

    $reservas = ReservaDAO::getAll($estado, $fechaInicio, $fechaFin, $nombre);

    $data = [];
    foreach ($reservas as $r) {
        $data[] = [
            "id" => $r->getId(),
            "nombre_huesped" => $r->getNombreHuesped(),
            "email_huesped" => $r->getEmailHuesped(),
            "nombre_alojamiento" => $r->getnombreAlojamiento(),
            "fecha_entrada" => $r->getFechaEntrada(),
            "fecha_salida" => $r->getFechaSalida(),
            "estado" => $r->getEstado(),
            "importe" => $r->getImporte(),
            "notas" => $r->getNotas()
        ];
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error interno del servidor: " . $e->getMessage()]);
}
