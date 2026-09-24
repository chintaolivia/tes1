<?php
/**
 * Halaman Utama Pemesanan & Antrian Salt Bread
 * Menampilkan Menu, Varian, & Sisa Stok Real-Time
 */

require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

// Ambil kategori dari filter jika ada
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';

if ($category === 'roti' || $category === 'minuman') {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE is_active = 1 AND category = ? ORDER BY id ASC");
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY category ASC, id ASC");
}
$menuItems = $stmt->fetchAll();

// Hitung total antrian aktif saat ini
$queueCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('menunggu_konfirmasi', 'dipanggang', 'dikemas', 'siap_diambil')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Little Salt Bread Blok M - Pesan Online & Antrian Real-Time</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon" style="font-size: 16px; font-weight: 800; color: var(--primary);">SB</div>
                <div>
                    <div class="brand-title"><span class="brand-little">little</span> SALT<span>BREAD</span></div>
                    <div class="brand-tag">Blok M &bull; Senin-Minggu 10:00 - 20:00</div>
                </div>
            </a>
            <div class="nav-links">
                <a href="index.php" class="nav-link active">Menu & Pesan</a>
                <a href="monitor.php" target="_blank" class="nav-link">Monitor TV</a>
                <a href="admin/index.php" class="nav-link">Staff Dapur</a>
                <button type="button" class="btn-nav-cart" onclick="openCartModal()">
                    Keranjang <span id="navCartCount" style="display:none; background:white; color:#b45309; padding:2px 7px; border-radius:12px; font-size:11px;">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">Little Salt Bread Blok M &bull; Senin-Minggu 10:00 - 20:00</div>
                <h1 class="hero-title">Salt Bread Renyah Gurih Langsung Dari Oven</h1>
                <p class="hero-desc">
                    Pesan online sekarang, amankan stok varian favoritmu dengan pembayaran instan (QRIS/Transfer), lalu pantau proses pemanggangan hingga siap diambil!
                </p>
                <div class="hero-features">
                    <div class="hero-feature-item">Dipanggang Fresh</div>
                    <div class="hero-feature-item">Estimasi Waktu Live</div>
                    <div class="hero-feature-item">Notifikasi Saat Siap</div>
                    <div class="hero-feature-item">Rak Ambil Mandiri 20 Mnt</div>
                </div>
            </div>

            <!-- Cek Status Antrian Cepat -->
            <div class="hero-tracking-card">
                <h4>Lacak Antrian Pesananmu</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                    Sudah punya nomor antrian? Masukkan di sini untuk pantau status live.
                </p>
                <form action="tracking.php" method="GET" class="hero-search-form">
                    <input type="text" name="q" class="hero-search-input" placeholder="Contoh: SB-001" required>
                    <button type="submit" class="btn-add-cart" style="padding: 10px 16px;">Lacak</button>
                </form>
                <div style="margin-top: 10px; font-size: 11px; color: #b45309; font-weight: 600;">
                    Sedang ada <strong><?= $queueCount ?> antrian</strong> dalam proses dapur
                </div>
            </div>
        </section>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <a href="index.php" class="tab-btn <?= $category === 'all' ? 'active' : '' ?>">Semua Menu</a>
            <a href="index.php?category=roti" class="tab-btn <?= $category === 'roti' ? 'active' : '' ?>">Salt Bread Varian</a>
            <a href="index.php?category=minuman" class="tab-btn <?= $category === 'minuman' ? 'active' : '' ?>">Minuman & Kopi</a>
        </div>

        <!-- Menu & Stock Grid -->
        <div class="menu-grid">
            <?php foreach ($menuItems as $item): ?>
                <?php 
                    $stock = (int)$item['stock'];
                    $imgBase = !empty($item['image_url']) ? $item['image_url'] : 'assets/images/classic.svg';
                    $imgVer = file_exists(__DIR__ . '/' . $imgBase) ? filemtime(__DIR__ . '/' . $imgBase) : time();
                    $img = $imgBase . '?v=' . $imgVer;
                ?>
                <div class="menu-card">
                    <div class="menu-img-wrap">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-img">
                        
                        <!-- Real-time Stock Badge -->
                        <?php if ($stock > 3): ?>
                            <div class="stock-badge stock-available">
                                Sisa <?= $stock ?> pcs
                            </div>
                        <?php elseif ($stock > 0): ?>
                            <div class="stock-badge stock-low">
                                Sisa <?= $stock ?> pcs (Hampir Habis)
                            </div>
                        <?php else: ?>
                            <div class="stock-badge stock-out">
                                Stok Habis
                            </div>
                        <?php endif; ?>

                        <?php if ($item['category'] === 'roti'): ?>
                            <div class="bake-time-badge">
                                ~<?= (int)$item['bake_time_mins'] ?> mnt
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="menu-body">
                        <h3 class="menu-title"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="menu-desc"><?= htmlspecialchars($item['description']) ?></p>
                        
                        <div class="menu-footer">
                            <div class="menu-price"><?= rupiah($item['price']) ?></div>
                            <button 
                                type="button" 
                                class="btn-add-cart" 
                                <?= $stock <= 0 ? 'disabled' : '' ?>
                                onclick="cart.addItem(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>, <?= $stock ?>, '<?= $img ?>')"
                            >
                                <?= $stock > 0 ? '+ Tambah' : 'Habis' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Floating Bottom Cart Bar -->
    <div id="cartFloatingBar" class="cart-floating-bar" style="display: none;">
        <div class="cart-float-info">
            <span id="cartFloatQty" class="cart-float-qty">0 item</span>
            <span id="cartFloatPrice" class="cart-float-price">Rp 0</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="openCartModal()" style="background:#334155; color:white; border:none; padding:10px 14px; border-radius:18px; font-size:13px; font-weight:700; cursor:pointer;">
                Lihat Detail
            </button>
            <a href="checkout.php" class="btn-float-checkout">
                Lanjut Bayar &rarr;
            </a>
        </div>
    </div>

    <!-- Cart Modal Dialog -->
    <div id="cartModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Keranjang Pesanan Online</h3>
                <button type="button" class="btn-close-modal" onclick="closeCartModal()">&times;</button>
            </div>

            <div id="cartModalItems" class="cart-items-list">
                <!-- Diisi via app.js -->
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span>Estimasi Penyiapan</span>
                    <span style="font-weight: 700; color: #b45309;">~15-20 Menit</span>
                </div>
                <div class="summary-row total">
                    <span>Total Pembayaran</span>
                    <span id="cartModalTotal" style="color: var(--primary);">Rp 0</span>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 14px;">
                <button type="button" onclick="cart.clearCart()" style="flex: 1; padding: 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 700; cursor: pointer; color: #64748b;">
                    Kosongkan
                </button>
                <a href="checkout.php" class="btn-submit-order" style="flex: 2; text-decoration: none;">
                    Checkout & Amankan Stok &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script>
        // Meminta izin Web Notification saat pertama kali load agar customer siap menerima notif selesai
        window.addEventListener('load', () => {
            if ('Notification' in window && Notification.permission === 'default') {
                setTimeout(() => {
                    Notification.requestPermission();
                }, 3000);
            }
        });
    </script>
</body>
</html>

