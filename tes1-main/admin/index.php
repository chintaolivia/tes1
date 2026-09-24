<?php
/**
 * Dashboard Admin, Kasir, & Dapur Salt Bread
 * Kontrol status pesanan, manajemen stok varian, dan verifikasi pembayaran
 */

require_once dirname(__DIR__) . '/config/database.php';
$pdo = getDbConnection();

// Auto check dan alihkan pesanan siap_diambil > 20 mnt ke rak_mandiri
$pdo->query("
    UPDATE orders 
    SET order_status = 'rak_mandiri', updated_at = NOW() 
    WHERE order_status = 'siap_diambil' 
      AND ready_at IS NOT NULL 
      AND TIMESTAMPDIFF(MINUTE, ready_at, NOW()) >= 20
");

// Filter tab
$currentTab = $_GET['tab'] ?? 'orders';
$statusFilter = $_GET['status'] ?? 'all';

// Hitung Statistik
$statsToday = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$statsActive = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('dipanggang', 'sedang_dikemas', 'dikemas')")->fetchColumn();
$statsReady = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'siap_diambil'")->fetchColumn();
$statsShelf = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'rak_mandiri'")->fetchColumn();
$statsRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()")->fetchColumn();

// Ambil data Pesanan
$orderSql = "SELECT * FROM orders WHERE 1=1 ";
$orderParams = [];

if ($statusFilter !== 'all') {
    $orderSql .= " AND order_status = ? ";
    $orderParams[] = $statusFilter;
}
$orderSql .= " ORDER BY id DESC LIMIT 50";

$stmtOrders = $pdo->prepare($orderSql);
$stmtOrders->execute($orderParams);
$orders = $stmtOrders->fetchAll();

// Ambil data Menu & Stok
$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen & Admin Dashboard - Salt Bread</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-nav {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .admin-stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .admin-stat-val {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            line-height: 1.2;
        }
        .admin-stat-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .admin-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .admin-tab-link {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .admin-tab-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        .stock-control-btn {
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: white;
            cursor: pointer;
        }
        .stock-control-btn:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body style="background: #f8fafc;">

    <!-- Admin Header -->
    <header class="admin-nav">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="../index.php" style="width: 40px; height: 40px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--primary); text-decoration: none;">SB</a>
            <div>
                <h1 style="font-family: var(--font-hand); font-size: 26px; font-weight: 700; color: #1e293b; margin: 0; line-height: 1.1;">
                    <span style="color: var(--primary);">little</span> SALT BREAD • DAPUR & STAFF
                </h1>
                <div style="font-size: 12px; color: #64748b;">
                    Kelola Antrian Online, Panggangan, & Stok Real-Time
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <button type="button" class="btn-sound-toggle" onclick="testChime()">
                Tes Suara Notifikasi
            </button>
            <a href="../monitor.php" target="_blank" class="btn-action btn-action-blue">
                Buka Monitor TV
            </a>
            <a href="../index.php" class="btn-action btn-action-primary">
                Halaman Pemesanan
            </a>
        </div>
    </header>

    <div class="container">

        <!-- Stat Cards -->
        <div class="admin-stat-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-title">Total Antrian Hari Ini</div>
                <div class="admin-stat-val"><?= $statsToday ?></div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Pemesanan online tercatat</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-title">Sedang Dipanggang/Kemas</div>
                <div class="admin-stat-val" style="color: #d97706;"><?= $statsActive ?></div>
                <div style="font-size: 11px; color: #d97706; margin-top: 4px;">Dalam proses dapur</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-title">Siap Diambil di Counter</div>
                <div class="admin-stat-val" style="color: #059669;"><?= $statsReady ?></div>
                <div style="font-size: 11px; color: #059669; margin-top: 4px;">Menunggu pelanggan</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-title">Rak Pengambilan Mandiri</div>
                <div class="admin-stat-val" style="color: #dc2626;"><?= $statsShelf ?></div>
                <div style="font-size: 11px; color: #dc2626; margin-top: 4px;">> 20 menit belum diambil</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-title">Omset Hari Ini (Lunas)</div>
                <div class="admin-stat-val" style="color: #1e293b; font-size: 22px;"><?= rupiah($statsRevenue) ?></div>
                <div style="font-size: 11px; color: #059669; margin-top: 4px;">Pembayaran terverifikasi</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="admin-tabs">
            <a href="?tab=orders" class="admin-tab-link <?= $currentTab === 'orders' ? 'active' : '' ?>">
                Antrian Dapur & Pesanan (<?= count($orders) ?>)
            </a>
            <a href="?tab=stocks" class="admin-tab-link <?= $currentTab === 'stocks' ? 'active' : '' ?>">
                Manajemen Stok Varian (<?= count($menuItems) ?>)
            </a>
        </div>

        <?php if ($currentTab === 'orders'): ?>
            
            <!-- Filter Bar Status -->
            <div style="display: flex; gap: 8px; margin-bottom: 16px; overflow-x: auto; padding-bottom: 4px;">
                <a href="?tab=orders&status=all" class="tab-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">Semua</a>
                <a href="?tab=orders&status=menunggu_konfirmasi" class="tab-btn <?= $statusFilter === 'menunggu_konfirmasi' ? 'active' : '' ?>">Konfirmasi</a>
                <a href="?tab=orders&status=dipanggang" class="tab-btn <?= $statusFilter === 'dipanggang' ? 'active' : '' ?>">Dipanggang</a>
                <a href="?tab=orders&status=sedang_dikemas" class="tab-btn <?= $statusFilter === 'sedang_dikemas' ? 'active' : '' ?>">Dikemas</a>
                <a href="?tab=orders&status=siap_diambil" class="tab-btn <?= $statusFilter === 'siap_diambil' ? 'active' : '' ?>">Siap Diambil</a>
                <a href="?tab=orders&status=rak_mandiri" class="tab-btn <?= $statusFilter === 'rak_mandiri' ? 'active' : '' ?>">Rak Mandiri (>20m)</a>
                <a href="?tab=orders&status=selesai" class="tab-btn <?= $statusFilter === 'selesai' ? 'active' : '' ?>">Selesai</a>
            </div>

            <!-- Tabel Antrian -->
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No. Antrian</th>
                            <th>Pemesan</th>
                            <th>Item Dipesan</th>
                            <th>Total & Bayar</th>
                            <th>Status Sekarang</th>
                            <th>Waktu / Timer</th>
                            <th style="text-align: right;">Aksi Cepat Dapur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    Belum ada antrian dengan filter ini.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($orders as $ord): ?>
                            <?php
                                $badge = getStatusBadge($ord['order_status']);
                                
                                // Ambil item
                                $itemSt = $pdo->prepare("SELECT item_name, quantity FROM order_items WHERE order_id = ?");
                                $itemSt->execute([$ord['id']]);
                                $its = $itemSt->fetchAll();

                                // Hitung durasi ready
                                $elapsedMinutes = null;
                                if (!empty($ord['ready_at'])) {
                                    $diffSec = time() - strtotime($ord['ready_at']);
                                    $elapsedMinutes = floor($diffSec / 60);
                                }
                            ?>
                            <tr id="rowOrder<?= $ord['id'] ?>">
                                <td>
                                    <div style="font-size: 20px; font-weight: 900; color: #78350f; letter-spacing: -0.5px;">
                                        <?= htmlspecialchars($ord['queue_number']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($ord['order_code']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                    <div style="font-size: 12px; color: #64748b;">Telp: <?= htmlspecialchars($ord['customer_phone']) ?></div>
                                    <span style="display:inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                        <?= $ord['order_type'] === 'dine_in' ? 'Dine-in' : 'Takeaway' ?>
                                    </span>
                                </td>
                                <td>
                                    <ul style="margin: 0; padding-left: 16px; font-size: 13px; color: #334155;">
                                        <?php foreach ($its as $it): ?>
                                            <li><strong><?= $it['quantity'] ?>x</strong> <?= htmlspecialchars($it['item_name']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--primary);"><?= rupiah($ord['total_amount']) ?></div>
                                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;"><?= htmlspecialchars($ord['payment_method']) ?></div>
                                    <?php if ($ord['payment_status'] === 'paid'): ?>
                                        <span class="stock-badge stock-available" style="position: static; font-size: 10px; padding: 2px 6px;">Lunas</span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-low" style="position: static; font-size: 10px; padding: 2px 6px;">Belum Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="display:inline-flex; align-items:center; gap:4px; background:<?= $badge['bg'] ?>; color:<?= $badge['color'] ?>; padding:4px 10px; border-radius:14px; font-weight:700; font-size:12px;">
                                        <?= $badge['label'] ?>
                                    </span>
                                    <?php if ($ord['order_status'] === 'rak_mandiri'): ?>
                                        <div style="font-size: 11px; font-weight: 800; color: #dc2626; margin-top: 4px;">
                                            <?= htmlspecialchars($ord['shelf_slot'] ?: 'Rak A-01') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: #64748b;">
                                        Dipesan: <?= date('H:i', strtotime($ord['created_at'])) ?>
                                    </div>
                                    <?php if ($elapsedMinutes !== null && $ord['order_status'] === 'siap_diambil'): ?>
                                        <div style="font-size: 11px; font-weight: 700; color: <?= $elapsedMinutes >= 15 ? '#dc2626' : '#059669' ?>;">
                                            Di counter: <?= $elapsedMinutes ?> mnt / 20 mnt
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                        <?php if ($ord['order_status'] === 'menunggu_konfirmasi'): ?>
                                            <button type="button" class="btn-action btn-action-primary" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'dipanggang')">
                                                Mulai Panggang
                                            </button>
                                        <?php elseif ($ord['order_status'] === 'dipanggang'): ?>
                                            <button type="button" class="btn-action btn-action-blue" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'sedang_dikemas')">
                                                Mulai Kemas
                                            </button>
                                        <?php elseif ($ord['order_status'] === 'sedang_dikemas' || $ord['order_status'] === 'dikemas'): ?>
                                            <button type="button" class="btn-action btn-action-green" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'siap_diambil')">
                                                Siap Diambil
                                            </button>
                                        <?php elseif ($ord['order_status'] === 'siap_diambil'): ?>
                                            <button type="button" class="btn-action btn-action-green" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'selesai')">
                                                Diambil Pelanggan
                                            </button>
                                            <button type="button" class="btn-action btn-action-red" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'rak_mandiri')">
                                                Pindah Rak Mandiri
                                            </button>
                                        <?php elseif ($ord['order_status'] === 'rak_mandiri'): ?>
                                            <button type="button" class="btn-action btn-action-green" onclick="updateOrderStatus(<?= $ord['id'] ?>, 'selesai')">
                                                Sudah Diambil dari Rak
                                            </button>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">Pesanan Selesai</span>
                                        <?php endif; ?>
                                        <a href="../tracking.php?order_code=<?= urlencode($ord['order_code']) ?>" target="_blank" class="btn-action" style="background:#f1f5f9; color:#475569;">
                                            Cek
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <!-- Tab Manajemen Varian & Stok Roti/Minuman -->
            <div style="background: white; padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 800; color: #1e293b;">Katalog Varian & Kontrol Stok Real-Time</h2>
                        <p style="font-size: 13px; color: var(--text-muted);">
                            Stok langsung otomatis terpotong saat pelanggan menyelesaikan pesanan online (Keep Pesanan).
                        </p>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Varian Menu</th>
                            <th>Kategori</th>
                            <th>Harga Satuan</th>
                            <th>Waktu Panggang</th>
                            <th>Sisa Stok Saat Ini</th>
                            <th style="text-align: right;">Aksi Tambah / Atur Stok Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $m): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 800; font-size: 14px;"><?= htmlspecialchars($m['name']) ?></div>
                                    <div style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($m['description']) ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; background: <?= $m['category'] === 'roti' ? '#ffedd5' : '#dbeafe' ?>; color: <?= $m['category'] === 'roti' ? '#9a3412' : '#1e40af' ?>; padding: 3px 8px; border-radius: 6px;">
                                        <?= $m['category'] === 'roti' ? 'Salt Bread' : 'Minuman' ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--primary);">
                                    <?= rupiah($m['price']) ?>
                                </td>
                                <td style="font-size: 13px; color: #475569;">
                                    ~<?= $m['bake_time_mins'] ?> Menit
                                </td>
                                <td>
                                    <span id="stockLabel<?= $m['id'] ?>" class="stock-badge <?= $m['stock'] > 3 ? 'stock-available' : ($m['stock'] > 0 ? 'stock-low' : 'stock-out') ?>" style="position: static; font-size: 13px; padding: 4px 10px;">
                                        <strong id="stockVal<?= $m['id'] ?>"><?= $m['stock'] ?></strong> pcs
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                        <button type="button" class="stock-control-btn" onclick="adjustStock(<?= $m['id'] ?>, -1)">&minus;1</button>
                                        <button type="button" class="stock-control-btn" style="background:#fef3c7; color:#92400e;" onclick="adjustStock(<?= $m['id'] ?>, 5)">+5</button>
                                        <button type="button" class="stock-control-btn" style="background:#fef3c7; color:#92400e;" onclick="adjustStock(<?= $m['id'] ?>, 10)">+10</button>
                                        <button type="button" class="stock-control-btn" style="background:#dcfce7; color:#166534;" onclick="adjustStock(<?= $m['id'] ?>, 20)">+20 pcs</button>
                                        <button type="button" class="stock-control-btn" onclick="customStock(<?= $m['id'] ?>)">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        function testChime() {
            soundNotifier.init();
            soundNotifier.playChime();
        }

        // Update status order via AJAX
        async function updateOrderStatus(orderId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('action', 'change_status');
                formData.append('order_id', orderId);
                formData.append('new_status', newStatus);

                const res = await fetch('../api/update_order.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    if (newStatus === 'siap_diambil') {
                        soundNotifier.playChime();
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah status pesanan.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan jaringan.');
            }
        }

        // Adjust stock +/-
        async function adjustStock(menuId, delta) {
            try {
                const formData = new FormData();
                formData.append('action', 'adjust_stock');
                formData.append('menu_id', menuId);
                formData.append('delta', delta);

                const res = await fetch('../api/update_order.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    const el = document.getElementById('stockVal' + menuId);
                    if (el) el.textContent = data.new_stock;
                    
                    const badge = document.getElementById('stockLabel' + menuId);
                    if (badge) {
                        badge.className = 'stock-badge ' + (data.new_stock > 3 ? 'stock-available' : (data.new_stock > 0 ? 'stock-low' : 'stock-out'));
                    }
                } else {
                    alert(data.message || 'Gagal mengubah stok.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Custom stock prompt
        function customStock(menuId) {
            const current = document.getElementById('stockVal' + menuId).textContent;
            const input = prompt('Masukkan jumlah stok baru:', current);
            if (input !== null && !isNaN(parseInt(input))) {
                const val = parseInt(input);
                const formData = new FormData();
                formData.append('action', 'set_stock');
                formData.append('menu_id', menuId);
                formData.append('stock', val);

                fetch('../api/update_order.php', {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            }
        }

        // Auto reload tab orders every 10 seconds for new incoming orders
        <?php if ($currentTab === 'orders'): ?>
        setInterval(() => {
            // Optional background refresh
        }, 10000);
        <?php endif; ?>
    </script>
</body>
</html>

