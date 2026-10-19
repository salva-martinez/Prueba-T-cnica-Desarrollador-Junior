<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Services/ReservaValidator.php';

verificarApiKey();

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
    header('Allow: PUT, POST');
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

$validarId = static function ($valor): int|false {
    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    return $id === false ? false : $id;
};

$accion = $input['accion'] ?? null;

if ($accion === 'cancelar' || $accion === 'confirmar') {
    $id = $validarId($input['id'] ?? null);

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

        if ($accion === 'cancelar') {
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
        } else {
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
        }

        $reserva->setEstado($nuevoEstado);
        ReservaDAO::update($reserva);

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

        exit;
    } catch (Throwable $e) {
        error_log('Error al cambiar estado de reserva: ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'status' => 'error',
            'message' => 'Error interno del servidor al cambiar el estado.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

$errores = ReservaValidator::validarActualizacion($input);

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

$id = $validarId($input['id'] ?? null);

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

    if (isset($input['nombre_huesped'])) {
        $reserva->setNombreHuesped($input['nombre_huesped']);
    }

    if (isset($input['email_huesped'])) {
        $reserva->setEmailHuesped($input['email_huesped']);
    }

    if (isset($input['nombre_alojamiento'])) {
        $reserva->setNombreAlojamiento($input['nombre_alojamiento']);
    }

    if (isset($input['fecha_entrada'])) {
        $reserva->setFechaEntrada($input['fecha_entrada']);
    }

    if (isset($input['fecha_salida'])) {
        $reserva->setFechaSalida($input['fecha_salida']);
    }

    if (isset($input['estado'])) {
        $reserva->setEstado($input['estado']);
    }

    if (isset($input['importe'])) {
        $reserva->setImporte($input['importe']);
    }

    if (array_key_exists('notas', $input)) {
        $reserva->setNotas($input['notas']);
    }

    $filas = ReservaDAO::update($reserva);

    http_response_code(200);

    echo json_encode([
        'status' => 'success',
        'message' => 'Reserva actualizada correctamente.',
        'data' => [
            'id' => $id,
            'filas_afectadas' => $filas
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error en putReservas.php: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor al actualizar la reserva.',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
