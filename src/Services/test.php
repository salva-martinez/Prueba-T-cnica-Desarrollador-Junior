<?php

// test.php
require_once './src/Controllers/ReservaDAO.php';
require_once './src/Controllers/ReservaEventoDAO.php';
require_once './src/Models/Reserva.php';
require_once './src/Models/ReservaEvento.php';


echo "=== 1. PROBANDO INSERTAR UNA NUEVA RESERVA (Debe registrar evento 'creada') ===\n";
$nuevaReserva = new Reserva();
$nuevaReserva->setNombreHuesped("Carlos Martí");
$nuevaReserva->setEmailHuesped("carlos.marti@example.com");
$nuevaReserva->setNombreAlojamiento("Cabaña de la Montaña");
$nuevaReserva->setFechaEntrada("2026-12-15");
$nuevaReserva->setFechaSalida("2026-12-20");
$nuevaReserva->setEstado("pendiente");
$nuevaReserva->setImporte(610.00);
$nuevaReserva->setNotas("Solicita leña para la chimenea");

$idInsertado = ReservaDAO::insert($nuevaReserva);
echo "Reserva insertada con ID: " . $idInsertado . "\n\n";

if ($idInsertado > 0) {
    echo "=== 2. PROBANDO SELECT POR ID ($idInsertado) ===\n";
    $reservaUnica = ReservaDAO::select($idInsertado);
    if ($reservaUnica) {
        echo "Encontrado -> Huésped: " . $reservaUnica->getNombreHuesped() . " | Alojamiento: " . $reservaUnica->getNombreAlojamiento() . " | Estado: " . $reservaUnica->getEstado() . "\n\n";

        echo "=== 3. PROBANDO UPDATE (Cambio de estado a 'confirmada' - Debe registrar evento) ===\n";
        $reservaUnica->setEstado("confirmada");
        $reservaUnica->setImporte(630.00);
        $reservaUnica->setNotas("Leña confirmada y pago recibido");
        
        $filasAfectadas = ReservaDAO::update($reservaUnica);
        echo "Filas actualizadas en reserva: " . $filasAfectadas . "\n\n";
    }
}

echo "=== 4. PROBANDO GETALL SIN FILTROS ===\n";
$todas = ReservaDAO::getAll();
echo "Total de reservas en la BD: " . count($todas) . "\n\n";

echo "=== 5. PROBANDO GETALL CON FILTROS (Ej: Estado 'confirmada') ===\n";
$filtradasEstado = ReservaDAO::getAll(estado: 'confirmada');
echo "Reservas confirmadas encontradas: " . count($filtradasEstado) . "\n";
foreach ($filtradasEstado as $r) {
    echo " - ID: " . $r->getId() . " | " . $r->getNombreHuesped() . " (" . $r->getEstado() . ")\n";
}

echo "\n=== 6. PROBANDO GETALL CON BÚSQUEDA POR NOMBRE ===\n";
$filtradasNombre = ReservaDAO::getAll(nombreHuesped: 'Carlos');
echo "Reservas que coinciden con 'Carlos': " . count($filtradasNombre) . "\n";
foreach ($filtradasNombre as $r) {
    echo " - ID: " . $r->getId() . " | " . $r->getNombreHuesped() . " | " . $r->getNombreAlojamiento() . "\n";
}