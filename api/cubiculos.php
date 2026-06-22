<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../clases/Cubiculo.php';
require_once __DIR__ . '/../clases/Mantenimiento.php';

try {
    $cubiculos = Cubiculo::obtenerEstadoCompleto();
    echo json_encode([
        'success'  => true,
        'timestamp'=> time(),
        'data'     => $cubiculos,
    ]);
} catch (Exception $e) {
    error_log('Error en API cubiculos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener el estado de cubiculos']);
}
