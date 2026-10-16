<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../src/Middleware/auth.php';
require_once __DIR__ . '/../src/Controllers/ReservaDAO.php';
require_once __DIR__ . '/../src/Models/Reserva.php';
require_once __DIR__ . '/../src/Services/ReservaValidator.php';
verificarApiKey();