<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Beranda';
$active_nav = 'home';

$sensor  = get_sensor_data();
$aktuator = get_actuator_status();

$issues = 0;
foreach (['suhu','humidity','amonia','cahaya','pakan'] as $key) {
    if (sensor_status($key, $sensor[$key]) !== 'normal') $issues++;
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <span class="hero-tag">🐔 Prototype IoT — ESP32 + TSL2591</span>
    <h1 class="hero-title">
        Kandang Pintar<br>
        <span>Monitoring & Kontrol</span>
    </h1>
    <p class="hero-desc">
        Pantau kondisi kandang ayam secara real-time. Kelola suhu, cahaya,
        amonia, dan pakan dari satu dashboard terpusat.
    </p>
    <div class="hero-actions">
        <a href="<?= url('monitor.php') ?>" class="btn btn-primary">◈ Lihat Monitor</a>
        <a href="<?= url('control.php') ?>" class="btn btn-outline">◎ Kontrol Perangkat</a>
    </div>
</section>

<div class="page-container" style="padding-top: 0;">

    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-label">Suhu</div>
            <div class="stat-value"><?= $sensor['suhu'] ?>°C</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Kelembaban</div>
            <div class="stat-value"><?= $sensor['humidity'] ?>%</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Amonia</div>
            <div class="stat-value"><?= $sensor['amonia'] ?> <small style="font-size:0.8rem">ppm</small></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Cahaya</div>
            <div class="stat-value"><?= $sensor['cahaya'] ?> <small style="font-size:0.8rem">lux</small></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pakan</div>
            <div class="stat-value"><?= $sensor['pakan'] ?> <small style="font-size:0.8rem">g</small></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Peringatan</div>
            <div class="stat-value" style="color: <?= $issues > 0 ? 'var(--danger)' : 'var(--accent)' ?>">
                <?= $issues ?> <small style="font-size:0.8rem">isu</small>
            </div>
        </div>
    </div>

    <?php if ($issues > 0): ?>
    <div class="alert alert-warning" style="margin-bottom: 2rem;">
        ⚠️ Terdapat <strong><?= $issues ?> kondisi sensor</strong> yang memerlukan perhatian.
        <a href="<?= url('monitor.php') ?>" style="color: inherit; margin-left: 0.5rem; font-weight: 600;">Lihat detail →</a>
    </div>
    <?php endif; ?>

    <p class="section-title">Fitur Sistem</p>
</div>

<div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">🌡️</div>
        <div class="feature-title">Monitor Sensor Real-Time</div>
        <div class="feature-desc">
            Pantau suhu (DHT22), kelembaban, kadar amonia (MQ-135),
            intensitas cahaya (TSL2591), dan berat pakan (HX711) secara langsung.
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🔆</div>
        <div class="feature-title">Sensor TSL2591</div>
        <div class="feature-desc">
            Menggunakan sensor cahaya presisi tinggi TSL2591 via I2C dengan
            dynamic range hingga 600 juta:1 untuk akurasi pengukuran lux yang optimal.
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🎛️</div>
        <div class="feature-title">Kontrol Aktuator</div>
        <div class="feature-desc">
            Kendalikan kipas pendingin, lampu, dan servo dispenser pakan
            langsung dari browser — PC maupun smartphone.
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <div class="feature-title">Threshold Otomatis</div>
        <div class="feature-desc">
            Atur batas nilai sensor. Sistem akan memberi peringatan
            visual ketika kondisi kandang melampaui ambang batas yang ditentukan.
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">📱</div>
        <div class="feature-title">Responsive Design</div>
        <div class="feature-desc">
            Tampilan menyesuaikan semua ukuran layar — dari monitor desktop
            hingga smartphone, tanpa perlu aplikasi tambahan.
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🔌</div>
        <div class="feature-title">Siap Koneksi ESP32</div>
        <div class="feature-desc">
            Web terhubung ke ESP32 via HTTP REST API. Saat prototype selesai,
            cukup ubah <code style="font-family:var(--font-mono);font-size:0.8rem">USE_DUMMY_DATA</code>
            ke <code style="font-family:var(--font-mono);font-size:0.8rem">false</code> di config.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
