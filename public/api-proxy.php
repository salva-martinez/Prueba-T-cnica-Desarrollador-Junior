
<?php

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config.php';

$accion = $_GET['accion'] ?? '';

if ($accion !== 'listar') {
    http_response_code(400);
    echo json_encode([
        'error' => 'La acción solicitada no es válida.'
    ]);
    exit;
}

$url = rtrim(API_BASE_URL, '/') . '/getReservas.php';

$curl = curl_init($url);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . API_TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 15
]);

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

http_response_code($codigoHTTP);
echo $respuesta;
