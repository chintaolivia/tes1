<?php
/**
 * Halaman Pelacakan Antrian Real-Time untuk Pelanggan
 * Menampilkan nomor antrian, status 5 tahap, estimasi waktu, notifikasi suara, 
 * dan peringatan otomatis 20 menit (Rak Pengambilan Mandiri)
 */

require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$orderCode = trim($_GET['order_code'] ?? '');
$searchQ   = trim($_GET['q'] ?? '');

$order = null;

if (!empty($orderCode)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();
} elseif (!empty($searchQ)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE queue_number = ? OR order_code = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$searchQ, $searchQ]);
    $order = $stmt->fetch();
}

if (!$order) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesanan Tidak Ditemukan - Salt Bread</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body style="display:flex; align-items:center; justify-content:center; min-height:100vh;">
        <div style="text-align:center; padding:30px; background:white; border-radius:16px; border:1px solid #fde68a; max-width:400px; box-shadow:var(--shadow);">
            <div style="font-size:48px; margin-bottom:12px;">🔍</div>
            <h2 style="color:#b45309; margin-bottom:8px;">Pesanan Tidak Ditemukan</h2>
            <p style="color:#64748b; font-size:14px; margin-bottom:20px;">
                Nomor antrian atau kode pesanan tidak terdaftar di sistem.
            </p>
            <a href="index.php" class="btn-float-checkout" style="display:inline-block;">&larr; Kembali ke Menu</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ambil item pesanan
$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$order['id']]);
$orderItems = $itemsStmt->fetchAll();

