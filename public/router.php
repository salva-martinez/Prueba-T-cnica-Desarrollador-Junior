<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$raizPublica = realpath(__DIR__);
$archivo = realpath(__DIR__ . $path);

if (
    $path !== '/' &&
    $raizPublica !== false &&
    $archivo !== false &&
    str_starts_with($archivo, $raizPublica . DIRECTORY_SEPARATOR) &&
    is_file($archivo)
) {
    return false;
}

require __DIR__ . '/index.php';
