<?php
// ============================================================
//  HALAMAN PROFIL ANGGOTA (profil.php)
// ============================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$page_title = 'Profil Anggota';
$active_nav = 'profil';

$anggota = db_get_anggota();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">

    <div class="page-header">
        <h1 class="page-title">◉ Profil Anggota</h1>
        <p class="page-subtitle">Kelompok pengembang sistem KandangSmart</p>
    </div>

    <?php if (empty($anggota)): ?>
    <div class="alert alert-warning">
        ⚠️ Data anggota belum tersedia di database. Pastikan sudah import file SQL.
    </div>
    <?php else: ?>

    <div class="profil-grid">
        <?php foreach ($anggota as $i => $a): ?>
        <div class="profil-card" style="animation-delay: <?= $i * 0.1 ?>s">

            <!-- Avatar -->
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

            <!-- Peran badge -->
            <span class="profil-badge">
                <?= htmlspecialchars($a['peran']) ?>
            </span>

            <!-- Nama & NIM -->
            <h2 class="profil-nama"><?= htmlspecialchars($a['nama']) ?></h2>
            <p class="profil-nim"><?= htmlspecialchars($a['nim']) ?></p>

            <!-- Bio -->
            <?php if (!empty($a['bio'])): ?>
            <p class="profil-bio"><?= htmlspecialchars($a['bio']) ?></p>
            <?php endif; ?>

            <!-- Detail -->
            <div class="profil-detail">
                <div class="profil-detail-item">
                    <span class="profil-detail-label">Program Studi</span>
                    <span class="profil-detail-value"><?= htmlspecialchars($a['prodi']) ?></span>
                </div>
                <div class="profil-detail-item">
                    <span class="profil-detail-label">Fakultas</span>
                    <span class="profil-detail-value"><?= htmlspecialchars($a['fakultas']) ?></span>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
