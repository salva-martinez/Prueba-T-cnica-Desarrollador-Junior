<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Models/Reserva.php';
require_once __DIR__ . '/../src/Services/ReservaValidator.php';
verificarApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$errores = ReservaValidator::validarCreacion($input);

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "mensajes" => $errores
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$input || empty($input['nombre_huesped']) || empty($input['email_huesped']) || empty($input['fecha_entrada'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos obligatorios en la peticion JSON"]);
    exit;
}

try {

    $reserva = new Reserva();
    $reserva->setNombreHuesped($input['nombre_huesped']);
    $reserva->setEmailHuesped($input['email_huesped']);
    $reserva->setNombreAlojamiento($input['nombre_alojamiento'] ?? 'Sin especificar');
    $reserva->setFechaEntrada($input['fecha_entrada']);
    $reserva->setFechaSalida($input['fecha_salida']);
    $reserva->setEstado($input['estado'] ?? 'pendiente');
    $reserva->setImporte($input['importe'] ?? 0.0);
    $reserva->setNotas($input['notas'] ?? '');

    $idInsertado = ReservaDAO::insert($reserva);

    if ($idInsertado > 0) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "mensaje" => "Reserva creada correctamente",
            "id" => $idInsertado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo insertar la reserva en la base de datos"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
