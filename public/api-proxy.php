
<?php

// Responder siempre con JSON.
header('Content-Type: application/json; charset=utf-8');

// Cargar la configuración desde la raíz del proyecto.
require_once dirname(__DIR__) . '/config.php';

// Por ahora, solo permitimos consultar las reservas.
$accion = $_GET['accion'] ?? '';

if ($accion !== 'listar') {
    http_response_code(400);
    echo json_encode([
        'error' => 'La acción solicitada no es válida.'
    ]);
    exit;
}

// Construir la URL del archivo permitido.
$url = rtrim(API_BASE_URL, '/') . '/getReservas.php';

// Preparar la petición HTTP.
$curl = curl_init($url);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . API_TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 15
]);

// Ejecutar la petición.
$respuesta = curl_exec($curl);
$codigoHTTP = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Comprobar errores de conexión y mostrar el motivo real.
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

// Devolver el estado HTTP de la API.
http_response_code($codigoHTTP);
echo $respuesta;
