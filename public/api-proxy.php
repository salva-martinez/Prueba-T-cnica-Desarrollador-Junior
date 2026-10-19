<?php

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config.php';

function responderErrorProxy(
    int $codigo,
    string $mensaje
): void {
    http_response_code($codigo);

    echo json_encode([
        'status' => 'error',
        'message' => $mensaje,
        'data' => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (
    !defined('API_BASE_URL') ||
    !defined('API_TOKEN') ||
    API_BASE_URL === '' ||
    API_TOKEN === ''
) {
    error_log('El proxy no tiene configurada la URL o el token de la API.');

    responderErrorProxy(
        500,
        'La configuración de la API está incompleta.'
    );
}

$accion = $_GET['accion'] ?? '';
$metodoCliente = $_SERVER['REQUEST_METHOD'];

if ($accion === 'listar') {
    if ($metodoCliente !== 'GET') {
        header('Allow: GET');

        responderErrorProxy(
            405,
            'Para listar reservas debes utilizar GET.'
        );
    }

    $url = rtrim(API_BASE_URL, '/') . '/getReservas.php';
    $metodoAPI = 'GET';

} elseif ($accion === 'crear') {
    if ($metodoCliente !== 'POST') {
        header('Allow: POST');

        responderErrorProxy(
            405,
            'Para crear una reserva debes utilizar POST.'
        );
    }

    $url = rtrim(API_BASE_URL, '/') . '/postReserva.php';
    $metodoAPI = 'POST';

} elseif ($accion === 'cancelar' || $accion === 'confirmar') {
    if ($metodoCliente !== 'POST') {
        header('Allow: POST');

        responderErrorProxy(
            405,
            'Para cambiar el estado de una reserva debes utilizar POST.'
        );
    }

    $url = rtrim(API_BASE_URL, '/') . '/putReservas.php';
    $metodoAPI = 'POST';

} else {
    responderErrorProxy(
        400,
        'La acción solicitada no es válida.'
    );
}

$cabeceras = [
    'Authorization: Bearer ' . API_TOKEN,
    'Accept: application/json'
];

$opciones = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $cabeceras,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 15
];

if ($metodoAPI === 'POST') {
    $datos = file_get_contents('php://input');

    $cabeceras[] = 'Content-Type: application/json';

    $opciones[CURLOPT_HTTPHEADER] = $cabeceras;
    $opciones[CURLOPT_POST] = true;
    $opciones[CURLOPT_POSTFIELDS] = $datos;
}

$curl = curl_init($url);

if ($curl === false) {
    responderErrorProxy(
        500,
        'No se pudo preparar la petición a la API.'
    );
}

curl_setopt_array($curl, $opciones);

$respuesta = curl_exec($curl);
$codigoHTTP = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($respuesta === false) {
    $errorCurl = curl_error($curl);
    $numeroError = curl_errno($curl);

    curl_close($curl);

    error_log(
        "Error cURL en api-proxy.php ($numeroError): $errorCurl"
    );

    responderErrorProxy(
        502,
        'No se ha podido conectar con la API.'
    );
}

curl_close($curl);

try {
    json_decode(
        $respuesta,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $e) {
    error_log(
        'La API devolvió una respuesta que no es JSON válido. HTTP: '
        . $codigoHTTP
    );

    responderErrorProxy(
        502,
        'La API ha devuelto una respuesta no válida.'
    );
}

http_response_code($codigoHTTP);
echo $respuesta;
