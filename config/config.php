<?php
// =============================================================
//  KONFIGURASI SISTEM IoT KANDANG AYAM
// =============================================================

// --- Informasi Sistem ---
define('SITE_NAME',    'KandangSmart');
define('SITE_DESC',    'Sistem Monitoring & Kontrol IoT Kandang Ayam');
define('SITE_VERSION', '1.0.0');

// --- Base path web (tanpa trailing slash) ---
// Laragon subfolder: '/iot-kandang' | root domain: ''
define('BASE_PATH', '/iot-kandang');

// --- Koneksi ke ESP32 ---
// Ganti dengan IP ESP32 kalian (bisa IP lokal atau domain)
define('ESP32_IP',      '192.168.18.38');
define('ESP32_PORT',    '80');
define('ESP32_BASE_URL', 'http://' . ESP32_IP . ':' . ESP32_PORT);
define('ESP32_TIMEOUT', 3); // detik

// --- Endpoint API ESP32 ---
// Sesuaikan dengan endpoint yang dibuat di firmware ESP32
define('API_SENSOR',    ESP32_BASE_URL . '/api/sensor');   // GET  - baca data sensor
define('API_CONTROL',   ESP32_BASE_URL . '/api/control');  // POST - kirim perintah kontrol
define('API_STATUS',    ESP32_BASE_URL . '/api/status');   // GET  - status aktuator

// --- Threshold Sensor (nilai batas normal) ---
define('THRESHOLD_SUHU_MIN',     18);   // °C
define('THRESHOLD_SUHU_MAX',     28);   // °C
define('THRESHOLD_HUMIDITY_MIN', 50);   // %
define('THRESHOLD_HUMIDITY_MAX', 70);   // %
define('THRESHOLD_AMONIA_MAX',   25);   // ppm
define('THRESHOLD_CAHAYA_MIN',   200);  // lux
define('THRESHOLD_CAHAYA_MAX',   800);  // lux
define('THRESHOLD_PAKAN_MIN',    500);  // gram

// --- Mode Data (ubah ke false jika ESP32 sudah terhubung) ---
define('USE_DUMMY_DATA', false);

// --- Auto-refresh interval halaman monitor (milidetik) ---
define('REFRESH_INTERVAL', 5000); // 5 detik
