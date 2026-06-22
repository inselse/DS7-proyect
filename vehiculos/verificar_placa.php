<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$placa = trim($_POST['placa'] ?? '');

if (empty($placa)) {
    http_response_code(400);
    echo json_encode(['error' => 'Placa requerida']);
    exit;
}

require_once __DIR__ . '/../controllers/VehiculoController.php';

$ctrl = new VehiculoController();
$info = $ctrl->obtenerConEstado($placa);

echo json_encode($info);
