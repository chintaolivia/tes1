<?php
/**
 * Customer Order History
 * Little Salt Bread Blok M — POS System
 */

$pageTitle = 'Riwayat Pesanan';
$pageDescription = 'Lihat riwayat transaksi dan lacak status pesanan Salt Bread Anda.';
$extraCss = 'customer.css';

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
initSession();

$currentUser = getCurrentUser();
$orders = [];

if ($currentUser) {
    $res = OrderController::getHistory($currentUser['id'], 1, 20);
    if ($res['success']) {
        $orders = $res['data']['orders'] ?? [];
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container section" style="max-width: 780px; padding-top: 20px; padding-bottom: 90px;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin: 0 0 6px; color: var(--primary-dark);">Riwayat Pesanan</h1>
        <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">Lacak dan pesan ulang varian Salt Bread favorit Anda.</p>
    </div>

    <!-- Quick search by order code (useful for guest orders) -->
    <div class="card" style="padding: 18px 20px; margin-bottom: 24px; background: var(--bg-card);">
        <form id="track-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" id="track-code" class="form-input" placeholder="Masukkan Kode Pesanan (Contoh: SB1809...)" style="flex: 1; min-width: 220px;" required>
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Lacak Pesanan</button>
        </form>
    </div>

    <?php if (!$currentUser && empty($orders)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="width: 48px; height: 48px; margin: 0 auto 12px; color: var(--text-muted);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <h3 style="font-family: var(--font-display); margin: 12px 0 6px;">Masuk untuk Melihat Riwayat</h3>
            <p style="color: var(--text-secondary); margin: 0 0 16px; font-size: 0.95rem;">Login ke akun Anda agar semua riwayat pesanan tersimpan rapi dan dapatkan poin loyalitas.</p>
            <a href="<?= $baseUrl ?>/views/auth/login.php" class="btn btn-primary btn-sm">Masuk Akun</a>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <div style="width: 48px; height: 48px; margin: 0 auto 12px; color: var(--text-muted);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="13" y2="17"></line></svg>
            </div>
            <h3 style="font-family: var(--font-display); margin: 12px 0 6px;">Belum Ada Riwayat Pesanan</h3>
            <p style="color: var(--text-secondary); margin: 0 0 16px;">Anda belum melakukan pemesanan. Yuk coba Salt Bread hangat kami!</p>
            <a href="<?= $baseUrl ?>/views/customer/index.php" class="btn btn-primary">Pesan Sekarang</a>
        </div>
    <?php else: ?>
        <div class="history-list" style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($orders as $o): 
                $statusColors = [
                    'pending'   => 'badge-warning',
                    'confirmed' => 'badge-info',
                    'processing'=> 'badge-primary',
                    'ready'     => 'badge-success',
                    'shelf'     => 'badge-warning',
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-error',
                ];
                $statusBadge = $statusColors[$o['order_status']] ?? 'badge-secondary';
                $statusLabels = [
                    'pending'   => 'Menunggu',
                    'confirmed' => 'Dikonfirmasi',
                    'processing'=> 'Dipanggang',
                    'ready'     => 'Siap Diambil',
                    'shelf'     => 'Di Rak Mandiri',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ];
            ?>
                <div class="card" style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                            <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary); font-family: var(--font-display); margin-top: 2px;">
                                <?= htmlspecialchars($o['queue_number']) ?>
                            </div>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Kode: <code><?= htmlspecialchars($o['order_code']) ?></code></span>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge <?= $statusBadge ?>"><?= $statusLabels[$o['order_status']] ?? $o['order_status'] ?></span>
                            <div style="font-weight: 800; font-size: 1.1rem; margin-top: 6px; color: var(--text-primary);">
                                <?= formatRupiah($o['total_amount']) ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border);">
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">
                            <?= $o['order_type'] === 'dine_in' ? 'Dine-in' : 'Take-away' ?> • <?= strtoupper($o['payment_method']) ?>
                        </span>
                        <a href="<?= $baseUrl ?>/views/customer/invoice.php?order_code=<?= urlencode($o['order_code']) ?>" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                            Lihat Struk &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trackForm = document.getElementById('track-form');
    if (trackForm) {
        trackForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const code = document.getElementById('track-code').value.trim();
            if (code) {
                window.location.href = `/views/customer/invoice.php?order_code=${encodeURIComponent(code)}`;
                window.location.href = `<?= $baseUrl ?>/views/customer/invoice.php?order_code=${encodeURIComponent(code)}`;
            }
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>

