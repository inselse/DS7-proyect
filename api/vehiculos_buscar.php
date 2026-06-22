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
require_once __DIR__ . '/../clases/Vehiculo.php';

$termino = $_GET['q'] ?? '';

if (strlen($termino) < 1) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $vehiculos = Vehiculo::buscar($termino);
    $data = [];

    foreach ($vehiculos as $v) {
        $data[] = [
            'id'           => $v->getId(),
            'placa'        => $v->getPlaca(),
            'marca'        => $v->getMarca(),
            'modelo'       => $v->getModelo(),
            'anio'         => $v->getAnio(),
            'color'        => $v->getColor(),
            'dueno_nombre' => $v->getDuenoNombre(),
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    error_log('Error en API vehiculos_buscar: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar vehiculos']);
}
