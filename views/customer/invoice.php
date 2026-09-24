<?php
/**
 * Customer Invoice, iPaymu Payment, & Live Queue Tracking
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
initSession();

$orderCode = trim($_GET['order_code'] ?? '');
$orderData = null;

if (!empty($orderCode)) {
    try {
        $res = OrderController::getByCode($orderCode);
        if ($res && !empty($res['success']) && !empty($res['data'])) {
            $orderData = $res['data'];
        }
    } catch (Throwable $e) {
        $orderData = null;
    }
}

$pageTitle = $orderData ? 'Pesanan #' . $orderData['queue_number'] : 'Lacak Pesanan';
$pageDescription = 'Lacak status pembayaran, antrean pemanggangan, dan pengambilan Salt Bread Anda secara langsung.';
$extraCss = 'customer.css';

require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════════════
   INVOICE & IPAYMU PAYMENT EXPERIENCE — ARTISAN CLEAN LOOK
   ═══════════════════════════════════════════════════════════════════ */
.inv-container {
    max-width: 780px;
    padding-top: 24px;
    padding-bottom: 90px;
    margin: 0 auto;
}

.inv-card {
    background: var(--bg-card);
    border-radius: 24px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 26px;
    margin-bottom: 22px;
}

/* Big Queue Card */
.inv-queue-hero {
    text-align: center;
    padding: 34px 24px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 28px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
    margin-bottom: 22px;
}

.inv-queue-num {
    font-family: var(--font-display);
    font-size: 5rem;
    font-weight: 800;
    color: var(--primary);
    line-height: 1;
    margin: 10px 0 6px;
    transition: all 0.3s ease;
}

