<?php
// ============================================================
//  KONEKSI DATABASE — config/database.php
//  Dipanggil via: require_once __DIR__ . '/../config/database.php';
// ============================================================

// Baca file rahasia .env jika tersedia
$env_path = __DIR__ . '/../.env';
$env = file_exists($env_path) ? parse_ini_file($env_path) : [];

define('DB_HOST', $env['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $env['DB_PORT'] ?? '3306');
define('DB_NAME', $env['DB_NAME'] ?? 'kandangsmart');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_CHAR', $env['DB_CHAR'] ?? 'utf8mb4');

/**
 * Mengembalikan koneksi PDO (singleton)
 */
function db_connect(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHAR
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Jangan tampilkan detail error ke user di production
        error_log('DB Error: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

/**
 * Simpan data sensor ke tabel sensor_log (Dibatasi maksimal 1x per menit)
 */
function db_save_sensor(array $data): bool {
    $pdo = db_connect();
    if (!$pdo) return false;

    // Cek kapan terakhir kali data disimpan untuk menghindari database bloat
    // Menggunakan TIMESTAMPDIFF di MySQL agar tidak terganggu perbedaan timezone PHP vs MySQL
    try {
        $stmt_check = $pdo->query("SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) AS diff FROM sensor_log");
        $diff = $stmt_check->fetchColumn();
        if ($diff !== null && $diff !== false && (int)$diff < 60) {
            return true;
        }
    } catch (PDOException $e) {
        // Jika gagal mengecek, biarkan lanjut mencoba menyimpan
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO sensor_log (suhu, humidity, amonia, cahaya, pakan, source)
            VALUES (:suhu, :humidity, :amonia, :cahaya, :pakan, :source)
        ");
        return $stmt->execute([
            ':suhu'     => isset($data['suhu'])     && $data['suhu']     !== null ? (float)$data['suhu']     : null,
            ':humidity' => isset($data['humidity']) && $data['humidity'] !== null ? (float)$data['humidity'] : null,
            ':amonia'   => isset($data['amonia'])   && $data['amonia']   !== null ? (float)$data['amonia']   : null,
            ':cahaya'   => isset($data['cahaya'])   && $data['cahaya']   !== null ? (float)$data['cahaya']   : null,
            ':pakan'    => isset($data['pakan'])    && $data['pakan']    !== null ? (float)$data['pakan']    : null,
            ':source'   => $data['source'] ?? 'esp32',
        ]);
    } catch (PDOException $e) {
        error_log('db_save_sensor error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Simpan log kontrol aktuator ke tabel kontrol_log
 */
function db_save_kontrol(string $perangkat, bool $state, bool $success = true): bool {
    $pdo = db_connect();
    if (!$pdo) return false;

    $aksi = ($perangkat === 'servo_pulse') ? 'PULSE' : ($state ? 'ON' : 'OFF');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO kontrol_log (perangkat, aksi, status)
            VALUES (:perangkat, :aksi, :status)
        ");
        return $stmt->execute([
            ':perangkat' => $perangkat,
            ':aksi'      => $aksi,
            ':status'    => $success ? 'berhasil' : 'gagal',
        ]);
    } catch (PDOException $e) {
        error_log('db_save_kontrol error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ambil riwayat sensor dari DB (default 20 baris terbaru)
 */
function db_get_sensor_log(int $limit = 20): array {
    $pdo = db_connect();
    if (!$pdo) return [];

    try {
        $stmt = $pdo->prepare("
            SELECT id, suhu, humidity, amonia, cahaya, pakan, source,
                   DATE_FORMAT(created_at, '%d/%m %H:%i:%s') AS waktu
            FROM sensor_log
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('db_get_sensor_log error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil riwayat kontrol dari DB (default 20 baris terbaru)
 */
function db_get_kontrol_log(int $limit = 20): array {
    $pdo = db_connect();
    if (!$pdo) return [];

    try {
        $stmt = $pdo->prepare("
            SELECT id, perangkat, aksi, status,
                   DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') AS waktu
            FROM kontrol_log
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('db_get_kontrol_log error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil semua anggota dari DB
 */
function db_get_anggota(): array {
    $pdo = db_connect();
    if (!$pdo) return [];

    try {
        $stmt = $pdo->query("SELECT * FROM anggota ORDER BY id ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('db_get_anggota error: ' . $e->getMessage());
        return [];
    }
}
