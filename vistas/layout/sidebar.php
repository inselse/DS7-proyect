<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-car-side"></i>
        <span class="sidebar-logo-text">(nombre)</span>
    </div>

    <nav class="sidebar-nav">
        <?php $url = $baseUrl ?? ''; ?>
        <a href="<?php echo $url; ?>/dashboard.php" class="sidebar-item <?php echo $seccionActiva === 'dashboard' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'dashboard' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-chart-pie"></i>
            <span class="sidebar-label">Panel de Control</span>
        </a>

        <a href="<?php echo $url; ?>/vehiculos/registro.php" class="sidebar-item <?php echo $seccionActiva === 'registro-vehiculo' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'registro-vehiculo' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-plus-circle"></i>
            <span class="sidebar-label">Registrar Vehiculo</span>
        </a>

        <a href="<?php echo $url; ?>/vehiculos/lista.php" class="sidebar-item <?php echo $seccionActiva === 'lista-vehiculos' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'lista-vehiculos' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-truck"></i>
            <span class="sidebar-label">Vehiculos</span>
        </a>

        <a href="<?php echo $url; ?>/cubiculos/panel.php" class="sidebar-item <?php echo $seccionActiva === 'cubiculos' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'cubiculos' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-warehouse"></i>
            <span class="sidebar-label">Cubiculos</span>
        </a>

        <a href="<?php echo $url; ?>/mantenimientos/lista.php" class="sidebar-item <?php echo $seccionActiva === 'mantenimientos' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'mantenimientos' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-tools"></i>
            <span class="sidebar-label">Mantenimientos</span>
        </a>

        <a href="<?php echo $url; ?>/reportes/informe.php" class="sidebar-item <?php echo $seccionActiva === 'reportes' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'reportes' ? 'aria-current="page"' : ''; ?>>
            <i class="fas fa-file-alt"></i>
            <span class="sidebar-label">Informe / Reporte</span>
        </a>

        <div class="sidebar-divider"></div>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <div class="sidebar-divider"></div>
            <a href="<?php echo $url; ?>/usuarios/lista.php" class="sidebar-item <?php echo $seccionActiva === 'usuarios' ? 'activo' : ''; ?>" <?php echo $seccionActiva === 'usuarios' ? 'aria-current="page"' : ''; ?>>
                <i class="fas fa-users-cog"></i>
                <span class="sidebar-label">Usuarios</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo $url; ?>/logout.php" class="sidebar-item">
            <i class="fas fa-right-from-bracket"></i>
            <span class="sidebar-label">Cerrar Sesion</span>
        </a>
    </nav>
</aside>
