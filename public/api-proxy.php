
<?php

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config.php';

$accion = $_GET['accion'] ?? '';

if ($accion === 'listar') {
    $url = rtrim(API_BASE_URL, '/') . '/getReservas.php';
    $metodo = 'GET';

} elseif ($accion === 'crear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'error' => 'Para crear una reserva debes utilizar POST.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $url = rtrim(API_BASE_URL, '/') . '/postReserva.php';
    $metodo = 'POST';

} else {
    http_response_code(400);
    echo json_encode([
        'error' => 'La acción solicitada no es válida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$cabeceras = [
    'Authorization: Bearer ' . API_TOKEN,
    'Accept: application/json'
];

$opciones = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $cabeceras,
    CURLOPT_TIMEOUT => 15
];

if ($metodo === 'POST') {
    $datos = file_get_contents('php://input');

    $cabeceras[] = 'Content-Type: application/json';

    $opciones[CURLOPT_HTTPHEADER] = $cabeceras;
    $opciones[CURLOPT_POST] = true;
    $opciones[CURLOPT_POSTFIELDS] = $datos;
}

$curl = curl_init($url);
curl_setopt_array($curl, $opciones);

$respuesta = curl_exec($curl);
$codigoHTTP = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($respuesta === false) {
    $errorCurl = curl_error($curl);
    $numeroError = curl_errno($curl);

    curl_close($curl);

    http_response_code(502);

    echo json_encode([
        'error' => 'No se ha podido conectar con la API.',
        'codigo_curl' => $numeroError,
        'detalle' => $errorCurl
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

curl_close($curl);

http_response_code($codigoHTTP);
echo $respuesta;
