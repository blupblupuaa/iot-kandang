<?php
// =============================================================
//  HEADER — dipanggil di setiap halaman
// =============================================================
require_once __DIR__ . '/../config/config.php';

// Cek status koneksi ESP32 secara aktual
$esp32_online = false;
if (!USE_DUMMY_DATA) {
    $ch = curl_init(ESP32_BASE_URL . '/ping');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 2,
        CURLOPT_CONNECTTIMEOUT => 2,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    $esp32_online = (!$err && $res);
}

// Tentukan label & warna status navbar
if (USE_DUMMY_DATA) {
    $nav_status_class = 'status-dummy';
    $nav_status_label = 'Demo';
} elseif ($esp32_online) {
    $nav_status_class = 'status-live';
    $nav_status_label = 'Live';
} else {
    $nav_status_class = 'status-offline';
    $nav_status_label = 'Offline';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? SITE_NAME) ?> — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        .status-offline { background: var(--danger); }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?= url('index.php') ?>" class="nav-brand">
            <span class="brand-icon">🐔</span>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li>
                <a href="<?= url('index.php') ?>" class="nav-link <?= ($active_nav ?? '') === 'home' ? 'active' : '' ?>">
                    <span class="nav-icon">⬡</span> Beranda
                </a>
            </li>
            <li>
                <a href="<?= url('monitor.php') ?>" class="nav-link <?= ($active_nav ?? '') === 'monitor' ? 'active' : '' ?>">
                    <span class="nav-icon">◈</span> Monitor
                </a>
            </li>
            <li>
                <a href="<?= url('control.php') ?>" class="nav-link <?= ($active_nav ?? '') === 'control' ? 'active' : '' ?>">
                    <span class="nav-icon">◎</span> Kontrol
                </a>
            </li>
        </ul>

        <div class="nav-status">
            <span class="status-dot <?= $nav_status_class ?>"></span>
            <span class="status-label"><?= $nav_status_label ?></span>
        </div>
    </div>
</nav>

<!-- ===== KONTEN UTAMA ===== -->
<main class="main-content">