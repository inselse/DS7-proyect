<?php

require_once __DIR__ . '/../controllers/AuthController.php';
AuthController::verificarSesion();

require_once __DIR__ . '/../controllers/MantenimientoController.php';

$baseUrl = '/DS7-proyect';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $baseUrl . '/mantenimientos/lista.php');
    exit;
}

$mantCtrl = new MantenimientoController();
$actualizado = $mantCtrl->actualizarEstado($_POST);

$registroId = (int)($_POST['registro_id'] ?? 0);
$redirect = $baseUrl . '/mantenimientos/lista.php';

if ($registroId > 0) {
    $redirect .= '?registro_id=' . $registroId;
}

$redirect .= ($actualizado ? '&exito=1' : '&error=1');

header('Location: ' . $redirect);
exit;
