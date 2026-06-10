<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$page_title = 'Profil Anggota';
$active_nav = 'profil';

$anggota = db_get_anggota();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container profil-page" style="padding: 0;">

    <div class="profil-section welcome-section">
        <div class="profil-header" style="text-align: center;">
            <p class="profil-hero-tag stagger-elem stagger-1">Tim Pengembang</p>
            <h1 class="page-title stagger-elem stagger-2" style="font-size: clamp(3rem, 8vw, 5rem); color: var(--accent); margin: 1rem 0;">Welcome</h1>
            <p class="page-subtitle stagger-elem stagger-3" style="font-size: 1.2rem;">Profil Anggota Kelompok di balik sistem KandangSmart</p>
            <p class="scroll-hint stagger-elem stagger-4" style="margin-top: 2rem; color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; animation: pulse 2s infinite;">Scroll ke bawah ↓</p>
        </div>
    </div>

    <?php if (empty($anggota)): ?>
        <div class="profil-section">
            <div class="alert alert-warning profil-no-data stagger-elem stagger-1">
                ⚠️ Data anggota belum tersedia di database. Pastikan sudah import file SQL.
            </div>
        </div>
    <?php else: ?>
        <div class="profil-members-list">
            <?php foreach ($anggota as $i => $a): ?>
            <div class="profil-section">
                <div class="profil-hero-layout stagger-elem stagger-1">
                    <div class="profil-hero-avatar stagger-elem stagger-2">
                        <div class="avatar-inner">
                            <?php if (!empty($a['foto']) && file_exists(__DIR__ . '/assets/img/' . $a['foto'])): ?>
                                <img src="<?= asset('img/' . htmlspecialchars($a['foto'])) ?>"
                                     alt="Foto <?= htmlspecialchars($a['nama']) ?>">
                            <?php else: ?>
                                <span class="profil-avatar-initials">
                                    <?= strtoupper(substr($a['nama'], 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="profil-hero-info">
                        <p class="profil-hero-greeting stagger-elem stagger-3">Halo, saya</p>
                        <h2 class="profil-hero-name stagger-elem stagger-3"><?= htmlspecialchars($a['nama']) ?>.</h2>
                        <p class="profil-hero-bio stagger-elem stagger-4">
                            <?php if (!empty($a['peran'])): ?>
                                <strong><?= htmlspecialchars($a['peran']) ?>.</strong>
                            <?php endif; ?>
                            <?php if (!empty($a['bio'])): ?>
                                <?= htmlspecialchars($a['bio']) ?>
                            <?php endif; ?>
                            <?php if (!empty($a['nim'])): ?>
                                <br><span style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">NIM: <?= htmlspecialchars($a['nim']) ?></span>
                            <?php endif; ?>
                        </p>
                        <div class="profil-hero-actions stagger-elem stagger-5">
                            <span class="btn btn-outline"><?= htmlspecialchars($a['prodi']) ?></span>
                            <span class="btn btn-primary"><?= htmlspecialchars($a['fakultas']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>