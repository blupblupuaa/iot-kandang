-- ============================================================
--  KANDANGSMART — SQL Dump
--  Database: kandangsmart
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kandangsmart`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `kandangsmart`;

-- ------------------------------------------------------------
--  Tabel: anggota
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `anggota` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(100)    NOT NULL,
    `nim`        VARCHAR(20)     NOT NULL,
    `prodi`      VARCHAR(100)    NOT NULL DEFAULT '',
    `fakultas`   VARCHAR(100)    NOT NULL DEFAULT '',
    `peran`      VARCHAR(50)     NOT NULL DEFAULT 'Anggota',
    `bio`        TEXT                NULL,
    `foto`       VARCHAR(255)        NULL COMMENT 'Nama file foto di assets/img/',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data anggota kelompok (sesuaikan NIM, prodi, dll)
INSERT INTO `anggota` (`nama`, `nim`, `prodi`, `fakultas`, `peran`, `bio`, `foto`) VALUES
('Ahmad Muamar Rijal', 'D400XXXXXX', 'Teknik Elektro', 'Fakultas Teknik', 'Ketua Kelompok',
 'Mahasiswa Teknik Elektro yang tertarik di bidang IoT dan embedded systems.', NULL),
('Aditya Arvio Putra', 'D400XXXXXX', 'Teknik Elektro', 'Fakultas Teknik', 'Anggota',
 'Mahasiswa Teknik Elektro dengan minat di bidang pemrograman web dan jaringan.', NULL);

-- ------------------------------------------------------------
--  Tabel: sensor_log
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sensor_log` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `suhu`       FLOAT               NULL COMMENT 'Derajat Celsius',
    `humidity`   FLOAT               NULL COMMENT 'Persen',
    `amonia`     FLOAT               NULL COMMENT 'ppm',
    `cahaya`     FLOAT               NULL COMMENT 'lux',
    `pakan`      FLOAT               NULL COMMENT 'gram',
    `source`     ENUM('esp32','cached','dummy') NOT NULL DEFAULT 'esp32',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Tabel: kontrol_log
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kontrol_log` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `perangkat`  VARCHAR(20)     NOT NULL COMMENT 'kipas | lampu | servo | servo_pulse',
    `aksi`       ENUM('ON','OFF','PULSE') NOT NULL,
    `status`     ENUM('berhasil','gagal') NOT NULL DEFAULT 'berhasil',
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_perangkat`  (`perangkat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
