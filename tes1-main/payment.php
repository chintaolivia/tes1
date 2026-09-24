<?php
/**
 * Halaman Pembayaran di Awal (QRIS / Transfer Bank)
 * Mencegah pesanan fiktif & verifikasi instan
 */

require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$orderCode = trim($_GET['order_code'] ?? '');
if (empty($orderCode)) {
    header("Location: index.php");
    exit;
}

// Ambil data order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
$stmt->execute([$orderCode]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan dengan kode {$orderCode} tidak ditemukan.");
}

$message = null;

// Handle Aksi Simulasi Bayar Instan atau Upload Bukti
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'simulate_pay') {
        // Simulasi pembayaran sukses seketika (langsung masuk ke status dipanggang)
        $updateStmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', 
                order_status = 'dipanggang',
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$order['id']]);

        header("Location: tracking.php?order_code=" . urlencode($orderCode) . "&paid=1");
        exit;
    }

    if ($action === 'upload_proof') {
        $proofPath = 'uploads/proof_' . time() . '_' . $order['id'] . '.png';
        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0777, true);
        }

        if (!empty($_FILES['payment_proof']['tmp_name'])) {
            move_uploaded_file($_FILES['payment_proof']['tmp_name'], __DIR__ . '/' . $proofPath);
        } else {
            $proofPath = 'simulated_proof.jpg';
        }

        // Update status menunggu konfirmasi admin
        $updateStmt = $pdo->prepare("
            UPDATE orders 
            SET payment_proof = ?,
                order_status = 'menunggu_konfirmasi',
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$proofPath, $order['id']]);

        header("Location: tracking.php?order_code=" . urlencode($orderCode) . "&submitted=1");
        exit;
    }
}

