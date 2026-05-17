<?php
require_once __DIR__ . '/../config/config.php';

define('LAST_SENSOR_FILE', __DIR__ . '/../config/last_sensor.json');
define('THRESHOLD_FILE',   __DIR__ . '/../config/threshold.json');

// URL aplikasi & asset
function url(string $path = ''): string {
    $base = rtrim(BASE_PATH, '/');
    if ($path === '') {
        return $base !== '' ? $base . '/' : '/';
    }
    return ($base !== '' ? $base . '/' : '/') . ltrim($path, '/');
}

function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

function get_thresholds(): array {
    if (file_exists(THRESHOLD_FILE)) {
        $data = json_decode(file_get_contents(THRESHOLD_FILE), true);
        if ($data) return $data;
    }
    return [
        'suhu_min'     => THRESHOLD_SUHU_MIN,
        'suhu_max'     => THRESHOLD_SUHU_MAX,
        'humidity_min' => THRESHOLD_HUMIDITY_MIN,
        'humidity_max' => THRESHOLD_HUMIDITY_MAX,
        'amonia_max'   => THRESHOLD_AMONIA_MAX,
        'cahaya_min'   => THRESHOLD_CAHAYA_MIN,
        'cahaya_max'   => THRESHOLD_CAHAYA_MAX,
        'pakan_min'    => THRESHOLD_PAKAN_MIN,
    ];
}

function save_last_sensor(array $data): void {
    $data['saved_at'] = date('Y-m-d H:i:s');
    file_put_contents(LAST_SENSOR_FILE, json_encode($data));
}

function get_last_sensor(): ?array {
    if (!file_exists(LAST_SENSOR_FILE)) return null;
    $data = json_decode(file_get_contents(LAST_SENSOR_FILE), true);
    return $data ?: null;
}

// HTTP ke firmware ESP32
function esp32_get(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => ESP32_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => ESP32_TIMEOUT,
    ]);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || !$response) return null;
    return json_decode($response, true);
}

function esp32_post(string $url, array $data): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => ESP32_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => ESP32_TIMEOUT,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || !$response) return null;
    return json_decode($response, true);
}

// Prioritas: ESP32 → cache → dummy
function get_sensor_data(): array {
    if (!USE_DUMMY_DATA) {
        $data = esp32_get(API_SENSOR);
        if ($data) {
            $data['source']    = 'esp32';
            $data['timestamp'] = date('H:i:s');
            save_last_sensor($data);
            return $data;
        }

        $last = get_last_sensor();
        if ($last) {
            $last['source'] = 'cached';
            return $last;
        }
    }

    return [
        'suhu'      => round(20 + mt_rand(0, 80) / 10, 1),
        'humidity'  => mt_rand(52, 68),
        'amonia'    => round(5 + mt_rand(0, 200) / 10, 1),
        'cahaya'    => mt_rand(200, 750),
        'pakan'     => null,
        'timestamp' => date('H:i:s'),
        'source'    => 'dummy',
    ];
}

function get_actuator_status(): array {
    if (!USE_DUMMY_DATA) {
        $data = esp32_get(API_STATUS);
        if ($data) return $data;
    }

    return [
        'kipas'  => false,
        'lampu'  => true,
        'servo'  => false,
        'source' => 'dummy',
    ];
}

function send_control(string $device, bool $state): array {
    if (!USE_DUMMY_DATA) {
        $result = esp32_post(API_CONTROL, ['device' => $device, 'state' => $state]);
        if ($result) return $result;
    }

    return ['success' => true, 'device' => $device, 'state' => $state, 'source' => 'dummy'];
}

// normal | warning | danger
function sensor_status(string $type, ?float $value): string {
    if ($value === null) return 'normal';

    $t = get_thresholds();

    switch ($type) {
        case 'suhu':
            if ($value < $t['suhu_min'] || $value > $t['suhu_max']) return 'danger';
            if ($value > $t['suhu_max'] - 2) return 'warning';
            return 'normal';
        case 'humidity':
            if ($value < $t['humidity_min'] || $value > $t['humidity_max']) return 'danger';
            return 'normal';
        case 'amonia':
            if ($value > $t['amonia_max']) return 'danger';
            if ($value > $t['amonia_max'] - 5) return 'warning';
            return 'normal';
        case 'cahaya':
            if ($value < $t['cahaya_min'] || $value > $t['cahaya_max']) return 'warning';
            return 'normal';
        case 'pakan':
            if ($value < $t['pakan_min']) return 'danger';
            if ($value < $t['pakan_min'] + 200) return 'warning';
            return 'normal';
        default:
            return 'normal';
    }
}
