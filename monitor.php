<?php
// ============================================================
//  HALAMAN MONITOR (monitor.php)
// ============================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$page_title = 'Monitor Sensor';
$active_nav = 'monitor';

$sensor = get_sensor_data();
$t      = get_thresholds();

$status = [
    'suhu'     => sensor_status('suhu',     $sensor['suhu']     ?? null),
    'humidity' => sensor_status('humidity', $sensor['humidity'] ?? null),
    'amonia'   => sensor_status('amonia',   $sensor['amonia']   ?? null),
    'cahaya'   => sensor_status('cahaya',   $sensor['cahaya']   ?? null),
    'pakan'    => sensor_status('pakan',    $sensor['pakan']    ?? null),
];

$status_label = ['normal' => 'Normal', 'warning' => 'Perhatian', 'danger' => 'Bahaya'];

// Ambil riwayat dari database
$riwayat = db_get_sensor_log(20);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">

    <!-- Header -->
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 class="page-title">◈ Monitor Sensor</h1>
            <p class="page-subtitle">Data real-time dari semua sensor kandang</p>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <div class="refresh-badge">
                <span class="refresh-dot"></span>
                Live
            </div>
            <span class="timestamp" id="sensorTimestamp">Update: <?= $sensor['timestamp'] ?></span>
            <span id="sourceBadge" class="alert <?= $sensor['source'] === 'esp32' ? 'alert-success' : ($sensor['source'] === 'cached' ? 'alert-warning' : 'alert-info') ?>"
                  style="padding:0.3rem 0.8rem; margin:0; font-size:0.75rem;">
                <?php if ($sensor['source'] === 'esp32'): ?>● Live ESP32
                <?php elseif ($sensor['source'] === 'cached'): ?>⚠️ Offline — <?= htmlspecialchars($sensor['saved_at'] ?? '-') ?>
                <?php else: ?>Mode Demo<?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Alert kondisi -->
    <div id="conditionAlerts">
    <?php foreach ($status as $key => $st): ?>
        <?php if ($st === 'danger'): ?>
        <div class="alert alert-danger">🚨 <strong><?= strtoupper($key) ?></strong> dalam kondisi BAHAYA!</div>
        <?php elseif ($st === 'warning'): ?>
        <div class="alert alert-warning">⚠️ <strong><?= strtoupper($key) ?></strong> mendekati batas normal.</div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- Kartu Sensor -->
    <p class="section-title">Pembacaan Sensor</p>
    <div class="sensor-grid" style="margin-bottom:2rem;">

        <div class="sensor-card status-<?= $status['suhu'] ?>" data-sensor="suhu">
            <span class="sensor-icon">🌡️</span>
            <div class="sensor-label">Suhu (DHT22)</div>
            <div class="sensor-value"><?= $sensor['suhu'] ?? '—' ?><span class="sensor-unit">°C</span></div>
            <span class="sensor-badge badge-<?= $status['suhu'] ?>"><?= $status_label[$status['suhu']] ?></span>
            <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--text-muted);font-family:var(--font-mono);">
                Normal: <?= $t['suhu_min'] ?>–<?= $t['suhu_max'] ?>°C
            </div>
        </div>

        <div class="sensor-card status-<?= $status['humidity'] ?>" data-sensor="humidity">
            <span class="sensor-icon">💧</span>
            <div class="sensor-label">Kelembaban (DHT22)</div>
            <div class="sensor-value"><?= $sensor['humidity'] ?? '—' ?><span class="sensor-unit">%</span></div>
            <span class="sensor-badge badge-<?= $status['humidity'] ?>"><?= $status_label[$status['humidity']] ?></span>
            <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--text-muted);font-family:var(--font-mono);">
                Normal: <?= $t['humidity_min'] ?>–<?= $t['humidity_max'] ?>%
            </div>
        </div>

        <div class="sensor-card status-<?= $status['amonia'] ?>" data-sensor="amonia">
            <span class="sensor-icon">☁️</span>
            <div class="sensor-label">Amonia (MQ-135)</div>
            <div class="sensor-value"><?= $sensor['amonia'] ?? '—' ?><span class="sensor-unit">ppm</span></div>
            <span class="sensor-badge badge-<?= $status['amonia'] ?>"><?= $status_label[$status['amonia']] ?></span>
            <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--text-muted);font-family:var(--font-mono);">
                Maks: <?= $t['amonia_max'] ?> ppm
            </div>
        </div>

        <div class="sensor-card status-<?= $status['cahaya'] ?>" data-sensor="cahaya">
            <span class="sensor-icon">🔆</span>
            <div class="sensor-label">Cahaya (TSL2591)</div>
            <div class="sensor-value"><?= $sensor['cahaya'] ?? '—' ?><span class="sensor-unit">lux</span></div>
            <span class="sensor-badge badge-<?= $status['cahaya'] ?>"><?= $status_label[$status['cahaya']] ?></span>
            <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--text-muted);font-family:var(--font-mono);">
                Normal: <?= $t['cahaya_min'] ?>–<?= $t['cahaya_max'] ?> lux
            </div>
        </div>

        <div class="sensor-card status-<?= $status['pakan'] ?>" data-sensor="pakan">
            <span class="sensor-icon">🌾</span>
            <div class="sensor-label">Pakan (HX711)</div>
            <div class="sensor-value"><?= $sensor['pakan'] ?? '—' ?><span class="sensor-unit"><?= $sensor['pakan'] !== null ? 'g' : '' ?></span></div>
            <span class="sensor-badge badge-<?= $status['pakan'] ?>">
                <?= $sensor['pakan'] !== null ? $status_label[$status['pakan']] : 'Belum terpasang' ?>
            </span>
            <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--text-muted);font-family:var(--font-mono);">
                Min: <?= $t['pakan_min'] ?> g
            </div>
        </div>

    </div>

    <!-- Riwayat dari Database -->
    <p class="section-title">Riwayat Pembacaan (Database)</p>
    <div class="card" style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-family:var(--font-mono);font-size:0.82rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">
                    <th style="padding:0.6rem 0.75rem;text-align:left;">Waktu</th>
                    <th style="padding:0.6rem 0.75rem;text-align:right;">Suhu (°C)</th>
                    <th style="padding:0.6rem 0.75rem;text-align:right;">Lembab (%)</th>
                    <th style="padding:0.6rem 0.75rem;text-align:right;">Amonia (ppm)</th>
                    <th style="padding:0.6rem 0.75rem;text-align:right;">Cahaya (lux)</th>
                    <th style="padding:0.6rem 0.75rem;text-align:right;">Pakan (g)</th>
                    <th style="padding:0.6rem 0.75rem;text-align:center;">Sumber</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat)): ?>
                <tr>
                    <td colspan="7" style="padding:1.5rem;text-align:center;color:var(--text-muted);">
                        Belum ada data tersimpan di database.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($riwayat as $row): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:0.5rem 0.75rem;color:var(--text-secondary);"><?= htmlspecialchars($row['waktu']) ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:right;"><?= $row['suhu']     ?? '—' ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:right;"><?= $row['humidity'] ?? '—' ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:right;"><?= $row['amonia']   ?? '—' ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:right;"><?= $row['cahaya']   ?? '—' ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:right;"><?= $row['pakan']    ?? '—' ?></td>
                    <td style="padding:0.5rem 0.75rem;text-align:center;">
                        <span style="font-size:0.7rem;padding:0.15rem 0.5rem;border-radius:999px;
                            background:<?= $row['source'] === 'esp32' ? 'var(--normal-dim)' : 'var(--accent-dim)' ?>;
                            color:<?= $row['source'] === 'esp32' ? 'var(--normal)' : 'var(--text-secondary)' ?>;">
                            <?= htmlspecialchars($row['source']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php ob_start(); ?>
<script>
function appendMonitorHistory(data) { 
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;

    // Hapus pesan kosong jika ada
    const emptyRow = tbody.querySelector('td[colspan="7"]');
    if (emptyRow) emptyRow.parentElement.remove();

    const now = new Date();
    const waktu = String(now.getDate()).padStart(2,'0') + '/' + 
                  String(now.getMonth()+1).padStart(2,'0') + ' ' +
                  String(now.getHours()).padStart(2,'0') + ':' +
                  String(now.getMinutes()).padStart(2,'0') + ':' +
                  String(now.getSeconds()).padStart(2,'0');

    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid var(--border)';
    tr.style.animation = 'fadeIn 0.5s ease';

    const sourceClass = data.source === 'esp32' ? 'var(--normal-dim)' : 'var(--accent-dim)';
    const sourceColor = data.source === 'esp32' ? 'var(--normal)' : 'var(--text-secondary)';

    tr.innerHTML = `
        <td style="padding:0.5rem 0.75rem;color:var(--text-secondary);">${waktu}</td>
        <td style="padding:0.5rem 0.75rem;text-align:right;">${data.suhu ?? '—'}</td>
        <td style="padding:0.5rem 0.75rem;text-align:right;">${data.humidity ?? '—'}</td>
        <td style="padding:0.5rem 0.75rem;text-align:right;">${data.amonia ?? '—'}</td>
        <td style="padding:0.5rem 0.75rem;text-align:right;">${data.cahaya ?? '—'}</td>
        <td style="padding:0.5rem 0.75rem;text-align:right;">${data.pakan ?? '—'}</td>
        <td style="padding:0.5rem 0.75rem;text-align:center;">
            <span style="font-size:0.7rem;padding:0.15rem 0.5rem;border-radius:999px;background:${sourceClass};color:${sourceColor};">${data.source}</span>
        </td>
    `;
    
    tbody.insertBefore(tr, tbody.firstChild);

    // Batasi maksimum 20 baris agar tabel tidak kepanjangan
    if (tbody.children.length > 20) {
        tbody.lastElementChild.remove();
    }
}

startSensorPolling(<?= REFRESH_INTERVAL ?>);

// Update timestamp real-time
setInterval(() => {
    const el = document.getElementById('sensorTimestamp');
    if (el) {
        const now = new Date();
        el.textContent = 'Update: ' +
            String(now.getHours()).padStart(2,'0') + ':' +
            String(now.getMinutes()).padStart(2,'0') + ':' +
            String(now.getSeconds()).padStart(2,'0');
    }
}, 1000);
</script>
<?php
$page_script = ob_get_clean();
require_once __DIR__ . '/includes/footer.php';
?>
