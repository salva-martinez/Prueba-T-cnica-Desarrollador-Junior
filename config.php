<?php

$rutaEnv = __DIR__ . '/.env';

if (is_readable($rutaEnv)) {
    $lineas = file(
        $rutaEnv,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        $partes = explode('=', $linea, 2);

        if (count($partes) !== 2) {
            continue;
        }

        $clave = trim($partes[0]);
        $valor = trim($partes[1]);

        if (
            strlen($valor) >= 2 &&
            (
                ($valor[0] === '"' && str_ends_with($valor, '"')) ||
                ($valor[0] === "'" && str_ends_with($valor, "'"))
            )
        ) {
            $valor = substr($valor, 1, -1);
        }

        if (getenv($clave) === false) {
            putenv("$clave=$valor");
        }

        $_ENV[$clave] ??= getenv($clave);
    }
}

define('API_BASE_URL', getenv('API_BASE_URL') ?: '');
define('API_TOKEN', getenv('API_TOKEN') ?: '');
