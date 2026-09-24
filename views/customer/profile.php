<?php
/**
 * Customer Profile & Loyalty Tier
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
initSession();

requireAuth('/views/auth/login.php');

$currentUser = getCurrentUser();
$pdo = getDb();

// Fetch fresh member data
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $currentUser['id']]);
$member = $stmt->fetch();

$totalSpent = (int) ($member['total_spent'] ?? 0);
$tier = $member['tier'] ?? 'regular';

// Thresholds from settings
$memberThreshold = (int) getSetting('member_threshold', '500000');
$vipThreshold = (int) getSetting('vip_threshold', '2000000');

// Calculate progress to next tier
$progressPercent = 0;
$nextTierName = '';
$remainingAmount = 0;

if ($tier === 'regular') {
    $nextTierName = 'Member (Diskon 5%)';
    $progressPercent = min(100, round(($totalSpent / $memberThreshold) * 100));
    $remainingAmount = max(0, $memberThreshold - $totalSpent);
} elseif ($tier === 'member') {
    $nextTierName = 'VIP (Diskon 10%)';
    $progressPercent = min(100, round((($totalSpent - $memberThreshold) / ($vipThreshold - $memberThreshold)) * 100));
    $remainingAmount = max(0, $vipThreshold - $totalSpent);
} else {
    $nextTierName = 'Tingkat Maksimal';
    $progressPercent = 100;
}

$pageTitle = 'Profil Member';
$pageDescription = 'Informasi akun member dan status tingkatan diskon loyalitas Little Salt Bread.';
$extraCss = 'customer.css';

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container section" style="max-width: 680px; padding-top: 20px; padding-bottom: 90px;">
    <!-- Profile Card -->
    <div class="card" style="padding: 32px 24px; text-align: center; margin-bottom: 24px;">
        <div class="user-avatar" style="width: 76px; height: 76px; font-size: 2rem; margin: 0 auto 16px; background: var(--primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
            <?= strtoupper(substr($member['name'], 0, 1)) ?>
        </div>
        <h2 style="font-family: var(--font-display); font-size: 1.8rem; margin: 0 0 4px;"><?= htmlspecialchars($member['name']) ?></h2>
        <p style="color: var(--text-secondary); margin: 0 0 12px; font-size: 0.95rem;"><?= htmlspecialchars($member['email']) ?> • <?= htmlspecialchars($member['phone'] ?? '-') ?></p>
        
        <div>
            <span class="badge badge-primary" style="font-size: 0.9rem; padding: 6px 14px; text-transform: uppercase; letter-spacing: 1px;">
                TIER: <?= htmlspecialchars($tier) ?>
            </span>
        </div>
    </div>

    <!-- Loyalty Tier Status Card -->
    <div class="card" style="padding: 24px; margin-bottom: 24px; background: var(--bg-card);">
        <h3 style="font-family: var(--font-display); margin: 0 0 14px; font-size: 1.4rem;">Status Loyalitas & Diskon</h3>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 0.9rem; color: var(--text-secondary);">Total Belanja Akumulasi:</span>
            <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;"><?= formatRupiah($totalSpent) ?></span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.9rem; color: var(--text-secondary);">Target Berikutnya:</span>
            <span style="font-weight: 600; font-size: 0.95rem;"><?= $nextTierName ?></span>
        </div>

        <!-- Progress Bar -->
        <div style="background: var(--border); border-radius: var(--radius-xl); height: 12px; overflow: hidden; margin-bottom: 10px;">
            <div style="background: var(--primary); height: 100%; width: <?= $progressPercent ?>%; transition: width 0.6s ease; border-radius: var(--radius-xl);"></div>
        </div>

        <?php if ($remainingAmount > 0): ?>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                Belanja <strong><?= formatRupiah($remainingAmount) ?></strong> lagi untuk naik ke tingkat <strong><?= $nextTierName ?></strong>!
            </p>
        <?php else: ?>
            <p style="color: var(--success); font-size: 0.85rem; margin: 0; font-weight: 600;">
                Selamat! Anda sudah berada di tingkat loyalitas tertinggi. Nikmati diskon spesial 10%!
            </p>
        <?php endif; ?>
    </div>

    <!-- Tier Benefits Matrix -->
    <div class="card" style="padding: 24px; margin-bottom: 24px;">
        <h3 style="font-family: var(--font-display); margin: 0 0 16px; font-size: 1.4rem;">Keuntungan Tingkatan Member</h3>
        
        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.95rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-radius: var(--radius); background: <?= $tier === 'regular' ? 'rgba(217, 119, 6, 0.1)' : 'transparent' ?>;">
                <div>
                    <strong>Regular</strong>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Pendaftaran awal</div>
                </div>
                <span>Diskon 0%</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-radius: var(--radius); background: <?= $tier === 'member' ? 'rgba(217, 119, 6, 0.1)' : 'transparent' ?>;">
                <div>
                    <strong>Member</strong>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Total belanja > Rp 500.000</div>
                </div>
                <span style="font-weight: 700; color: var(--primary);">Diskon 5% Otomatis</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-radius: var(--radius); background: <?= $tier === 'vip' ? 'rgba(217, 119, 6, 0.1)' : 'transparent' ?>;">
                <div>
                    <strong>VIP Member</strong>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Total belanja > Rp 2.000.000</div>
                </div>
                <span style="font-weight: 800; color: var(--primary);">Diskon 10% Otomatis</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="display: flex; gap: 12px;">
        <a href="<?= $baseUrl ?>/views/customer/history.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
            Riwayat Pesanan
        </a>
        <button type="button" class="btn btn-danger" onclick="handleLogout(event)" style="flex: 1;">
            Keluar Akun
        </button>
    </div>
</div>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>

