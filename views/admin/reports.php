<?php
/**
 * Admin Sales Reports & Analytics
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
initSession();

requireAdmin('/views/auth/login.php');

$pdo = getDb();
$range = $_GET['range'] ?? 'today';

$dateCondition = match($range) {
    '7days' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
    'month' => "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())",
    default => "DATE(created_at) = CURDATE()",
};

// Summary metrics
$summaryStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as paid_revenue,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN tax_amount ELSE 0 END), 0) as tax_collected,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN discount_amount ELSE 0 END), 0) as total_discounts
    FROM orders 
    WHERE {$dateCondition}
");
$summary = $summaryStmt->fetch();

// Breakdown by payment method
$payStmt = $pdo->query("
    SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
    FROM orders
    WHERE {$dateCondition} AND payment_status = 'paid'
    GROUP BY payment_method
");
$paymentMethods = $payStmt->fetchAll();

// Product sales ranking
$prodStmt = $pdo->query("
    SELECT oi.item_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE {$dateCondition} AND o.payment_status = 'paid'
    GROUP BY oi.item_name
    ORDER BY total_qty DESC
");
$productSales = $prodStmt->fetchAll();

$maxQty = 1;
foreach ($productSales as $ps) {
    if ($ps['total_qty'] > $maxQty) $maxQty = $ps['total_qty'];
}

$pageTitle = 'Laporan Penjualan & Keuangan';
$extraCss = 'admin.css';

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container section" style="padding-top: 20px; padding-bottom: 90px;">
    <!-- Header with Date Filters -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin: 0 0 4px; color: var(--primary-dark);">Laporan Penjualan</h1>
            <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">Analisis omzet, setoran pajak, dan performa menu Little Salt Bread Blok M.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/views/admin/reports.php?range=today" class="btn btn-sm <?= $range === 'today' ? 'btn-primary' : 'btn-secondary' ?>">Hari Ini</a>
            <a href="<?= $baseUrl ?>/views/admin/reports.php?range=7days" class="btn btn-sm <?= $range === '7days' ? 'btn-primary' : 'btn-secondary' ?>">7 Hari Terakhir</a>
            <a href="<?= $baseUrl ?>/views/admin/reports.php?range=month" class="btn btn-sm <?= $range === 'month' ? 'btn-primary' : 'btn-secondary' ?>">Bulan Ini</a>
        </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="stat-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div class="card" style="padding: 20px; border-left: 4px solid var(--primary);">
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Omzet Bersih</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin: 8px 0 4px;">
                <?= formatRupiah($summary['paid_revenue']) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Dari pesanan terverifikasi lunas</div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid var(--info);">
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Transaksi</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--info); margin: 8px 0 4px;">
                <?= (int) $summary['total_orders'] ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Pesanan pada periode ini</div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid var(--success);">
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Pajak PB1 Terkumpul</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--success); margin: 8px 0 4px;">
                <?= formatRupiah($summary['tax_collected']) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Kewajiban pajak restoran (11%)</div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid var(--warning);">
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Potongan Member</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--warning); margin: 8px 0 4px;">
                <?= formatRupiah($summary['total_discounts']) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Diskon loyalitas yang diberikan</div>
        </div>
    </div>

    <!-- Charts & Breakdown Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Product Sales Horizontal Chart -->
        <div class="card" style="padding: 24px;">
            <h3 style="font-family: var(--font-display); margin: 0 0 20px; font-size: 1.3rem;">Volume Penjualan Produk</h3>
            
            <?php if (empty($productSales)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">Belum ada penjualan pada periode ini.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($productSales as $ps): 
                        $widthPercent = round(($ps['total_qty'] / $maxQty) * 100);
                    ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.9rem;">
                                <strong><?= htmlspecialchars($ps['item_name']) ?></strong>
                                <span style="color: var(--primary); font-weight: 700;"><?= $ps['total_qty'] ?> porsi (<?= formatRupiah($ps['total_sales']) ?>)</span>
                            </div>
                            <div style="background: var(--border); height: 10px; border-radius: var(--radius-xl); overflow: hidden;">
                                <div style="background: var(--primary); height: 100%; width: <?= $widthPercent ?>%; border-radius: var(--radius-xl); transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Method Distribution -->
        <div class="card" style="padding: 24px;">
            <h3 style="font-family: var(--font-display); margin: 0 0 20px; font-size: 1.3rem;">Metode Pembayaran</h3>

            <?php if (empty($paymentMethods)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">Belum ada data pembayaran.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($paymentMethods as $pm): ?>
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($pm['payment_method']) ?></span>
                                <span class="badge badge-primary"><?= $pm['count'] ?> trx</span>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary);">
                                <?= formatRupiah($pm['total']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>

