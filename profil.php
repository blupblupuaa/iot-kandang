<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$page_title = 'Profil Anggota';
$active_nav = 'profil';

$anggota = db_get_anggota();
$total   = count($anggota);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container profil-page">

    <div class="profil-carousel-top">
        <span class="profil-counter">
            <span id="profilCurrent">Profil</span><?php if ($total > 0): ?> / <span id="profilTotal"><?= $total ?></span><?php endif; ?>
        </span>
        <?php if ($total > 0): ?>
        <div class="profil-nav">
            <button type="button" class="profil-nav-btn" id="profilPrev" aria-label="Slide sebelumnya">‹</button>
            <button type="button" class="profil-nav-btn" id="profilNext" aria-label="Slide berikutnya">›</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="profil-sections" id="profilSections">
        <section class="profil-hero profil-slide is-active">
            <p class="profil-hero-tag">Tim Pengembang</p>
            <h1 class="page-title">Profil Anggota</h1>
            <p class="page-subtitle">Kelompok di balik sistem KandangSmart</p>
            <p class="profil-hero-note">Scroll ke bawah untuk melihat detail satu per satu.</p>
            <?php if (empty($anggota)): ?>
            <div class="alert alert-warning profil-no-data">
                ⚠️ Data anggota belum tersedia di database. Pastikan sudah import file SQL.
            </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($anggota)): ?>
            <?php foreach ($anggota as $i => $a): ?>
            <section class="profil-slide" data-index="<?= $i ?>">
                <article class="profil-card">
                    <div class="profil-card-accent"></div>
                    <div class="profil-card-body">
                        <div class="profil-avatar">
                            <?php if (!empty($a['foto']) && file_exists(__DIR__ . '/assets/img/' . $a['foto'])): ?>
                                <img src="<?= asset('img/' . htmlspecialchars($a['foto'])) ?>"
                                     alt="Foto <?= htmlspecialchars($a['nama']) ?>">
                            <?php else: ?>
                                <span class="profil-avatar-initials">
                                    <?= strtoupper(substr($a['nama'], 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="profil-info">
                            <span class="profil-badge"><?= htmlspecialchars($a['peran']) ?></span>
                            <h2 class="profil-nama"><?= htmlspecialchars($a['nama']) ?></h2>
                            <p class="profil-nim"><?= htmlspecialchars($a['nim']) ?></p>
                            <?php if (!empty($a['bio'])): ?>
                            <p class="profil-bio"><?= htmlspecialchars($a['bio']) ?></p>
                            <?php endif; ?>
                            <div class="profil-meta">
                                <div class="profil-meta-item">
                                    <span class="profil-meta-label">Prodi</span>
                                    <span class="profil-meta-value"><?= htmlspecialchars($a['prodi']) ?></span>
                                </div>
                                <div class="profil-meta-item">
                                    <span class="profil-meta-label">Fakultas</span>
                                    <span class="profil-meta-value"><?= htmlspecialchars($a['fakultas']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total > 0): ?>
    <div class="profil-dots" id="profilDots">
        <?php foreach ($anggota as $i => $a): ?>
        <button type="button" class="profil-dot<?= $i === 0 ? ' active' : '' ?>"
                data-index="<?= $i ?>" aria-label="Anggota <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <p class="profil-swipe-hint">Scroll atau gunakan tombol navigasi untuk mengganti profil.</p>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
