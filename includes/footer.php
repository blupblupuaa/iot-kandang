<?php require_once __DIR__ . '/functions.php'; ?>
</main>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-left">
            <span class="brand-name">🐔 <?= SITE_NAME ?></span>
            <span class="footer-version">v<?= SITE_VERSION ?></span>
        </div>
        <div class="footer-right">
            <span>ESP32 · TSL2591 · DHT22 · MQ-135 · HX711</span>
        </div>
    </div>
</footer>
<script>window.APP_BASE = <?= json_encode(base_path()) ?>;</script>
<script src="<?= htmlspecialchars(asset('js/script.js')) ?>"></script>
<?php if (!empty($page_script)) echo $page_script; ?>
</body>
</html>
