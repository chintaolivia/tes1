<?php
/**
 * Admin Member Management
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
initSession();

requireAdmin('/views/auth/login.php');

$pdo = getDb();
$members = $pdo->query("SELECT * FROM members ORDER BY id DESC")->fetchAll();

$pageTitle = 'Data Pelanggan & Member';
$extraCss = 'admin.css';

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container section" style="padding-top: 20px; padding-bottom: 90px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin: 0 0 4px; color: var(--primary-dark);">Data Pelanggan & Member</h1>
            <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">Daftar akun pelanggan, tingkatan loyalitas tier, dan total transaksi akumulasi.</p>
        </div>
        <input type="text" id="member-search" class="form-input" placeholder="Cari nama, email, nomor HP..." style="max-width: 280px;">
    </div>

    <div class="card" style="padding: 24px;">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--text-muted);">
                        <th style="padding: 12px 10px;">ID</th>
                        <th style="padding: 12px 10px;">Nama Pelanggan</th>
                        <th style="padding: 12px 10px;">Email</th>
                        <th style="padding: 12px 10px;">WhatsApp</th>
                        <th style="padding: 12px 10px;">Role</th>
                        <th style="padding: 12px 10px;">Tier Loyalitas</th>
                        <th style="padding: 12px 10px;">Total Belanja</th>
                        <th style="padding: 12px 10px;">Terdaftar</th>
                    </tr>
                </thead>
                <tbody id="member-tbody">
                    <?php foreach ($members as $m): 
                        $tierBadge = match($m['tier']) {
                            'vip'    => 'badge-primary',
                            'member' => 'badge-success',
                            default  => 'badge-secondary'
                        };
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);" class="member-row">
                            <td style="padding: 12px 10px; color: var(--text-muted);">#<?= $m['id'] ?></td>
                            <td style="padding: 12px 10px; font-weight: 700;"><?= htmlspecialchars($m['name']) ?></td>
                            <td style="padding: 12px 10px;"><?= htmlspecialchars($m['email']) ?></td>
                            <td style="padding: 12px 10px;"><?= htmlspecialchars($m['phone'] ?? '-') ?></td>
                            <td style="padding: 12px 10px;">
                                <span class="badge <?= $m['role'] === 'admin' ? 'badge-error' : 'badge-secondary' ?>">
                                    <?= strtoupper($m['role']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 10px;">
                                <span class="badge <?= $tierBadge ?>" style="text-transform: uppercase;">
                                    <?= htmlspecialchars($m['tier']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 10px; font-weight: 700; color: var(--primary);">
                                <?= formatRupiah($m['total_spent'] ?? 0) ?>
                            </td>
                            <td style="padding: 12px 10px; color: var(--text-muted); font-size: 0.85rem;">
                                <?= date('d M Y', strtotime($m['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('member-search');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const val = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.member-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            });
        }, 150));
    }
});
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>