// Ambil item pesanan
$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$order['id']]);
$orderItems = $itemsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan <?= htmlspecialchars($order['queue_number']) ?> - Salt Bread</title>
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
                    <div class="brand-tag">Pembayaran di Awal</div>
                </div>
            </a>
            <div class="nav-links">
                <a href="tracking.php?order_code=<?= urlencode($orderCode) ?>" class="nav-link">Lacak Pesanan &rarr;</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div style="max-width: 680px; margin: 0 auto;">

            <!-- Header Info Tagihan -->
            <div style="text-align: center; margin-bottom: 24px;">
                <span class="stock-badge stock-available" style="position: static; display: inline-flex; margin-bottom: 8px;">
                    Stok Varian Sudah Di-Keep Untuk Anda
                </span>
                <h1 style="font-size: 26px; font-weight: 800; color: #451a03;">Selesaikan Pembayaran</h1>
                <p style="color: var(--text-muted); font-size: 14px;">
                    Nomor Antrian Anda: <strong style="color: var(--primary); font-size: 18px;"><?= htmlspecialchars($order['queue_number']) ?></strong>
                </p>
            </div>

            <!-- Card Pembayaran -->
            <div class="form-card" style="border: 2px solid #fde68a;">
                
                <!-- Countdown Bayar -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <span style="font-size: 13px; font-weight: 700; color: #92400e;">Sisa Waktu Pembayaran:</span>
                    <span id="paymentCountdown" style="font-size: 16px; font-weight: 800; color: #b45309;">14:59</span>
                </div>

                <!-- Total yang harus dibayar -->
                <div style="text-align: center; padding: 12px 0 20px; border-bottom: 1px dashed #e2e8f0; margin-bottom: 20px;">
                    <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Total Pembayaran</div>
                    <div style="font-size: 34px; font-weight: 900; color: var(--primary); margin: 4px 0;"><?= rupiah($order['total_amount']) ?></div>
                    <div style="font-size: 12px; color: #64748b;">Metode: <strong><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></strong></div>
                </div>

                <?php if ($order['payment_method'] === 'qris'): ?>
                    <!-- Tampilan QRIS -->
                    <div style="text-align: center; margin-bottom: 24px;">
                        <p style="font-size: 13px; color: var(--text); font-weight: 600; margin-bottom: 12px;">
                            Scan QRIS di bawah ini dengan aplikasi mobile banking atau e-wallet:
                        </p>
                        <div style="max-width: 320px; margin: 0 auto; box-shadow: var(--shadow); border-radius: 16px; overflow: hidden;">
                            <img src="assets/images/qris_mockup.svg" alt="QRIS Salt Bread" style="width: 100%; display: block;">
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tampilan Rekening Bank -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 4px;">REKENING TUJUAN TRANSFER:</div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <div>
                                <div style="font-size: 16px; font-weight: 800; color: #1e293b;">
                                    <?= $order['payment_method'] === 'bca' ? 'Bank Central Asia (BCA)' : 'Bank Mandiri' ?>
                                </div>
                                <div style="font-size: 18px; font-weight: 800; color: var(--primary);" id="bankAccNumber">
                                    <?= $order['payment_method'] === 'bca' ? '8830 192 881' : '137 00 9988 1121' ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b;">a/n Salt Bread Artisan Bakery</div>
                            </div>
                            <button type="button" onclick="copyAccNumber()" class="btn-action btn-action-primary" style="padding: 8px 14px;">
                                Salin Rekening
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol Aksi: Simulasi Bayar Instan (Untuk Pengujian Cepat & Nyaman) -->
                <form method="POST" style="margin-bottom: 12px;">
                    <input type="hidden" name="action" value="simulate_pay">
                    <button type="submit" class="btn-submit-order" style="background: #059669;" onclick="cart.clearCart()">
                        Simulasi Bayar Sekarang (Instan Lunas & Masuk Antrian)
                    </button>
                </form>

                <!-- Atau Form Upload Bukti Transfer Manual -->
                <details style="margin-top: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
                    <summary style="font-weight: 700; font-size: 13px; cursor: pointer; color: #475569;">
                        Upload Bukti Pembayaran / Struk Transfer Manual
                    </summary>
                    <form method="POST" enctype="multipart/form-data" style="margin-top: 14px;">
                        <input type="hidden" name="action" value="upload_proof">
                        <div class="form-group">
                            <label class="form-label">Pilih Foto Bukti Transfer</label>
                            <input type="file" name="payment_proof" accept="image/*" class="form-control">
                        </div>
                        <button type="submit" class="btn-action btn-action-blue" style="width: 100%; padding: 10px;" onclick="cart.clearCart()">
                            Kirim Bukti Pembayaran
                        </button>
                    </form>
                </details>

            </div>

            <!-- Rincian Pesanan Singkat -->
            <div class="form-card">
                <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Daftar Item dalam Pesanan ini:</h4>
                <?php foreach ($orderItems as $it): ?>
                    <div class="cart-item-row" style="padding: 6px 0;">
                        <div class="cart-item-info">
                            <span style="font-weight: 700; font-size: 13px;"><?= htmlspecialchars($it['item_name']) ?></span>
                            <span style="color: var(--text-muted); font-size: 12px;">&times; <?= $it['quantity'] ?></span>
                        </div>
                        <div style="font-weight: 700; font-size: 13px;"><?= rupiah($it['subtotal']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script>
        // Kosongkan keranjang di localStorage karena pesanan sudah berhasil dibuat
        document.addEventListener('DOMContentLoaded', () => {
            cart.clearCart();
        });

        // Countdown Timer 15 Menit
        let secondsLeft = 15 * 60;
        const countdownEl = document.getElementById('paymentCountdown');
        const timer = setInterval(() => {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(timer);
                countdownEl.textContent = '00:00 (Kadaluarsa)';
            } else {
                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                countdownEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
        }, 1000);

        function copyAccNumber() {
            const acc = document.getElementById('bankAccNumber').textContent.trim();
            navigator.clipboard.writeText(acc.replace(/\s+/g, '')).then(() => {
                alert('Nomor rekening berhasil disalin!');
            });
        }
    </script>
</body>
</html>

