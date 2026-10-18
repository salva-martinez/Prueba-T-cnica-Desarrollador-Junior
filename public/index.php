<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Controllers/ReservaEventoDAO.php';

$loader = new \Twig\Loader\FilesystemLoader(
    __DIR__ . '/../src/Views'
);

$twig = new \Twig\Environment($loader, [
    'autoescape' => 'html',
    'cache' => false,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/public/index\.php$#', '/', $path);
$path = preg_replace('#^/public(?=/|$)#', '', $path);

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    exit('Método no permitido');
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];
}

elseif (preg_match('#^/reservas/([0-9]+)$#', $path, $matches)) {
    $id = (int) $matches[1];
} else {
    $id = null;
}

if ($id !== null) {
    $reserva = ReservaDAO::select($id);

    if (!$reserva) {
        http_response_code(404);
        echo $twig->render('reservas/show.html.twig', [
            'reserva' => null,
            'eventos' => [],
        ]);
        exit;
    }

    $eventos = ReservaEventoDAO::select($id);

    echo $twig->render('reservas/show.html.twig', [
        'reserva' => $reserva,
        'eventos' => $eventos,
    ]);
    exit;
}

if ($path === '/reservas/nueva') {
    echo $twig->render('reservas/new.html.twig');
    exit;
}


if ($path === '/' || $path === '/reservas') {
    $estado = $_GET['estado'] ?? null;
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    $nombre = $_GET['nombre'] ?? null;

    $reservas = ReservaDAO::getAll(
        $estado,
        $fechaInicio,
        $fechaFin,
        $nombre
    );

    echo $twig->render('reservas/index.html.twig', [
        'reservas' => $reservas,
        'filtros' => [
            'estado' => $estado,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'nombre' => $nombre,
        ],
    ]);
    exit;
}

http_response_code(404);
echo 'Página no encontrada';
