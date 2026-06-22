    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/main.js"></script>
    <?php if (isset($jsExtra)): ?>
        <?php foreach ((array)$jsExtra as $js): ?>
            <script src="<?php echo $baseUrl; ?>/assets/js/<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
