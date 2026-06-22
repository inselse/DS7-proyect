<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>(nombre) - <?php echo htmlspecialchars($tituloPagina ?? 'Panel de Control'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/main.css">
    <?php if (isset($cssExtra)): ?>
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/<?php echo htmlspecialchars($cssExtra); ?>">
    <?php endif; ?>
</head>
<body>
    <div id="bloqueo-movil">
        <i class="fas fa-rotate-left" style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.6;"></i>
        <h2>Acceso no disponible</h2>
        <p>Este sistema solo esta disponible en tablets, laptops y equipos de escritorio.</p>
        <p style="font-size: 0.85rem; opacity: 0.6; margin-top: 1rem;">Gire su dispositivo horizontalmente o acceda desde una pantalla mas grande.</p>
    </div>
