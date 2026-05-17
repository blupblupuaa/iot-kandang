<?php
// =============================================================
//  HALAMAN KONTROL (control.php)
// =============================================================
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Kontrol Perangkat';
$active_nav = 'control';

$aktuator = get_actuator_status();
$t        = get_thresholds(); // ambil threshold dinamis

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">

    <div class="page-header">
        <h1 class="page-title">◎ Kontrol Perangkat</h1>
        <p class="page-subtitle">Kendalikan aktuator kandang secara manual</p>
    </div>

    <?php if (USE_DUMMY_DATA): ?>
    <div class="alert alert-info" style="margin-bottom:1.5rem;">
        ℹ️ Mode Demo aktif. Toggle akan berubah di UI tapi tidak mengirim perintah nyata ke ESP32.
    </div>
    <?php endif; ?>

    <!-- ===== KONTROL AKTUATOR ===== -->
    <p class="section-title">Aktuator</p>
    <div class="control-grid" style="margin-bottom: 2rem;">

        <!-- Kipas -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-icon">🌀</span>
                <div>
                    <div class="control-name">Kipas Pendingin</div>
                    <div class="control-desc">Ventilasi & pendingin kandang</div>
                </div>
            </div>
            <div class="toggle-wrapper">
                <span class="toggle-state <?= $aktuator['kipas'] ? 'on' : 'off' ?>">
                    <?= $aktuator['kipas'] ? 'ON' : 'OFF' ?>
                </span>
                <label class="toggle-switch">
                    <input type="checkbox"
                        <?= $aktuator['kipas'] ? 'checked' : '' ?>
                        onchange="sendControl('kipas', this.checked, this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Lampu -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-icon">💡</span>
                <div>
                    <div class="control-name">Lampu Kandang</div>
                    <div class="control-desc">Pencahayaan tambahan kandang</div>
                </div>
            </div>
            <div class="toggle-wrapper">
                <span class="toggle-state <?= $aktuator['lampu'] ? 'on' : 'off' ?>">
                    <?= $aktuator['lampu'] ? 'ON' : 'OFF' ?>
                </span>
                <label class="toggle-switch">
                    <input type="checkbox"
                        <?= $aktuator['lampu'] ? 'checked' : '' ?>
                        onchange="sendControl('lampu', this.checked, this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Servo -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-icon">⚙️</span>
                <div>
                    <div class="control-name">Dispenser Pakan</div>
                    <div class="control-desc">Servo motor pengisi pakan otomatis</div>
                </div>
            </div>
            <div class="toggle-wrapper">
                <span class="toggle-state <?= $aktuator['servo'] ? 'on' : 'off' ?>">
                    <?= $aktuator['servo'] ? 'ON' : 'OFF' ?>
                </span>
                <label class="toggle-switch">
                    <input type="checkbox"
                        <?= $aktuator['servo'] ? 'checked' : '' ?>
                        onchange="sendControl('servo', this.checked, this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <button class="btn btn-outline" style="width:100%;"
                onclick="sendControl('servo_pulse', true, null)">
                ▷ Aktifkan Sekali (Pulse)
            </button>
        </div>

    </div>

    <!-- ===== PENGATURAN THRESHOLD ===== -->
    <p class="section-title">Pengaturan Threshold</p>
    <div class="card">
        <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1.5rem;">
            Atur batas nilai sensor. Sistem akan menampilkan peringatan jika nilai melampaui threshold.
        </p>

        <form id="thresholdForm" onsubmit="sendThreshold(event)">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">

                <div class="form-group">
                    <label class="form-label">Suhu Minimum</label>
                    <div class="input-group">
                        <input type="number" name="suhu_min" class="form-input"
                            value="<?= $t['suhu_min'] ?>" min="0" max="50" step="0.5">
                        <span class="input-suffix">°C</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Suhu Maksimum</label>
                    <div class="input-group">
                        <input type="number" name="suhu_max" class="form-input"
                            value="<?= $t['suhu_max'] ?>" min="0" max="50" step="0.5">
                        <span class="input-suffix">°C</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kelembaban Minimum</label>
                    <div class="input-group">
                        <input type="number" name="humidity_min" class="form-input"
                            value="<?= $t['humidity_min'] ?>" min="0" max="100">
                        <span class="input-suffix">%</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kelembaban Maksimum</label>
                    <div class="input-group">
                        <input type="number" name="humidity_max" class="form-input"
                            value="<?= $t['humidity_max'] ?>" min="0" max="100">
                        <span class="input-suffix">%</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Amonia Maksimum</label>
                    <div class="input-group">
                        <input type="number" name="amonia_max" class="form-input"
                            value="<?= $t['amonia_max'] ?>" min="0" max="200">
                        <span class="input-suffix">ppm</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cahaya Minimum</label>
                    <div class="input-group">
                        <input type="number" name="cahaya_min" class="form-input"
                            value="<?= $t['cahaya_min'] ?>" min="0">
                        <span class="input-suffix">lux</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cahaya Maksimum</label>
                    <div class="input-group">
                        <input type="number" name="cahaya_max" class="form-input"
                            value="<?= $t['cahaya_max'] ?>" min="0">
                        <span class="input-suffix">lux</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pakan Minimum</label>
                    <div class="input-group">
                        <input type="number" name="pakan_min" class="form-input"
                            value="<?= $t['pakan_min'] ?>" min="0">
                        <span class="input-suffix">gram</span>
                    </div>
                </div>

            </div>

            <div style="margin-top:1rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">💾 Simpan Threshold</button>
                <button type="reset" class="btn btn-outline">↺ Reset ke Default</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>