.inv-queue-locked {
    font-size: 2rem;
    font-family: var(--font-display);
    font-weight: 800;
    color: var(--text-muted);
    line-height: 1.2;
    margin: 14px 0 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

/* Payment Tabs */
.ipaymu-tabs {
    display: flex;
    gap: 8px;
    background: var(--bg-primary);
    padding: 6px;
    border-radius: 16px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
}

.ipaymu-tab-btn {
    flex: 1;
    border: none;
    background: transparent;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 0.90rem;
    font-weight: 700;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.ipaymu-tab-btn.active {
    background: #ffffff;
    color: var(--primary-dark);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

[data-theme='dark'] .ipaymu-tab-btn.active {
    background: #1e293b;
    color: #fbbf24;
}

.ipaymu-tab-panel {
    display: none;
    animation: fadeIn 0.25s ease;
}

.ipaymu-tab-panel.active {
    display: block;
}

/* Bank Selection Pills */
.va-bank-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.va-bank-pill {
    padding: 7px 14px;
    border-radius: 9999px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}

.va-bank-pill.active {
    border-color: var(--primary);
    background: rgba(245, 158, 11, 0.12);
    color: var(--primary-dark);
}

/* Action Simulation Button */
.btn-sim-pay {
    width: 100%;
    background: #059669;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    padding: 15px 24px;
    font-size: 1.05rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
}

.btn-sim-pay:hover {
    transform: translateY(-2px);
    background: #047857;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
}

.btn-sim-pay:active {
    transform: translateY(0);
}

/* Email Banner */
.email-confirm-box {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 18px;
    padding: 14px 18px;
    margin: 18px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container section inv-container">
    <?php if (!$orderData): ?>
        <div class="inv-card" style="text-align: center; padding: 48px 24px; max-width: 520px; margin: 30px auto;">
            <div style="width: 60px; height: 60px; margin: 0 auto 16px; border-radius: 50%; background: rgba(217, 119, 6, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            </div>
            <h2 style="font-family: var(--font-display); margin: 0 0 8px; font-size: 1.8rem; color: var(--text-primary);">Pesanan Tidak Ditemukan</h2>
            <?php if (!empty($orderCode)): ?>
                <p style="color: var(--text-secondary); margin: 0 0 18px; font-size: 0.95rem; line-height: 1.5;">
                    Kode pesanan <code style="background: var(--bg-primary); padding: 3px 8px; border-radius: 6px; font-weight: 700; color: var(--primary-dark); font-size: 0.95rem;"><?= htmlspecialchars($orderCode) ?></code> tidak ditemukan dalam sistem.
                </p>
            <?php else: ?>
                <p style="color: var(--text-secondary); margin: 0 0 18px; font-size: 0.95rem;">
                    Silakan masukkan kode pesanan untuk melacak status pesanan Salt Bread Anda.
                </p>
            <?php endif; ?>

            <!-- Form Pencarian Langsung -->
            <form action="<?= $baseUrl ?>/views/customer/invoice.php" method="GET" style="display: flex; gap: 8px; margin: 20px 0 24px; max-width: 420px; margin-left: auto; margin-right: auto;">
                <input type="text" name="order_code" value="<?= htmlspecialchars($orderCode) ?>" placeholder="Contoh: SB2309..." required class="form-input" style="text-transform: uppercase; font-weight: 700; font-size: 0.95rem; text-align: center; border-radius: 12px;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap; font-weight: 700; padding: 10px 18px; border-radius: 12px;">Cari</button>
            </form>

            <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/views/customer/history.php" class="btn btn-secondary btn-sm" style="font-weight: 600; border-radius: 10px;">Lihat Riwayat Pesanan</a>
                <a href="<?= $baseUrl ?>/views/customer/index.php" class="btn btn-primary btn-sm" style="font-weight: 700; border-radius: 10px;">Kembali ke Menu</a>
            </div>
        </div>
    <?php else: 
        $status = $orderData['order_status'];
        $isPaid = ($orderData['payment_status'] === 'paid');
        $items = $orderData['items'] ?? [];
        $totalFormatted = formatRupiah($orderData['total_amount']);
        $custEmail = $orderData['customer_email'] ?? '';

        $rawMethod = strtolower($orderData['payment_method'] ?? 'qris');
        if ($rawMethod === 'va_bca' || $rawMethod === 'bca') {
            $currentTab = 'va';
            $currentBank = 'bca';
            $currentBankPrefix = '80777';
            $methodDisplayName = 'BCA Virtual Account';
            $methodDesc = 'Transfer ke nomor Virtual Account BCA resmi untuk verifikasi instan 24 jam';
            $simBtnText = 'Bayar Sekarang via BCA (Simulasi) &rarr;';
        } elseif ($rawMethod === 'va_mandiri' || $rawMethod === 'mandiri') {
            $currentTab = 'va';
            $currentBank = 'mandiri';
            $currentBankPrefix = '89022';
            $methodDisplayName = 'Mandiri Virtual Account';
            $methodDesc = 'Transfer ke nomor Virtual Account Mandiri resmi';
            $simBtnText = 'Bayar Sekarang via Mandiri (Simulasi) &rarr;';
        } elseif ($rawMethod === 'va_bni' || $rawMethod === 'bni') {
            $currentTab = 'va';
            $currentBank = 'bni';
            $currentBankPrefix = '82144';
            $methodDisplayName = 'BNI Virtual Account';
            $methodDesc = 'Transfer ke nomor Virtual Account BNI resmi';
            $simBtnText = 'Bayar Sekarang via BNI (Simulasi) &rarr;';
        } elseif ($rawMethod === 'va_bri' || $rawMethod === 'bri') {
            $currentTab = 'va';
            $currentBank = 'bri';
            $currentBankPrefix = '84133';
            $methodDisplayName = 'BRI Virtual Account';
            $methodDesc = 'Transfer ke nomor Virtual Account BRI resmi';
            $simBtnText = 'Bayar Sekarang via BRI (Simulasi) &rarr;';
        } elseif ($rawMethod === 'kasir' || $rawMethod === 'cash') {
            $currentTab = 'cash';
            $currentBank = '';
            $currentBankPrefix = '';
            $methodDisplayName = 'Kasir Outlet';
            $methodDesc = 'Bayar langsung di kasir toko dengan Tunai atau Kartu EDC';
            $simBtnText = 'Konfirmasi Pembayaran Kasir (Simulasi) &rarr;';
        } else {
            $currentTab = 'qris';
            $currentBank = '';
            $currentBankPrefix = '';
            $methodDisplayName = 'QRIS Instant';
            $methodDesc = 'Pindai kode QRIS di bawah menggunakan e-Wallet atau Mobile Banking';
            $simBtnText = 'Bayar Sekarang via QRIS (Simulasi) &rarr;';
        }
    ?>
        <!-- Breadcrumb / Actions -->
        <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <a href="<?= $baseUrl ?>/views/customer/index.php" style="color: var(--primary); text-decoration: none; font-size: 0.92rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Pesan Menu Lain</span>
            </a>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 6px; border-radius: 10px; font-weight: 600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak Struk</span>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(window.location.href); if (typeof Toast !== 'undefined') Toast.success('Tautan invoice disalin!');" style="display: inline-flex; align-items: center; gap: 6px; border-radius: 10px; font-weight: 600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    <span>Bagikan</span>
                </button>
            </div>
        </div>

        <!-- Big Queue Card: Dinamis Sesuai Status Pembayaran -->
        <div class="inv-queue-hero" id="main-queue-hero">
            <!-- Label Header -->
            <span id="queue-header-label" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-secondary); font-weight: 800;">
                <?= $isPaid ? 'Nomor Antrean Anda' : 'Status Tagihan Pemesanan' ?>
            </span>

            <!-- Nomor Antrean / Locked State -->
            <div id="queue-number-wrap">
                <?php if ($isPaid): ?>
                    <div id="queue-number-display" class="inv-queue-num">
                        <?= htmlspecialchars($orderData['queue_number']) ?>
                    </div>
                <?php else: ?>
                    <div id="queue-locked-display" class="inv-queue-locked">
                        <div style="font-size: 2.8rem; line-height: 1; margin-bottom: 6px; color: var(--primary);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <span>MENUNGGU PEMBAYARAN</span>
                    </div>
                    <div id="queue-number-display" class="inv-queue-num" style="display: none;">
                        <?= htmlspecialchars($orderData['queue_number']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Badges -->
            <div style="display: flex; justify-content: center; gap: 10px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
                <span class="badge" style="background: var(--bg-secondary); border: 1px solid var(--border);">Kode: <strong id="order-code-display"><?= htmlspecialchars($orderData['order_code']) ?></strong></span>
                <span class="badge <?= $isPaid ? 'badge-success' : 'badge-warning' ?>" id="payment-badge" style="font-weight: 700;">
                    <?= $isPaid ? 'Sudah Dibayar' : 'Belum Dibayar' ?>
                </span>
                <span class="badge badge-primary">
                    <?= $orderData['order_type'] === 'dine_in' ? 'Dine-in' : 'Take-away' ?>
                </span>
                <span class="badge" style="background: var(--bg-secondary); border: 1px solid var(--border); font-weight: 700; color: var(--primary);">
                    Total: <?= $totalFormatted ?>
                </span>
            </div>

            <!-- Wait Time & Queue Ahead Info -->
            <div id="queue-info-banner" style="margin-top: 18px; padding: 12px 20px; background: var(--bg-secondary); border-radius: var(--radius); display: <?= $isPaid ? 'inline-flex' : 'none' ?>; gap: 20px; align-items: center; border: 1px solid var(--border);">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Estimasi Tunggu</div>
                    <div style="font-weight: 800; color: var(--primary); font-size: 1.15rem;" id="est-minutes">~<?= $orderData['estimated_minutes'] ?> Menit</div>
                </div>
                <div style="width: 1px; height: 26px; background: var(--border);"></div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Antrean di Depan</div>
                    <div style="font-weight: 800; color: var(--text-primary); font-size: 1.15rem;" id="queue-ahead">...</div>
                </div>
            </div>

            <!-- Notice before paid -->
            <div id="queue-unpaid-notice" style="display: <?= $isPaid ? 'none' : 'block' ?>; margin-top: 14px; color: var(--text-secondary); font-size: 0.88rem; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.5;">
                Nomor antrean resmi akan aktif otomatis setelah pembayaran diverifikasi, lalu pesanan seketika diteruskan ke dapur dan layar TV monitor outlet.
            </div>

            <!-- Email Notification Success Card (Active when paid) -->
            <div id="email-confirmation-card" class="email-confirm-box" style="display: <?= $isPaid ? 'flex' : 'none' ?>;">
                <div style="color: #059669; flex-shrink: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <div style="font-size: 0.88rem; color: var(--text-primary);">
                    <strong>Email Konfirmasi Terkirim:</strong>
                    <div style="color: var(--text-secondary); margin-top: 2px;">
                        Bukti transaksi dan tiket antrean telah dikirimkan ke <strong id="email-target-display"><?= htmlspecialchars($custEmail ?: 'email pemesan') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             IPAYMU PAYMENT ACTION CARD (JIKA BELUM DIBAYAR)
             ══════════════════════════════════════════════════════════════ -->
        <div class="inv-card no-print" id="payment-action-card" style="display: <?= $isPaid ? 'none' : 'block' ?>; border: 2px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3 id="current-method-title" style="font-family: var(--font-display); margin: 0; font-size: 1.45rem; color: var(--primary);">
                            Pembayaran <?= htmlspecialchars($methodDisplayName) ?>
                        </h3>
                        <span class="badge badge-primary" style="font-size: 0.72rem; letter-spacing: 0.5px; font-weight: 800;">METODE DIPILIH</span>
                    </div>
                    <div id="current-method-desc" style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                        <?= htmlspecialchars($methodDesc) ?>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-warning" style="font-weight: 700;">Waktu: 15 Menit</span>
                </div>
            </div>

            <!-- Ganti Metode Pembayaran Toggle Button -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px dashed var(--border); flex-wrap: wrap; gap: 8px;">
                <span style="font-size: 0.82rem; color: var(--text-muted);">Selesaikan pembayaran sesuai instruksi di bawah:</span>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-toggle-change-method" onclick="togglePaymentChangeOptions()" style="border-radius: 10px; font-weight: 700; font-size: 0.80rem; display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    <span id="label-toggle-change-method">Ganti Metode Pembayaran</span>
                </button>
            </div>

            <!-- Method Switcher (Hidden by default, shown only when user clicks 'Ganti Metode Pembayaran') -->
            <div id="ipaymu-method-switcher" style="display: none; margin-bottom: 20px; background: var(--bg-secondary); padding: 14px; border-radius: 16px; border: 1px solid var(--border);">
                <div style="font-size: 0.80rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Pilih Metode Pembayaran Lain:</div>
                <div class="ipaymu-tabs" style="margin-bottom: 0;">
                    <button type="button" class="ipaymu-tab-btn <?= $currentTab === 'va' ? 'active' : '' ?>" id="tab-btn-va" onclick="selectPaymentMethod('va')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                        <span>BCA Virtual Account</span>
                    </button>
                    <button type="button" class="ipaymu-tab-btn <?= $currentTab === 'qris' ? 'active' : '' ?>" id="tab-btn-qris" onclick="selectPaymentMethod('qris')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>QRIS Instant</span>
                    </button>
                    <button type="button" class="ipaymu-tab-btn <?= $currentTab === 'cash' ? 'active' : '' ?>" id="tab-btn-cash" onclick="selectPaymentMethod('cash')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                        <span>Kasir Outlet</span>
                    </button>
                </div>
            </div>

            <!-- Panel 1: Virtual Account (Active if user chose BCA Virtual Account) -->
            <div class="ipaymu-tab-panel <?= $currentTab === 'va' ? 'active' : '' ?>" id="tab-panel-va">
                <div style="padding: 2px 0;">
                    <div style="background: var(--bg-secondary); border-radius: 18px; padding: 20px; border: 1px solid var(--border); margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="background: #005baa; color: #ffffff; font-weight: 800; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; letter-spacing: 0.5px;" id="va-bank-badge">BCA</span>
                                <span style="font-size: 0.88rem; color: var(--text-primary); font-weight: 700;" id="va-bank-label">Virtual Account BCA Resmi (iPaymu)</span>
                            </div>
                            <span style="font-size: 0.78rem; color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Verifikasi Otomatis 24 Jam
                            </span>
                        </div>

                        <!-- Nomor VA Box -->
                        <div style="background: var(--bg-card); border-radius: 14px; padding: 14px 18px; border: 1px solid var(--border); margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Nomor Virtual Account BCA</div>
                                <div style="font-size: 1.6rem; font-weight: 800; letter-spacing: 1.5px; color: var(--text-primary); font-feature-settings: 'tnum';" id="va-number-display"><?= ($currentBankPrefix ?: '80777') . str_pad($orderData['id'], 8, '0', STR_PAD_LEFT) ?></div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="copyVaNumber()" style="border-radius: 10px; font-weight: 700; padding: 8px 16px; border: 1px solid var(--border); display: inline-flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                Salin VA
                            </button>
                        </div>

                        <!-- Total & Merchant Info -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.88rem; color: var(--text-secondary);">
                            <div>Nama Akun: <strong style="color: var(--text-primary);">Little Salt Bread Blok M</strong></div>
                            <div>Total Tagihan: <strong style="color: var(--primary); font-size: 1.05rem;"><?= $totalFormatted ?></strong></div>
                        </div>
                    </div>

                    <!-- Petunjuk Transfer BCA -->
                    <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 14px 18px; font-size: 0.85rem;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <span>Cara Transfer Virtual Account BCA:</span>
                        </div>
                        <ol style="margin: 0; padding-left: 18px; color: var(--text-secondary); line-height: 1.6;">
                            <li>Buka aplikasi <strong>BCA mobile</strong> &gt; pilih menu <strong>m-Transfer</strong> &gt; <strong>BCA Virtual Account</strong>.</li>
                            <li>Masukkan nomor Virtual Account: <strong style="color: var(--text-primary);"><?= ($currentBankPrefix ?: '80777') . str_pad($orderData['id'], 8, '0', STR_PAD_LEFT) ?></strong>.</li>
                            <li>Pastikan nama merchant adalah <strong>Little Salt Bread</strong> dan nominal transfer <strong><?= $totalFormatted ?></strong>.</li>
                            <li>Masukkan PIN m-BCA Anda. Transaksi akan langsung diverifikasi otomatis.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Panel 2: QRIS Instant (Active if user chose QRIS) -->
            <div class="ipaymu-tab-panel <?= $currentTab === 'qris' ? 'active' : '' ?>" id="tab-panel-qris">
                <div style="text-align: center; padding: 10px 0;">
                    <p style="color: var(--text-secondary); margin: 0 0 14px; font-size: 0.90rem;">
                        Pindai kode QRIS di bawah menggunakan <strong>BCA Mobile, GoPay, OVO, Dana, ShopeePay, atau Livin' Mandiri</strong>:
                    </p>
                    <div style="background: #ffffff; display: inline-block; padding: 18px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 2px solid var(--border); margin-bottom: 12px;">
                        <img src="<?= $baseUrl ?>/assets/img/qris_mockup.svg" alt="QRIS iPaymu" style="width: 220px; height: 220px; object-fit: contain;" onerror="this.src='/assets/img/qris_mockup.svg'">
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;">Nominal yang harus dibayar:</div>
                    <div style="font-size: 1.45rem; font-weight: 800; color: var(--primary); font-feature-settings: 'tnum';"><?= $totalFormatted ?></div>
                </div>
            </div>

            <!-- Panel 3: Kasir Outlet (Active if user chose Kasir) -->
            <div class="ipaymu-tab-panel <?= $currentTab === 'cash' ? 'active' : '' ?>" id="tab-panel-cash">
                <div style="padding: 14px 6px; color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;">
                    <div style="background: var(--bg-secondary); border-radius: 16px; padding: 18px; border: 1px solid var(--border);">
                        Silakan langsung menuju kasir Little Salt Bread Blok M dan tunjukkan kode pesanan berikut:
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--primary); margin: 8px 0; letter-spacing: 1px;">
                            <?= htmlspecialchars($orderData['order_code']) ?>
                        </div>
                        Kasir akan menerima pembayaran secara Tunai atau Kartu Debit/Kredit EDC dan menerbitkan nomor antrean pemanggangan Anda.
                    </div>
                </div>
            </div>

            <!-- Simulation Payment Action Button -->
            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px dashed var(--border);">
                <button type="button" id="btn-simulate-pay" class="btn-sim-pay btn-simulate-pay-trigger" onclick="handleSimulatePayment(this)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span id="btn-simulate-pay-label"><?= $simBtnText ?></span>
                </button>
                <div style="text-align: center; margin-top: 10px; font-size: 0.80rem; color: var(--text-muted);">
                    Simulasi pembayaran instan: langsung menerbitkan nomor antrean resmi, mengirim email konfirmasi, dan memajukan status ke dapur & monitor.
                </div>
            </div>
        </div>

        <!-- Heated Shelf Alert (>20 Mins) -->
        <div id="shelf-alert" class="inv-card" style="display: <?= $status === 'shelf' ? 'block' : 'none' ?>; background: #fff7ed; border-left: 5px solid #ea580c; padding: 18px 20px;">
            <div style="display: flex; gap: 14px; align-items: flex-start;">
                <div style="color: #ea580c; flex-shrink: 0; margin-top: 2px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div>
                    <h4 style="margin: 0 0 4px; color: #9a3412; font-size: 1.1rem; font-weight: 700;">Pesanan Berada di Rak Mandiri (Heated Shelf)</h4>
                    <p style="margin: 0; color: #c2410c; font-size: 0.90rem; line-height: 1.4;">
                        Pesanan Anda sudah dipindahkan ke <strong><?= htmlspecialchars($orderData['shelf_slot'] ?? 'Rak Mandiri A-01') ?></strong> berpenghangat untuk menjaga tekstur renyah roti. Silakan ambil langsung di rak mandiri dengan menyebutkan nomor antrean <strong><?= htmlspecialchars($orderData['queue_number']) ?></strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- 5-Stage Stepper Antrean Toko -->
        <div class="inv-card">
            <h3 style="font-family: var(--font-display); margin: 0 0 20px; font-size: 1.35rem; color: var(--text-primary);">Tahapan Pemesanan</h3>
            
            <div class="stepper-horizontal" id="status-stepper">
                <div class="step-item" data-step="pending">
                    <div class="step-icon">1</div>
                    <div class="step-label">Menunggu Pembayaran</div>
                </div>
                <div class="step-item" data-step="confirmed">
                    <div class="step-icon">2</div>
                    <div class="step-label">Diterima Kasir</div>
                </div>
                <div class="step-item" data-step="processing">
                    <div class="step-icon">3</div>
                    <div class="step-label">Dipanggang</div>
                </div>
                <div class="step-item" data-step="ready">
                    <div class="step-icon">4</div>
                    <div class="step-label">Siap Diambil</div>
                </div>
                <div class="step-item" data-step="completed">
                    <div class="step-icon">5</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>

        <!-- Itemized Receipt Card -->
        <div class="inv-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <h3 style="font-family: var(--font-display); margin: 0; font-size: 1.3rem; color: var(--text-primary);">Rincian Item</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($orderData['created_at'])) ?></span>
            </div>

            <div style="margin-bottom: 16px;">
                <?php foreach ($items as $item): 
                    $opts = !empty($item['options_json']) ? json_decode($item['options_json'], true) : [];
                    $optList = [];
                    if (!empty($opts['sugar'])) $optList[] = 'Gula: ' . $opts['sugar'];
                    if (!empty($opts['size'])) $optList[] = 'Size: ' . $opts['size'];
                    if (!empty($opts['notes'])) $optList[] = '"' . $opts['notes'] . '"';
                ?>
                    <div style="display: flex; justify-content: space-between; padding: 11px 0; border-bottom: 1px dashed var(--border); font-size: 0.95rem;">
                        <div>
                            <strong style="color: var(--text-primary);"><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['item_name']) ?>
                            <?php if (!empty($optList)): ?>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;"><?= implode(' • ', $optList) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="font-weight: 700; color: var(--text-primary);">
                            <?= formatRupiah($item['subtotal']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Price Breakdown -->
            <div style="font-size: 0.90rem; line-height: 1.9;">
                <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                    <span>Subtotal</span>
                    <span><?= formatRupiah($orderData['subtotal']) ?></span>
                </div>
                <?php if ($orderData['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: var(--success); font-weight: 600;">
                        <span>Diskon Member</span>
                        <span>- <?= formatRupiah($orderData['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($orderData['service_charge'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                        <span>Biaya Layanan Dine-in (5%)</span>
                        <span><?= formatRupiah($orderData['service_charge']) ?></span>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                    <span>Pajak Restoran (PB1 11%)</span>
                    <span><?= formatRupiah($orderData['tax_amount']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.30rem; font-weight: 800; color: var(--primary); padding-top: 12px; border-top: 2px dashed var(--border); margin-top: 10px;">
                    <span>Total Tagihan</span>
                    <span><?= $totalFormatted ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const ORDER_CODE = <?= json_encode($orderCode) ?>;
const ORDER_ID = <?= json_encode($orderData['id'] ?? 0) ?>;
const BASE_URL = '<?= $baseUrl ?>';
let isPaidState = <?= json_encode($isPaid ?? false) ?>;
let lastKnownStatus = <?= json_encode($orderData['order_status'] ?? '') ?>;
let currentMethodKey = <?= json_encode($orderData['payment_method'] ?? 'va_bca') ?>;

// Toggle Ganti Metode Pembayaran Panel
function togglePaymentChangeOptions() {
    const switcher = document.getElementById('ipaymu-method-switcher');
    const label = document.getElementById('label-toggle-change-method');
    if (!switcher) return;
    const isHidden = (switcher.style.display === 'none' || !switcher.style.display);
    if (isHidden) {
        switcher.style.display = 'block';
        if (label) label.textContent = 'Tutup Pilihan Metode';
    } else {
        switcher.style.display = 'none';
        if (label) label.textContent = 'Ganti Metode Pembayaran';
    }
}

// Select Payment Method
function selectPaymentMethod(tabKey) {
    document.querySelectorAll('.ipaymu-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.ipaymu-tab-panel').forEach(panel => panel.classList.remove('active'));

    const activeBtn = document.getElementById(`tab-btn-${tabKey}`);
    const activePanel = document.getElementById(`tab-panel-${tabKey}`);
    if (activeBtn) activeBtn.classList.add('active');
    if (activePanel) activePanel.classList.add('active');

    const titleEl = document.getElementById('current-method-title');
    const descEl = document.getElementById('current-method-desc');
    const simLabelEl = document.getElementById('btn-simulate-pay-label');

    if (tabKey === 'va') {
        currentMethodKey = 'va_bca';
        if (titleEl) titleEl.textContent = 'Pembayaran BCA Virtual Account';
        if (descEl) descEl.textContent = 'Transfer ke nomor Virtual Account BCA di bawah untuk konfirmasi otomatis';
        if (simLabelEl) simLabelEl.textContent = 'Bayar Sekarang via BCA (Simulasi)';
    } else if (tabKey === 'qris') {
        currentMethodKey = 'qris';
        if (titleEl) titleEl.textContent = 'Pembayaran QRIS Instant';
        if (descEl) descEl.textContent = 'Pindai kode QRIS dengan aplikasi pembayaran/m-banking favorit Anda';
        if (simLabelEl) simLabelEl.textContent = 'Simulasi Bayar QRIS Berhasil';
    } else if (tabKey === 'cash') {
        currentMethodKey = 'kasir';
        if (titleEl) titleEl.textContent = 'Pembayaran di Kasir Outlet';
        if (descEl) descEl.textContent = 'Tunjukkan kode pesanan ke kasir Little Salt Bread Blok M';
        if (simLabelEl) simLabelEl.textContent = 'Konfirmasi Bayar Kasir (Simulasi)';
    }

    const switcher = document.getElementById('ipaymu-method-switcher');
    const label = document.getElementById('label-toggle-change-method');
    if (switcher) switcher.style.display = 'none';
    if (label) label.textContent = 'Ganti Metode Pembayaran';
}

// Copy Virtual Account Number
function copyVaNumber() {
    const vaDisplay = document.getElementById('va-number-display');
    if (vaDisplay) {
        const text = vaDisplay.textContent.trim();
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        }
        if (typeof Toast !== 'undefined') {
            Toast.success('Nomor Virtual Account berhasil disalin: ' + text);
        }
    }
}

// Stepper updater
function updateStepperUI(currentStatus) {
    const stepper = document.getElementById('status-stepper');
    if (!stepper) return;

    let effectiveStep = currentStatus;
    if (currentStatus === 'shelf') {
        effectiveStep = 'ready';
    }

    const statusOrder = ['pending', 'confirmed', 'processing', 'ready', 'completed'];
    const currentIdx = statusOrder.indexOf(effectiveStep);

    stepper.querySelectorAll('.step-item').forEach(item => {
        const stepName = item.dataset.step;
        const stepIdx = statusOrder.indexOf(stepName);

        item.classList.remove('completed', 'active');
        if (stepIdx < currentIdx) {
            item.classList.add('completed');
            item.querySelector('.step-icon').innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        } else if (stepIdx === currentIdx) {
            item.classList.add('active');
            item.querySelector('.step-icon').innerHTML = String(stepIdx + 1);
        } else {
            item.querySelector('.step-icon').innerHTML = String(stepIdx + 1);
        }
    });

    const shelfAlert = document.getElementById('shelf-alert');
    if (shelfAlert) {
        shelfAlert.style.display = (currentStatus === 'shelf') ? 'block' : 'none';
    }
}

// Simulation Payment Handler
async function handleSimulatePayment(triggerBtn) {
    if (!ORDER_CODE) return;

    if (triggerBtn && typeof Loading !== 'undefined') {
        Loading.start(triggerBtn, 'Memverifikasi pembayaran...');
    }

    try {
        const response = await fetch(`${BASE_URL}/api/payment_callback.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                reference: ORDER_CODE,
                status: 'paid',
                trx_id: 'SIM-' + Date.now(),
                payment_method: currentMethodKey
            })
        });

        const res = await response.json();

        if (res && res.success) {
            isPaidState = true;
            lastKnownStatus = 'confirmed';

            // 1. Update Header & Badges
            const headerLabel = document.getElementById('queue-header-label');
            if (headerLabel) headerLabel.textContent = 'Nomor Antrean Anda';

            const paymentBadge = document.getElementById('payment-badge');
            if (paymentBadge) {
                paymentBadge.className = 'badge badge-success';
                paymentBadge.textContent = 'Sudah Dibayar';
            }

            // 2. Reveal Queue Number
            const queueLocked = document.getElementById('queue-locked-display');
            const queueDisplay = document.getElementById('queue-number-display');
            if (queueLocked) queueLocked.style.display = 'none';
            if (queueDisplay) {
                if (res.data && res.data.queue_number) {
                    queueDisplay.textContent = res.data.queue_number;
                }
                queueDisplay.style.display = 'block';
            }

            // 3. Show queue info & email confirmation card
            const queueInfoBanner = document.getElementById('queue-info-banner');
            const unpaidNotice = document.getElementById('queue-unpaid-notice');
            const emailCard = document.getElementById('email-confirmation-card');
            if (queueInfoBanner) queueInfoBanner.style.display = 'inline-flex';
            if (unpaidNotice) unpaidNotice.style.display = 'none';
            if (emailCard) {
                const targetEmailEl = document.getElementById('email-target-display');
                if (targetEmailEl && res.data && res.data.customer_email) {
                    targetEmailEl.textContent = res.data.customer_email;
                }
                emailCard.style.display = 'flex';
            }

            // 4. Hide payment action card
            const paymentCard = document.getElementById('payment-action-card');
            if (paymentCard) paymentCard.style.display = 'none';

            // 5. Update Stepper
            updateStepperUI('confirmed');

            // 6. Notify user
            if (typeof Toast !== 'undefined') {
                const qNum = res.data?.queue_number || '';
                const emailMsg = res.data?.email_sent ? ' Email konfirmasi berhasil dikirim.' : '';
                Toast.success(`Pembayaran Berhasil! Nomor Antrean [${qNum}] telah aktif.${emailMsg}`);
            }

            if (typeof soundNotifier !== 'undefined') {
                soundNotifier.play('ready');
            }
        } else {
            throw new Error(res?.message || 'Gagal memproses simulasi pembayaran');
        }
    } catch (err) {
        if (typeof Toast !== 'undefined') {
            Toast.error('Gagal simulasi: ' + err.message);
        }
    } finally {
        if (triggerBtn && typeof Loading !== 'undefined') {
            Loading.stop(triggerBtn);
        }
    }
}

// Live polling for status & queue ahead
async function checkOrderStatus() {
    if (!ORDER_CODE || !<?= json_encode($orderData !== null) ?>) return;

    try {
        const response = await fetch(`${BASE_URL}/api/get_order_status.php?order_code=${encodeURIComponent(ORDER_CODE)}`);
        const res = await response.json();

        if (res && res.success && res.data) {
            const o = res.data;

            // Update queue ahead
            const queueAheadEl = document.getElementById('queue-ahead');
            if (queueAheadEl && typeof o.queue_ahead !== 'undefined') {
                queueAheadEl.textContent = `${o.queue_ahead} Pesanan`;
            }

            // Check if status changed
            if (o.order_status !== lastKnownStatus) {
                lastKnownStatus = o.order_status;
                updateStepperUI(lastKnownStatus);

                if (lastKnownStatus === 'ready') {
                    if (typeof soundNotifier !== 'undefined') soundNotifier.play('ready');
                    if (typeof Toast !== 'undefined') Toast.success('Pesanan Anda sudah SIAP DIAMBIL di counter!');
                } else if (lastKnownStatus === 'shelf') {
                    if (typeof soundNotifier !== 'undefined') soundNotifier.play('alert');
                    if (typeof Toast !== 'undefined') Toast.warning('Pesanan dipindahkan ke Rak Mandiri.');
                }
            }

            // If payment status became paid externally
            if (o.payment_status === 'paid' && !isPaidState) {
                isPaidState = true;
                const headerLabel = document.getElementById('queue-header-label');
                if (headerLabel) headerLabel.textContent = 'Nomor Antrean Anda';

                const paymentBadge = document.getElementById('payment-badge');
                if (paymentBadge) {
                    paymentBadge.className = 'badge badge-success';
                    paymentBadge.textContent = 'Sudah Dibayar';
                }
                const paymentCard = document.getElementById('payment-action-card');
                if (paymentCard) paymentCard.style.display = 'none';

                const queueLocked = document.getElementById('queue-locked-display');
                const queueDisplay = document.getElementById('queue-number-display');
                if (queueLocked) queueLocked.style.display = 'none';
                if (queueDisplay) queueDisplay.style.display = 'block';

                const queueInfoBanner = document.getElementById('queue-info-banner');
                if (queueInfoBanner) queueInfoBanner.style.display = 'inline-flex';

                const unpaidNotice = document.getElementById('queue-unpaid-notice');
                if (unpaidNotice) unpaidNotice.style.display = 'none';

                const emailCard = document.getElementById('email-confirmation-card');
                if (emailCard) emailCard.style.display = 'flex';

                updateStepperUI(o.order_status || 'confirmed');
            }
        }
    } catch (err) {
        // Silently retry next poll
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateStepperUI(lastKnownStatus);
    setInterval(checkOrderStatus, 3500);
    checkOrderStatus();
});
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
