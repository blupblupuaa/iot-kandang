<?php
// =============================================================
//  HALAMAN MONITOR (monitor.php)
// =============================================================
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Monitor Sensor';
$active_nav = 'monitor';

// Ambil data sensor
$sensor = get_sensor_data();

// Ambil threshold dinamis
$t = get_thresholds();

// Hitung status tiap sensor
$status = [
    'suhu'     => sensor_status('suhu',     $sensor['suhu']     ?? null),
    'humidity' => sensor_status('humidity', $sensor['humidity'] ?? null),
    'amonia'   => sensor_status('amonia',   $sensor['amonia']   ?? null),
    'cahaya'   => sensor_status('cahaya',   $sensor['cahaya']   ?? null),
    'pakan'    => sensor_status('pakan',    $sensor['pakan']    ?? null),
];

// Label status
$status_label = ['normal' => 'Normal', 'warning' => 'Perhatian', 'danger' => 'Bahaya'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 class="page-title">◈ Monitor Sensor</h1>
            <p class="page-subtitle">Data real-time dari semua sensor kandang</p>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <div class="refresh-badge">
                <span class="refresh-dot"></span>
                Update berikutnya dalam <span id="refreshCounter"><?= REFRESH_INTERVAL/1000 ?>s</span>
            </div>
            <span class="timestamp" id="sensorTimestamp">Update: <?= $sensor['timestamp'] ?></span>

            <span id="sourceBadge" class="alert <?= $sensor['source'] === 'esp32' ? 'alert-success' : ($sensor['source'] === 'cached' ? 'alert-warning' : 'alert-info') ?>" style="padding:0.3rem 0.8rem; margin:0; font-size:0.75rem;">
                <?php if ($sensor['source'] === 'esp32'): ?>
                ● Live ESP32
                <?php elseif ($sensor['source'] === 'cached'): ?>
                ⚠️ ESP32 Offline — Data terakhir: <?= htmlspecialchars($sensor['saved_at'] ?? '-') ?>
                <?php else: ?>
                Mode Demo
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- ===== ALERT KONDISI ===== -->
    <div id="conditionAlerts">
    <?php foreach ($status as $key => $st): ?>
        <?php if ($st === 'danger'): ?>
        <div class="alert alert-danger">
            🚨 <strong><?= strtoupper($key) ?></strong> dalam kondisi BAHAYA! Segera periksa kandang.
        </div>
        <?php elseif ($st === 'warning'): ?>
        <div class="alert alert-warning">
            ⚠️ <strong><?= strtoupper($key) ?></strong> mendekati batas normal. Pantau terus.
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- ===== SENSOR CARDS ===== -->
    <p class="section-title">Pembacaan Sensor</p>
    <div class="sensor-grid" style="margin-bottom: 2rem;">

        <!-- Suhu -->
        <div class="sensor-card status-<?= $status['suhu'] ?>" data-sensor="suhu">
            <span class="sensor-icon">🌡️</span>
            <div class="sensor-label">Suhu (DHT22)</div>
            <div class="sensor-value">
                <?= $sensor['suhu'] ?? '—' ?><span class="sensor-unit">°C</span>
            </div>
            <div>
                <span class="sensor-badge badge-<?= $status['suhu'] ?>">
                    <?= $status_label[$status['suhu']] ?>
                </span>
            </div>
            <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Normal: <?= $t['suhu_min'] ?>–<?= $t['suhu_max'] ?>°C
            </div>
        </div>

        <!-- Kelembaban -->
        <div class="sensor-card status-<?= $status['humidity'] ?>" data-sensor="humidity">
            <span class="sensor-icon">💧</span>
            <div class="sensor-label">Kelembaban (DHT22)</div>
            <div class="sensor-value">
                <?= $sensor['humidity'] ?? '—' ?><span class="sensor-unit">%</span>
            </div>
            <div>
                <span class="sensor-badge badge-<?= $status['humidity'] ?>">
                    <?= $status_label[$status['humidity']] ?>
                </span>
            </div>
            <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Normal: <?= $t['humidity_min'] ?>–<?= $t['humidity_max'] ?>%
            </div>
        </div>

        <!-- Amonia -->
        <div class="sensor-card status-<?= $status['amonia'] ?>" data-sensor="amonia">
            <span class="sensor-icon">☁️</span>
            <div class="sensor-label">Amonia (MQ-135)</div>
            <div class="sensor-value">
                <?= $sensor['amonia'] ?? '—' ?><span class="sensor-unit">ppm</span>
            </div>
            <div>
                <span class="sensor-badge badge-<?= $status['amonia'] ?>">
                    <?= $status_label[$status['amonia']] ?>
                </span>
            </div>
            <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Maks normal: <?= $t['amonia_max'] ?> ppm
            </div>
        </div>

        <!-- Cahaya TSL2591 -->
        <div class="sensor-card status-<?= $status['cahaya'] ?>" data-sensor="cahaya">
            <span class="sensor-icon">🔆</span>
            <div class="sensor-label">Cahaya (TSL2591)</div>
            <div class="sensor-value">
                <?= $sensor['cahaya'] ?? '—' ?><span class="sensor-unit">lux</span>
            </div>
            <div>
                <span class="sensor-badge badge-<?= $status['cahaya'] ?>">
                    <?= $status_label[$status['cahaya']] ?>
                </span>
            </div>
            <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Normal: <?= $t['cahaya_min'] ?>–<?= $t['cahaya_max'] ?> lux
            </div>
        </div>

        <!-- Pakan -->
        <div class="sensor-card status-<?= $status['pakan'] ?>" data-sensor="pakan">
            <span class="sensor-icon">🌾</span>
            <div class="sensor-label">Pakan (HX711)</div>
            <div class="sensor-value">
                <?= $sensor['pakan'] ?? '—' ?><span class="sensor-unit"><?= $sensor['pakan'] !== null ? 'g' : '' ?></span>
            </div>
            <div>
                <span class="sensor-badge badge-<?= $status['pakan'] ?>">
                    <?= $sensor['pakan'] !== null ? $status_label[$status['pakan']] : 'Belum terpasang' ?>
                </span>
            </div>
            <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Min. pakan: <?= $t['pakan_min'] ?> g
            </div>
        </div>

    </div>

    <!-- ===== TABEL HISTORIS ===== -->
    <p class="section-title">Riwayat Pembacaan (Sesi Ini)</p>
    <div class="card" style="overflow-x:auto;">
        <table id="historyTable" style="width:100%; border-collapse:collapse; font-family:var(--font-mono); font-size:0.82rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border); color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em;">
                    <th style="padding:0.6rem 0.75rem; text-align:left;">Waktu</th>
                    <th style="padding:0.6rem 0.75rem; text-align:right;">Suhu (°C)</th>
                    <th style="padding:0.6rem 0.75rem; text-align:right;">Lembab (%)</th>
                    <th style="padding:0.6rem 0.75rem; text-align:right;">Amonia (ppm)</th>
                    <th style="padding:0.6rem 0.75rem; text-align:right;">Cahaya (lux)</th>
                    <th style="padding:0.6rem 0.75rem; text-align:right;">Pakan (g)</th>
                </tr>
            </thead>
            <tbody id="historyBody"></tbody>
        </table>
    </div>

</div>

<?php ob_start(); ?>
<script>
const HISTORY_KEY = 'kandang_history';
const maxHistory  = 10;

function getHistory() {
    try { return JSON.parse(sessionStorage.getItem(HISTORY_KEY)) || []; }
    catch { return []; }
}

function saveHistory(rows) {
    sessionStorage.setItem(HISTORY_KEY, JSON.stringify(rows.slice(-maxHistory)));
}

function renderHistory() {
    const rows  = getHistory();
    const tbody = document.getElementById('historyBody');
    if (!tbody) return;

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="padding:1.5rem; text-align:center; color:var(--text-muted);">Belum ada data historis di sesi ini.</td></tr>`;
        return;
    }

    tbody.innerHTML = rows.slice().reverse().map(r => `
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:0.5rem 0.75rem; color:var(--text-secondary); font-family:var(--font-mono);">${r.timestamp}</td>
            <td style="padding:0.5rem 0.75rem; text-align:right;">${r.suhu ?? '—'}</td>
            <td style="padding:0.5rem 0.75rem; text-align:right;">${r.humidity ?? '—'}</td>
            <td style="padding:0.5rem 0.75rem; text-align:right;">${r.amonia ?? '—'}</td>
            <td style="padding:0.5rem 0.75rem; text-align:right;">${r.cahaya ?? '—'}</td>
            <td style="padding:0.5rem 0.75rem; text-align:right;">${r.pakan ?? '—'}</td>
        </tr>
    `).join('');
}

// Tambah data saat ini ke histori
const currentData = {
    timestamp: new Date().toLocaleTimeString('id-ID', {
                   hour: '2-digit', minute: '2-digit', second: '2-digit',
                   hour12: false
               }),
    suhu:      <?= json_encode($sensor['suhu']     ?? null) ?>,
    humidity:  <?= json_encode($sensor['humidity'] ?? null) ?>,
    amonia:    <?= json_encode($sensor['amonia']   ?? null) ?>,
    cahaya:    <?= json_encode($sensor['cahaya']   ?? null) ?>,
    pakan:     <?= json_encode($sensor['pakan']    ?? null) ?>,
};

const history = getHistory();
history.push(currentData);
saveHistory(history);
renderHistory();

function appendMonitorHistory(data) {
    const row = {
        timestamp: new Date().toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
        }),
        suhu:     data.suhu     ?? null,
        humidity: data.humidity ?? null,
        amonia:   data.amonia   ?? null,
        cahaya:   data.cahaya   ?? null,
        pakan:    data.pakan    ?? null,
    };
    const history = getHistory();
    history.push(row);
    saveHistory(history);
    renderHistory();
}

startSensorPolling(<?= REFRESH_INTERVAL ?>);
</script>
<?php
$page_script = ob_get_clean();
require_once __DIR__ . '/includes/footer.php';
?>