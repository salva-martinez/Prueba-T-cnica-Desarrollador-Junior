<?php

require_once __DIR__ . '/../../config.php';

function verificarApiKey(): void
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($authHeader === '' && function_exists('getallheaders')) {
        $headers = getallheaders();

        foreach ($headers as $nombre => $valor) {
            if (strcasecmp($nombre, 'Authorization') === 0) {
                $authHeader = $valor;
                break;
            }
        }
    }

    if (
        !preg_match('/^Bearer\s+(\S+)$/i', trim($authHeader), $matches)
        || !defined('API_TOKEN')
        || !hash_equals(API_TOKEN, $matches[1])
    ) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'error',
            'message' => 'No autorizado. Token inválido o ausente.',
            'data' => null
        ]);

        exit;
    }
}
