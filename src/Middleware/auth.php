<?php

function verificarApiKey()
{
    $headers = getallheaders();
    $tokenEsperado = "MI_TOKEN_SECRETO_123";

    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $tokenRecibido = $matches[1];
        if ($tokenRecibido === $tokenEsperado) {
            return true;
        }
    }

    http_response_code(401);
    echo json_encode([
        "error" => "No autorizado. Token inválido o ausente."
    ]);
    exit;
}
