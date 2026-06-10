<?php
// ============================================================
//  API ENDPOINT (api.php)
// ============================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!$body || !isset($body['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bad request']);
    exit;
}

switch ($body['action']) {

    case 'sensor':
        $data = get_sensor_data();
        $status = [];
        foreach (['suhu', 'humidity', 'amonia', 'cahaya', 'pakan'] as $key) {
            $status[$key] = sensor_status($key, isset($data[$key]) ? (float)$data[$key] : null);
        }
        // Simpan ke database
        db_save_sensor($data);
        echo json_encode(['success' => true, 'data' => $data, 'status' => $status]);
        break;

    case 'control':
        $device = $body['device'] ?? null;
        $state  = $body['state']  ?? false;

        $allowed = ['kipas', 'lampu', 'servo', 'servo_pulse'];
        if (!in_array($device, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Perangkat tidak dikenal']);
            break;
        }

        $result  = send_control($device, (bool)$state);
        $success = $result['success'] ?? true;

        // Simpan log kontrol ke database
        db_save_kontrol($device, (bool)$state, $success);

        echo json_encode(['success' => true, 'result' => $result, 'device' => $device, 'state' => $state]);
        break;

    case 'threshold':
        $keys = ['suhu_min','suhu_max','humidity_min','humidity_max',
                 'amonia_max','cahaya_min','cahaya_max','pakan_min'];
        $payload = [];
        foreach ($keys as $k) {
            if (!isset($body[$k]) || !is_numeric($body[$k])) {
                echo json_encode(['success' => false, 'message' => "Nilai $k tidak valid"]);
                exit;
            }
            $payload[$k] = (float)$body[$k];
        }
        $saved = file_put_contents(
            __DIR__ . '/config/threshold.json',
            json_encode($payload, JSON_PRETTY_PRINT)
        );
        echo json_encode([
            'success' => $saved !== false,
            'message' => $saved !== false ? 'Threshold disimpan' : 'Gagal menyimpan'
        ]);
        break;

    case 'status':
        $data = get_actuator_status();
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action tidak dikenal']);
        break;
}
