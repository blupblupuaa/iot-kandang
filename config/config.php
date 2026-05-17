<?php

// Situs
define('SITE_NAME',    'KandangSmart');
define('SITE_DESC',    'Sistem Monitoring & Kontrol IoT Kandang Ayam');
define('SITE_VERSION', '1.0.0');
define('BASE_PATH', '/iot-kandang'); // ubah ke '' jika deploy di root domain

// ESP32
define('ESP32_IP',      '192.168.18.38');
define('ESP32_PORT',    '80');
define('ESP32_BASE_URL', 'http://' . ESP32_IP . ':' . ESP32_PORT);
define('ESP32_TIMEOUT', 3);

define('API_SENSOR',    ESP32_BASE_URL . '/api/sensor');
define('API_CONTROL',   ESP32_BASE_URL . '/api/control');
define('API_STATUS',    ESP32_BASE_URL . '/api/status');

// Threshold default (override lewat config/threshold.json)
define('THRESHOLD_SUHU_MIN',     18);
define('THRESHOLD_SUHU_MAX',     28);
define('THRESHOLD_HUMIDITY_MIN', 50);
define('THRESHOLD_HUMIDITY_MAX', 70);
define('THRESHOLD_AMONIA_MAX',   25);
define('THRESHOLD_CAHAYA_MIN',   200);
define('THRESHOLD_CAHAYA_MAX',   800);
define('THRESHOLD_PAKAN_MIN',    500);

define('USE_DUMMY_DATA', false);      // true = data simulasi, false = baca ESP32
define('REFRESH_INTERVAL', 5000);    // interval polling monitor (ms)
