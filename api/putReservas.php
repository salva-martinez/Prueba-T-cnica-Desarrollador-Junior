<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';

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

$accion = $input['accion'] ?? null;

if (!in_array($accion, ['confirmar', 'cancelar'], true)) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'La acción debe ser confirmar o cancelar.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$id = filter_var(
    $input['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'El ID de la reserva no es válido.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {
    $reserva = ReservaDAO::select($id);

    if (!$reserva) {
        http_response_code(404);

        echo json_encode([
            'status' => 'error',
            'message' => 'Reserva no encontrada.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ($accion === 'confirmar') {
        if ($reserva->getEstado() !== 'pendiente') {
            http_response_code(409);

            echo json_encode([
                'status' => 'error',
                'message' => 'Solo se pueden confirmar reservas pendientes.',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $nuevoEstado = 'confirmada';
        $mensaje = 'Reserva confirmada correctamente.';

    } else {
        if ($reserva->getEstado() === 'cancelada') {
            http_response_code(409);

            echo json_encode([
                'status' => 'error',
                'message' => 'Esta reserva ya está cancelada.',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $nuevoEstado = 'cancelada';
        $mensaje = 'Reserva cancelada correctamente.';
    }

    $reserva->setEstado($nuevoEstado);

    $filas = ReservaDAO::update($reserva);

    if ($filas <= 0) {
        throw new RuntimeException(
            'No se pudo actualizar el estado de la reserva.'
        );
    }

    $reservaActualizada = ReservaDAO::select($id);

    if (
        !$reservaActualizada ||
        $reservaActualizada->getEstado() !== $nuevoEstado
    ) {
        throw new RuntimeException(
            'No se pudo verificar el cambio de estado.'
        );
    }

    http_response_code(200);

    echo json_encode([
        'status' => 'success',
        'message' => $mensaje,
        'data' => [
            'id' => $id,
            'estado' => $nuevoEstado
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('Error al cambiar estado de reserva: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor al cambiar el estado.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
