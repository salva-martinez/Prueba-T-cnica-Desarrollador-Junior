<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Models/Reserva.php';
require_once __DIR__ . '/../src/Services/ReservaValidator.php';

verificarApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode([
        'status' => 'error',
        'message' => 'Método HTTP no permitido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $input = json_decode(
        file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $e) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'El JSON enviado no es válido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (!is_array($input) || array_is_list($input)) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Se esperaba un objeto JSON con los datos de la reserva.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$errores = ReservaValidator::validarCreacion($input);

if (!empty($errores)) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Hay errores en los datos de la reserva.',
        'data' => null,
        'errors' => $errores
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $reserva = new Reserva();

    $reserva->setNombreHuesped($input['nombre_huesped']);
    $reserva->setEmailHuesped($input['email_huesped']);
    $reserva->setNombreAlojamiento(
        $input['nombre_alojamiento'] ?? 'Sin especificar'
    );
    $reserva->setFechaEntrada($input['fecha_entrada']);
    $reserva->setFechaSalida($input['fecha_salida']);

    $reserva->setEstado('pendiente');

    $reserva->setImporte($input['importe'] ?? 0.0);
    $reserva->setNotas($input['notas'] ?? '');

    $idInsertado = ReservaDAO::insert($reserva);

    if (!$idInsertado || $idInsertado <= 0) {
        throw new RuntimeException('No se pudo insertar la reserva.');
    }

    http_response_code(201);

    echo json_encode([
        'status' => 'success',
        'message' => 'Reserva creada correctamente.',
        'data' => [
            'id' => $idInsertado
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('Error en postReserva.php: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor al crear la reserva.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
