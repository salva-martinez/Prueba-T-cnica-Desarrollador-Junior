
<?php

$rutaEnv = __DIR__ . '/.env';

if (!file_exists($rutaEnv)) {
    throw new RuntimeException('No se encuentra el archivo .env');
}

$lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$configuracion = [];

foreach ($lineas as $linea) {
   
    $linea = trim($linea);

    if ($linea === '' || str_starts_with($linea, '#')) {
        continue;
    }

    
    $partes = explode('=', $linea, 2);

    if (count($partes) === 2) {
        $nombre = trim($partes[0]);
        $valor = trim($partes[1]);

        // Quitar comillas si existen.
        $valor = trim($valor, "\"'");

        $configuracion[$nombre] = $valor;
    }
}

define('API_BASE_URL', $configuracion['API_BASE_URL'] ?? '');
define('API_TOKEN', $configuracion['API_TOKEN'] ?? '');

// Comprobar que las dos variables tienen valor.
if (API_BASE_URL === '' || API_TOKEN === '') {
    throw new RuntimeException('Falta configurar API_BASE_URL o API_TOKEN en .env');
}
