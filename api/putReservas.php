<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Services/ReservaValidator.php';
verificarApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (
    is_array($input)
    && isset($input['accion'])
    && $input['accion'] === 'cancelar'
) {
    $id = filter_var(
        $input['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'error' => 'El ID de la reserva no es válido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $reserva = ReservaDAO::select($id);

        if (!$reserva) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'error' => 'Reserva no encontrada.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($reserva->getEstado() === 'cancelada') {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'error' => 'Esta reserva ya está cancelada.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $reserva->setEstado('cancelada');
        $filas = ReservaDAO::update($reserva);

        $reservaActualizada = ReservaDAO::select($id);

        if (
            !$reservaActualizada ||
            $reservaActualizada->getEstado() !== 'cancelada'
        ) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'error' => 'No se pudo cancelar la reserva.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Reserva cancelada correctamente.',
            'id' => $id,
            'estado' => 'cancelada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'error' => 'Error al cancelar la reserva.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (
    is_array($input)
    && isset($input['accion'])
    && $input['accion'] === 'confirmar'
) {
    $id = filter_var(
        $input['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'error' => 'El ID de la reserva no es válido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $reserva = ReservaDAO::select($id);

        if (!$reserva) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'error' => 'Reserva no encontrada.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($reserva->getEstado() !== 'pendiente') {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'error' => 'Solo se pueden confirmar reservas pendientes.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $reserva->setEstado('confirmada');
        ReservaDAO::update($reserva);

        $reservaActualizada = ReservaDAO::select($id);

        if (
            !$reservaActualizada ||
            $reservaActualizada->getEstado() !== 'confirmada'
        ) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'error' => 'No se pudo confirmar la reserva.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Reserva confirmada correctamente.',
            'id' => $id,
            'estado' => 'confirmada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'error' => 'Error al confirmar la reserva.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$errores = ReservaValidator::validarActualizacion($input);

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "mensajes" => $errores
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    $reserva = ReservaDAO::select($input['id']);
    if (!$reserva) {
        http_response_code(404);
        echo json_encode(["error" => "Reserva no encontrada"]);
        exit;
    }

    if (isset($input['nombre_huesped'])) $reserva->setNombreHuesped($input['nombre_huesped']);
    if (isset($input['email_huesped'])) $reserva->setEmailHuesped($input['email_huesped']);
    if (isset($input['nombre_alojamiento'])) $reserva->setNombreAlojamiento($input['nombre_alojamiento']);
    if (isset($input['fecha_entrada'])) $reserva->setFechaEntrada($input['fecha_entrada']);
    if (isset($input['fecha_salida'])) $reserva->setFechaSalida($input['fecha_salida']);
    if (isset($input['estado'])) $reserva->setEstado($input['estado']);
    if (isset($input['importe'])) $reserva->setImporte($input['importe']);
    if (isset($input['notas'])) $reserva->setNotas($input['notas']);

    $filas = ReservaDAO::update($reserva);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "mensaje" => "Reserva actualizada correctamente",
        "filas_afectadas" => $filas
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