$badge = getStatusBadge($order['order_status']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian <?= htmlspecialchars($order['queue_number']) ?> - Live Tracking Salt Bread</title>
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
                    <div class="brand-tag">Live Order Tracking</div>
                </div>
            </a>
            <div class="nav-links">
                <button type="button" class="btn-sound-toggle" id="btnEnableSound" onclick="toggleAudio()">
                    Aktifkan Suara Notifikasi
                </button>
                <a href="index.php" class="nav-link">+ Pesan Baru</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="tracking-wrapper">

            <!-- Card Nomor Antrian Utama -->
            <div class="queue-badge-card">
                <div class="queue-badge-label">NOMOR ANTRIAN ANDA</div>
                <div class="queue-badge-num" id="displayQueueNum"><?= htmlspecialchars($order['queue_number']) ?></div>
                <div class="queue-code-sub">
                    Pemesan: <strong><?= htmlspecialchars($order['customer_name']) ?></strong> • Kode: <?= htmlspecialchars($order['order_code']) ?>
                </div>

                <!-- Status Live Badge -->
                <div style="margin-top: 14px;">
                    <span id="displayStatusBadge" style="display:inline-flex; align-items:center; gap:6px; background:<?= $badge['bg'] ?>; color:<?= $badge['color'] ?>; padding:8px 20px; border-radius:30px; font-weight:800; font-size:15px; border: 1px solid <?= $badge['color'] ?>33;">
                        <span id="statusText"><?= $badge['label'] ?></span>
                    </span>
                </div>
            </div>

            <!-- Stepper 5 Tahap Status Pesanan -->
            <div class="stepper" id="orderStepper">
                <!-- Step 1: Konfirmasi -->
                <div class="step-item" id="step1">
                    <div class="step-circle">1</div>
                    <div class="step-title">Menunggu Konfirmasi</div>
                </div>
                <!-- Step 2: Dipanggang -->
                <div class="step-item" id="step2">
                    <div class="step-circle">2</div>
                    <div class="step-title">Sedang Dipanggang</div>
                </div>
                <!-- Step 3: Dikemas -->
                <div class="step-item" id="step3">
                    <div class="step-circle">3</div>
                    <div class="step-title">Sedang Dikemas</div>
                </div>
                <!-- Step 4: Siap Diambil -->
                <div class="step-item" id="step4">
                    <div class="step-circle">4</div>
                    <div class="step-title">Siap Diambil</div>
                </div>
                <!-- Step 5: Selesai / Rak Mandiri -->
                <div class="step-item" id="step5">
                    <div class="step-circle">5</div>
                    <div class="step-title">Pengambilan</div>
                </div>
            </div>

            <!-- 20-Minute Alert Box: Rak Pengambilan Mandiri -->
            <div id="shelfWarningBox" class="alert-shelf-warning" style="display: none;">
                <div>
                    <div class="alert-shelf-title">PERHATIAN: Pesanan Dialihkan ke Rak Pengambilan Mandiri</div>
                    <div class="alert-shelf-desc">
                        Pesanan Anda telah berada di counter penyerahan lebih dari <strong>20 menit</strong>.
                        Demi menjaga kualitas dan kehangatan roti tetap renyah, pesanan Anda telah dipindahkan ke:
                        <div style="background: #ffffff; padding: 10px 14px; border-radius: 8px; margin: 8px 0; font-weight: 800; color: #991b1b; border: 1px solid #f87171; display: inline-block;">
                            <?= htmlspecialchars($order['shelf_slot'] ?: 'RAK PENGAMBILAN MANDIRI (Slot A-01)') ?>
                        </div>
                        <br>
                        Silakan langsung ambil paket Anda di rak penghangat outlet Salt Bread dengan menunjukkan bukti antrian nomor <strong><?= htmlspecialchars($order['queue_number']) ?></strong> ini kepada staf jika diperlukan.
                    </div>
                </div>
            </div>

            <!-- Timer Menunggu di Counter (muncul saat Siap Diambil) -->
            <div id="readyCounterTimerBox" style="display: none; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; padding: 14px 20px; margin-bottom: 20px; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #065f46;">Roti Sudah Siap Di Counter</div>
                    <div style="font-size: 12px; color: #047857;">Harap ambil di counter sebelum batas 20 menit agar tidak dialihkan ke rak mandiri.</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase;">Waktu di Counter:</div>
                    <div id="readyTimeElapsed" style="font-size: 20px; font-weight: 900; color: #065f46;">00:00 / 20:00</div>
                </div>
            </div>

            <!-- Banner Estimasi Waktu Tunggu Pemanggangan -->
            <div class="estimate-banner" id="estimateBanner">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Estimasi Roti Matang & Siap:</div>
                    <div class="estimate-time" id="estimateMinutesText">~<?= (int)$order['estimated_minutes'] ?> Menit</div>
                </div>
                <div style="text-align: right;">
                    <span class="stock-badge stock-available" style="position: static; font-size: 12px;">
                        Fresh From Oven
                    </span>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;" id="queueAheadText">
                        Harap menunggu di area tunggu outlet
                    </div>
                </div>
            </div>

            <!-- Ringkasan Roti yang Dipesan -->
            <div class="form-card">
                <h3 class="form-title">Menu yang Sedang Disiapkan</h3>
                <div class="cart-items-list">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="cart-item-row">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                <div class="cart-item-unit-price"><?= rupiah($item['price']) ?> &times; <?= $item['quantity'] ?> pcs</div>
                            </div>
                            <div style="font-weight: 800; color: var(--primary);">
                                <?= rupiah($item['subtotal']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-box" style="margin-bottom: 0;">
                    <div class="summary-row">
                        <span>Metode Pembayaran</span>
                        <span style="font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Status Pembayaran</span>
                        <span style="font-weight: 800; color: <?= $order['payment_status'] === 'paid' ? '#059669' : '#d97706' ?>;">
                            <?= $order['payment_status'] === 'paid' ? 'LUNAS (Terverifikasi)' : 'Menunggu Verifikasi' ?>
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Nilai Pesanan</span>
                        <span style="color: var(--primary);"><?= rupiah($order['total_amount']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Tombol Bantuan -->
            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <a href="index.php" class="btn-action btn-action-primary" style="flex: 1; padding: 12px; font-size: 14px; text-align: center; justify-content: center;">
                    &larr; Kembali ke Menu
                </a>
                <a href="monitor.php" target="_blank" class="btn-action btn-action-blue" style="flex: 1; padding: 12px; font-size: 14px; text-align: center; justify-content: center;">
                    📺 Buka Layar Monitor TV Outlet
                </a>
            </div>

        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script>
        const ORDER_CODE = "<?= htmlspecialchars($order['order_code']) ?>";
        let lastStatus = "<?= htmlspecialchars($order['order_status']) ?>";
        let soundEnabled = true;

        function toggleAudio() {
            soundNotifier.init();
            soundNotifier.playChime();
            const btn = document.getElementById('btnEnableSound');
            btn.textContent = '🔔 Suara Bell Aktif!';
            btn.style.background = '#dcfce7';
            btn.style.borderColor = '#86efac';
            btn.style.color = '#15803d';
        }

        // Update tampilan Stepper visual sesuai order_status
        function updateStepperUI(status) {
            const steps = [
                { id: 'step1', keys: ['menunggu_konfirmasi'] },
                { id: 'step2', keys: ['dipanggang'] },
                { id: 'step3', keys: ['sedang_dikemas', 'dikemas'] },
                { id: 'step4', keys: ['siap_diambil'] },
                { id: 'step5', keys: ['rak_mandiri', 'selesai'] }
            ];

            const statusOrder = ['menunggu_konfirmasi', 'dipanggang', 'sedang_dikemas', 'dikemas', 'siap_diambil', 'rak_mandiri', 'selesai'];
            const currentIndex = statusOrder.indexOf(status);

            steps.forEach((s, idx) => {
                const el = document.getElementById(s.id);
                if (!el) return;

                el.classList.remove('active', 'done');

                if (s.keys.includes(status)) {
                    el.classList.add('active');
                } else {
                    // Cek apakah step ini sudah lewat
                    let highestKeyIndex = -1;
                    s.keys.forEach(k => {
                        const ki = statusOrder.indexOf(k);
                        if (ki > highestKeyIndex) highestKeyIndex = ki;
                    });
                    if (currentIndex > highestKeyIndex) {
                        el.classList.add('done');
                    }
                }
            });
        }

        // Format detik jadi MM:SS
        function formatMinutesSeconds(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        // Polling status secara live tiap 3 detik
        async function pollOrderStatus() {
            try {
                const res = await fetch(`api/order_status.php?order_code=${encodeURIComponent(ORDER_CODE)}`);
                const data = await res.json();

                if (data.success && data.order) {
                    const ord = data.order;
                    const newStatus = ord.order_status;

                    // Update label status
                    document.getElementById('statusText').textContent = ord.status_label;
                    updateStepperUI(newStatus);

                    // Deteksi perubahan status untuk play sound dan Web Notification
                    if (newStatus !== lastStatus) {
                        if (newStatus === 'sedang_dikemas') {
                            soundNotifier.playChime();
                            notifyUser('Salt Bread Hampir Siap', `Pesanan ${ord.queue_number} sedang dikemas.`);
                        } else if (newStatus === 'siap_diambil') {
                            soundNotifier.playChime();
                            notifyUser('Salt Bread Siap Diambil', `Pesanan ${ord.queue_number} sudah siap di counter!`);
                            alert(`Pesanan Roti Salt Bread (${ord.queue_number}) Anda sudah Siap Diambil di Counter!`);
                        } else if (newStatus === 'rak_mandiri') {
                            soundNotifier.playWarning();
                            notifyUser('Pindah ke Rak Mandiri', `Pesanan ${ord.queue_number} telah dipindahkan ke Rak Pengambilan Mandiri.`);
                        }
                        lastStatus = newStatus;
                    }

                    // Tampilkan / Sembunyikan Alert 20 Menit Rak Pengambilan Mandiri
                    const shelfBox = document.getElementById('shelfWarningBox');
                    const timerBox = document.getElementById('readyCounterTimerBox');
                    const estimateBanner = document.getElementById('estimateBanner');

                    if (newStatus === 'rak_mandiri' || ord.is_over_20_mins) {
                        shelfBox.style.display = 'flex';
                        timerBox.style.display = 'none';
                        estimateBanner.style.display = 'none';
                    } else if (newStatus === 'siap_diambil') {
                        shelfBox.style.display = 'none';
                        timerBox.style.display = 'flex';
                        estimateBanner.style.display = 'none';

                        const elapsed = ord.ready_elapsed_sec || 0;
                        document.getElementById('readyTimeElapsed').textContent = `${formatMinutesSeconds(elapsed)} / 20:00`;
                    } else if (newStatus === 'selesai') {
                        shelfBox.style.display = 'none';
                        timerBox.style.display = 'none';
                        estimateBanner.style.display = 'none';
                    } else {
                        shelfBox.style.display = 'none';
                        timerBox.style.display = 'none';
                        estimateBanner.style.display = 'flex';

                        if (ord.queue_ahead > 0) {
                            document.getElementById('queueAheadText').textContent = `🔥 Ada ${ord.queue_ahead} antrian di depan pesanan Anda`;
                        } else {
                            document.getElementById('queueAheadText').textContent = 'Sedang diproses oleh chef dapur';
                        }
                    }
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateStepperUI("<?= htmlspecialchars($order['order_status']) ?>");
            setInterval(pollOrderStatus, 3000);
            
            // Cek jika baru saja bayar
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('paid') === '1') {
                setTimeout(() => {
                    toggleAudio();
                }, 500);
            }
        });
    </script>
</body>
</html>

