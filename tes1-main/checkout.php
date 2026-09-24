<?php
/**
 * Halaman Checkout & Validasi Stok
 * Mengunci/nge-keep stok saat pesanan dibuat & memproses metode pembayaran di awal
 */

require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$errorMessage = null;

// Proses Form Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName   = trim($_POST['customer_name'] ?? '');
    $customerPhone  = trim($_POST['customer_phone'] ?? '');
    $orderType      = trim($_POST['order_type'] ?? 'takeaway');
    $notes          = trim($_POST['notes'] ?? '');
    $paymentMethod  = trim($_POST['payment_method'] ?? 'qris');
    $cartJson       = $_POST['cart_data'] ?? '[]';
    $cartItems      = json_decode($cartJson, true);

    if (empty($customerName) || empty($customerPhone)) {
        $errorMessage = "Mohon lengkapi nama pemesan dan nomor WhatsApp/telepon.";
    } elseif (empty($cartItems) || !is_array($cartItems)) {
        $errorMessage = "Keranjang belanja Anda masih kosong. Silakan pilih menu terlebih dahulu.";
    } else {
        try {
            // Mulai Transaksi Database agar pengurangan stok aman (Atomic / ACID)
            $pdo->beginTransaction();

            $totalAmount = 0;
            $itemsToInsert = [];
            $maxBakeTime = 12;

            foreach ($cartItems as $item) {
                $itemId = (int)$item['id'];
                $qty    = (int)$item['qty'];

                if ($qty <= 0) continue;

                // Kunci baris menu untuk pengecekan stok akurat (SELECT FOR UPDATE)
                $stmtCheck = $pdo->prepare("SELECT id, name, price, stock, bake_time_mins FROM menu_items WHERE id = ? FOR UPDATE");
                $stmtCheck->execute([$itemId]);
                $menuDb = $stmtCheck->fetch();

                if (!$menuDb) {
                    throw new Exception("Menu dengan ID #{$itemId} tidak ditemukan.");
                }

                // Validasi Stok
                if ($menuDb['stock'] < $qty) {
                    throw new Exception("Maaf, stok '{$menuDb['name']}' tidak mencukupi! Tersisa {$menuDb['stock']} pcs, Anda memesan {$qty} pcs.");
                }

                $subtotal = $menuDb['price'] * $qty;
                $totalAmount += $subtotal;

                if ((int)$menuDb['bake_time_mins'] > $maxBakeTime) {
                    $maxBakeTime = (int)$menuDb['bake_time_mins'];
                }

                // Kurangi stok sekarang juga agar pesanan langsung ter-keep!
                $stmtDeduct = $pdo->prepare("UPDATE menu_items SET stock = stock - ? WHERE id = ?");
                $stmtDeduct->execute([$qty, $itemId]);

                $itemsToInsert[] = [
                    'menu_item_id' => $menuDb['id'],
                    'item_name'    => $menuDb['name'],
                    'quantity'     => $qty,
                    'price'        => $menuDb['price'],
                    'subtotal'     => $subtotal,
                ];
            }

            if (empty($itemsToInsert)) {
                throw new Exception("Keranjang pesanan tidak valid.");
            }

            // Hitung estimasi waktu tunggu berdasarkan antrian yang sedang berjalan di dapur
            $stmtQueue = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('menunggu_konfirmasi', 'dipanggang', 'dikemas')");
            $activeQueueAhead = (int)$stmtQueue->fetchColumn();
            
            // Rumus estimasi: Waktu panggangan menu terlama + (3 menit * antrian di depan)
            $estimatedMinutes = $maxBakeTime + ($activeQueueAhead * 3);
            if ($estimatedMinutes > 60) $estimatedMinutes = 60; // Max 60 mnt

            // Generate Kode Antrian & Order Code
            $queueNumber = generateQueueNumber($pdo);
            $orderCode   = 'SB' . date('ymd') . strtoupper(substr(uniqid(), -4));

            // Simpan Order
            $stmtOrder = $pdo->prepare("
                INSERT INTO orders (
                    queue_number, order_code, customer_name, customer_phone, order_type, 
                    notes, total_amount, payment_method, payment_status, order_status, 
                    estimated_minutes, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, 'unpaid', 'menunggu_konfirmasi', 
                    ?, NOW()
                )
            ");
            $stmtOrder->execute([
                $queueNumber, $orderCode, $customerName, $customerPhone, $orderType,
                $notes, $totalAmount, $paymentMethod, $estimatedMinutes
            ]);
            $orderId = $pdo->lastInsertId();

            // Simpan Item Pesanan
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, item_name, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($itemsToInsert as $ins) {
                $stmtItem->execute([
                    $orderId, $ins['menu_item_id'], $ins['item_name'], 
                    $ins['quantity'], $ins['price'], $ins['subtotal']
                ]);
            }

            // Komit transaksi
            $pdo->commit();

            // Redirect ke halaman pembayaran
            header("Location: payment.php?order_code=" . urlencode($orderCode));
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Pembayaran Awal - Salt Bread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon" style="font-size: 16px; font-weight: 800; color: var(--primary);">SB</div>
                <div>
                    <div class="brand-title"><span class="brand-little">little</span> SALT<span>BREAD</span></div>
                    <div class="brand-tag">Konfirmasi Pesanan</div>
                </div>
            </a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">&larr; Kembali ke Menu</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div style="max-width: 900px; margin: 0 auto;">
            
            <div style="margin-bottom: 20px;">
                <h1 style="font-size: 26px; font-weight: 800; color: #451a03;">Checkout & Kunci Stok Pesanan</h1>
                <p style="color: var(--text-muted); font-size: 14px;">
                    Sistem kami mewajibkan pembayaran di awal untuk menjaga pesanan tetap hangat dan mencegah orderan fiktif.
                </p>
            </div>

            <?php if ($errorMessage): ?>
                <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkoutForm" onsubmit="return validateBeforeSubmit()">
                <input type="hidden" name="cart_data" id="cartDataInput">

                <div class="checkout-grid">
                    
                    <!-- Kolom Kiri: Info Pemesan & Pilihan Bayar -->
                    <div>
                        <!-- Data Pemesan -->
                        <div class="form-card">
                            <h3 class="form-title">Informasi Pemesan</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Contoh: Tata Cantik" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nomor WhatsApp / Telepon *</label>
                                <input type="tel" name="customer_phone" class="form-control" placeholder="Contoh: 081234567890" required>
                                <small style="font-size: 11px; color: var(--text-muted);">Digunakan untuk notifikasi status pesanan.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tipe Pesanan</label>
                                <div style="display: flex; gap: 12px;">
                                    <label style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="order_type" value="takeaway" checked>
                                        <span style="font-weight: 700; font-size: 13px;">Bawa Pulang (Takeaway)</span>
                                    </label>
                                    <label style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="order_type" value="dine_in">
                                        <span style="font-weight: 700; font-size: 13px;">Makan di Tempat</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Catatan Tambahan (Opsional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Jangan terlalu asin, atau minta es dipisah..."></textarea>
                            </div>
                        </div>

                        <!-- Pilihan Metode Pembayaran Di Awal -->
                        <div class="form-card">
                            <h3 class="form-title">Pembayaran di Awal (Bebas Fiktif)</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
                                Roti baru akan dipanggang dan stok di-keep setelah Anda melakukan konfirmasi pembayaran.
                            </p>

                            <div class="payment-methods">
                                <label class="payment-card">
                                    <input type="radio" name="payment_method" value="qris" checked>
                                    <div class="payment-icon" style="font-weight:800; font-size:12px; color:#d97706;">QRIS</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 800; font-size: 14px;">QRIS Instan (Rekomendasi)</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">BCA Mobile, Livin', GoPay, OVO, ShopeePay, DANA</div>
                                    </div>
                                </label>

                                <label class="payment-card">
                                    <input type="radio" name="payment_method" value="bca">
                                    <div class="payment-icon" style="font-weight:800; font-size:12px; color:#2563eb;">BCA</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 800; font-size: 14px;">Transfer Bank BCA</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">No. Rek: 8830-192-881 a/n Salt Bread Bakery</div>
                                    </div>
                                </label>

                                <label class="payment-card">
                                    <input type="radio" name="payment_method" value="mandiri">
                                    <div class="payment-icon" style="font-weight:800; font-size:12px; color:#059669;">MDR</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 800; font-size: 14px;">Transfer Bank Mandiri</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">No. Rek: 137-00-9988112-1 a/n Salt Bread Bakery</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian Belanja -->
                    <div>
                        <div class="form-card" style="position: sticky; top: 90px;">
                            <h3 class="form-title">Rincian Pesanan</h3>
                            
                            <div id="checkoutItemsList" class="cart-items-list">
                                <!-- Diisi secara dinamis dari localStorage -->
                            </div>

                            <div class="summary-box">
                                <div class="summary-row">
                                    <span>Estimasi Waktu Tunggu</span>
                                    <span style="font-weight: 800; color: #b45309;" id="estWaitTime">~15-20 Menit</span>
                                </div>
                                <div class="summary-row">
                                    <span>Biaya Layanan & Kemasan</span>
                                    <span style="font-weight: 700; color: #059669;">GRATIS (Rp 0)</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total Tagihan</span>
                                    <span id="checkoutTotalAmount" style="color: var(--primary);">Rp 0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn-submit-order">
                                Kunci Stok & Bayar Sekarang &rarr;
                            </button>

                            <div style="margin-top: 14px; text-align: center; font-size: 11px; color: var(--text-muted);">
                                Stok varian roti langsung di-keep untuk Anda setelah klik tombol di atas.
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script>
        function renderCheckoutList() {
            const listEl = document.getElementById('checkoutItemsList');
            const totalEl = document.getElementById('checkoutTotalAmount');
            const inputEl = document.getElementById('cartDataInput');

            if (cart.items.length === 0) {
                listEl.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #94a3b8;">
                        <p>Keranjang Anda kosong. Kembali ke <a href="index.php" style="color: var(--primary); font-weight: 700;">Halaman Menu</a></p>
                    </div>`;
                totalEl.textContent = 'Rp 0';
                return;
            }

            listEl.innerHTML = cart.items.map(item => `
                <div class="cart-item-row">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-unit-price">${cart.formatRupiah(item.price)} &times; ${item.qty} pcs</div>
                    </div>
                    <div style="font-weight: 800; color: var(--text);">
                        ${cart.formatRupiah(item.price * item.qty)}
                    </div>
                </div>
            `).join('');

            totalEl.textContent = cart.formatRupiah(cart.getTotalPrice());
            inputEl.value = JSON.stringify(cart.items);
        }

        function validateBeforeSubmit() {
            if (cart.items.length === 0) {
                alert("Keranjang pesanan masih kosong! Silakan pilih varian roti terlebih dahulu.");
                window.location.href = 'index.php';
                return false;
            }
            document.getElementById('cartDataInput').value = JSON.stringify(cart.items);
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderCheckoutList();
        });
    </script>
</body>
</html>

