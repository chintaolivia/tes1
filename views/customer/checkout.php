<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

initSession();

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
$baseUrl = $protocol . '://' . $host;

$pageTitle = 'My Cart - Little Salt Bread Blok M';

if (isAdmin()) {
    header('Location: ' . $baseUrl . '/views/admin/dashboard.php');
    exit;
}

$currentUser = getCurrentUser();

$discountPercent = 0;
$memberTier = 'regular';
if ($currentUser) {
    $discountPercent = OrderController::calculateDiscount($currentUser['id']);
    $memberTier = $currentUser['tier'] ?? 'regular';
}

$taxRate = (int) getSetting('tax_rate', '11');
$serviceRate = (int) getSetting('service_charge_rate', '5');

require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════════════
   MY CART & CHECKOUT — CLEAN MOBILE POS AESTHETIC
   ═══════════════════════════════════════════════════════════════════ */
:root {
  --cart-card-bg: #ffffff;
  --cart-card-border: rgba(0, 0, 0, 0.07);
  --cart-card-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
  --cart-accent: #f59e0b;
  --cart-accent-hover: #d97706;
  --cart-accent-soft: #fef3c7;
  --cart-accent-text: #b45309;
  --cart-orange: #d97706;
  --cart-danger: #ef4444;
  --cart-danger-soft: #fee2e2;
}

[data-theme='dark'] {
  --cart-card-bg: #1c1d22;
  --cart-card-border: rgba(255, 255, 255, 0.09);
  --cart-card-shadow: 0 4px 18px rgba(0, 0, 0, 0.35);
  --cart-accent: #f59e0b;
  --cart-accent-hover: #fbbf24;
  --cart-accent-soft: rgba(245, 158, 11, 0.18);
  --cart-accent-text: #fbbf24;
  --cart-orange: #fbbf24;
  --cart-danger: #ef4444;
  --cart-danger-soft: rgba(239, 68, 68, 0.2);
}

.cart-page-wrapper {
  max-width: 480px;
  margin: 0 auto;
  padding: 16px 16px 140px 16px;
  min-height: 85vh;
}

/* ─── Topbar ─── */
.cart-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.cart-back-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--cart-accent-soft);
  color: var(--cart-accent-text);
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, background 0.15s ease;
  flex-shrink: 0;
}
.cart-back-btn:hover {
  transform: scale(1.06);
  background: rgba(245, 158, 11, 0.25);
}

.cart-title {
  font-family: var(--font-body);
  font-size: 1.45rem;
  font-weight: 800;
  margin: 0;
  color: var(--text-primary);
  letter-spacing: -0.3px;
  text-align: center;
  flex-grow: 1;
}

.cart-theme-toggle {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: transparent;
  border: none;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: opacity 0.15s ease, transform 0.15s ease;
  flex-shrink: 0;
}
.cart-theme-toggle:hover {
  opacity: 0.8;
  transform: scale(1.08);
}

/* ─── Empty Cart State ─── */
.cart-empty-state {
  display: none;
  text-align: center;
  padding: 60px 24px;
  background: var(--cart-card-bg);
  border-radius: 26px;
  border: 1px dashed var(--cart-card-border);
  box-shadow: var(--cart-card-shadow);
  margin-top: 20px;
}
.cart-empty-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--cart-accent-soft);
  color: var(--cart-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}
.cart-empty-title {
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0 0 8px;
  color: var(--text-primary);
}
.cart-empty-desc {
  color: var(--text-secondary);
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0 0 24px;
}

/* ─── Cart Item Card ─── */
.cart-item-card {
  background: var(--cart-card-bg);
  border-radius: 22px;
  padding: 16px;
  margin-bottom: 16px;
  border: 1px solid var(--cart-card-border);
  box-shadow: var(--cart-card-shadow);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cart-item-top {
  display: flex;
  align-items: center;
  gap: 14px;
}

.cart-item-img-wrap {
  width: 84px;
  height: 84px;
  border-radius: 0;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 2px;
}
.cart-item-img-wrap img {
  width: 100%;
  max-width: 100%;
  height: 100%;
  max-height: 80px;
  object-fit: contain;
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.12));
}

.cart-item-details {
  flex-grow: 1;
  min-width: 0;
}

.cart-item-name {
  font-family: var(--font-body);
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 3px;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cart-item-desc {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin: 0 0 6px;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.cart-item-price {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text-primary);
  font-feature-settings: 'tnum';
}

.cart-item-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px dashed var(--cart-card-border);
}

.cart-qty-control {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cart-qty-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--cart-accent-soft);
  color: var(--cart-accent-text);
  border: none;
  font-size: 1.15rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.15s ease, background 0.15s ease;
  line-height: 1;
  user-select: none;
}
.cart-qty-btn:hover {
  transform: scale(1.08);
  background: rgba(245, 158, 11, 0.28);
}

.cart-qty-val {
  font-size: 1rem;
  font-weight: 800;
  color: var(--text-primary);
  min-width: 18px;
  text-align: center;
  font-feature-settings: 'tnum';
}

.cart-remove-link {
  background: none;
  border: none;
  color: var(--text-secondary);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 6px;
  transition: opacity 0.15s ease, color 0.15s ease;
}
.cart-remove-link:hover {
  opacity: 0.8;
  color: var(--cart-danger);
  text-decoration: underline;
}

/* ─── Add More Items Button ─── */
.cart-add-more {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--cart-accent-text);
  font-size: 0.95rem;
  font-weight: 700;
  text-decoration: none;
  margin: 4px 0 20px 4px;
  cursor: pointer;
  transition: transform 0.15s ease, color 0.15s ease;
}
.cart-add-more:hover {
  transform: translateX(4px);
  color: var(--cart-accent);
}

/* ─── Order Summary Sheet ─── */
.cart-summary-sheet {
  background: var(--cart-card-bg);
  border-radius: 26px;
  padding: 22px 20px;
  border: 1px solid var(--cart-card-border);
  box-shadow: var(--cart-card-shadow);
  margin-top: 10px;
}

.cart-summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  font-size: 0.95rem;
  color: var(--text-secondary);
}

.cart-summary-row strong,
.cart-summary-num {
  font-weight: 700;
  color: var(--text-primary);
  font-feature-settings: 'tnum';
}

.cart-summary-divider {
  border-top: 1px dotted rgba(0, 0, 0, 0.18);
  margin: 14px 0;
}
[data-theme='dark'] .cart-summary-divider {
  border-top-color: rgba(255, 255, 255, 0.18);
}

.cart-summary-total-row {
  margin-bottom: 18px;
}

.cart-total-label {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--text-primary);
}

.cart-total-val {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--text-primary);
  font-feature-settings: 'tnum';
}

/* Main Amber Pill Button */
.cart-main-pill-btn {
  width: 100%;
  background: #d97706;
  color: #ffffff;
  border: none;
  border-radius: 9999px;
  padding: 16px 24px;
  font-size: 1.05rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
}
.cart-main-pill-btn:hover {
  transform: translateY(-2px);
  background: #b45309;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
}
.cart-main-pill-btn:active {
  transform: translateY(0);
}

/* ─── Step 2: Checkout Details & Form Styling ─── */
#step-checkout-details {
  display: none;
  animation: fadeIn 0.25s ease;
}

.checkout-step-card {
  background: var(--cart-card-bg);
  border-radius: 22px;
  padding: 20px;
  margin-bottom: 16px;
  border: 1px solid var(--cart-card-border);
  box-shadow: var(--cart-card-shadow);
}

.checkout-step-title {
  font-size: 1.1rem;
  font-weight: 800;
  margin: 0 0 14px;
  color: var(--text-primary);
}

.order-type-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.order-type-option {
  border: 2px solid var(--border);
  border-radius: 16px;
  padding: 14px 10px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background: var(--cart-card-bg);
}
.order-type-option input {
  display: none;
}
.order-type-option.active {
  border-color: var(--primary);
  background: rgba(245, 158, 11, 0.08);
}
.order-type-option .type-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
  color: var(--text-secondary);
  transition: all 0.2s ease;
}
.order-type-option.active .type-icon {
  background: var(--primary);
  color: #fff;
}
.order-type-option .type-name {
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--text-primary);
  margin-bottom: 2px;
}
.order-type-option .type-sub {
  font-size: 0.72rem;
  color: var(--text-secondary);
}

.pay-method-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.pay-method-item {
  border: 2px solid var(--border);
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.2s ease;
  background: var(--cart-card-bg);
}
.pay-method-item input {
  margin-right: 12px;
  accent-color: var(--primary);
}
.pay-method-item.active {
  border-color: var(--primary);
  background: rgba(245, 158, 11, 0.06);
}
.pay-method-title {
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--text-primary);
}
.pay-method-desc {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="cart-page-wrapper">
    <!-- ══════════════════════════════════════════════════════════════
         STEP 1: MY CART (SESUAI DESAIN REFERENSI PENGGUNA)
         ══════════════════════════════════════════════════════════════ -->
    <div id="step-cart-items">
        <!-- Topbar -->
        <div class="cart-topbar">
            <a href="<?= $baseUrl ?>/views/customer/index.php" class="cart-back-btn" aria-label="Kembali ke Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <h1 class="cart-title">My Cart</h1>
            <button type="button" class="cart-theme-toggle" id="cart-theme-toggle-btn" aria-label="Ganti Tema">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
        </div>

        <!-- Empty Cart Alert -->
        <div id="empty-cart-alert" class="cart-empty-state">
            <div class="cart-empty-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            </div>
            <h3 class="cart-empty-title">Keranjang Anda Masih Kosong</h3>
            <p class="cart-empty-desc">Pilih varian salt bread renyah gurih favorit Anda hangat dari oven terlebih dahulu.</p>
            <a href="<?= $baseUrl ?>/views/customer/index.php" class="cart-main-pill-btn">
                + Mulai Pilih Menu
            </a>
        </div>

        <!-- Cart Items List Container -->
        <div id="cart-items-container">
            <!-- Injected dynamically via JavaScript -->
        </div>

        <!-- Add More Items Link & Clear Cart -->
        <div id="add-more-wrap" style="display: none; align-items: center; justify-content: space-between; margin: 16px 0 22px;">
            <a href="<?= $baseUrl ?>/views/customer/index.php" class="cart-add-more" style="margin: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add more items</span>
            </a>
            <button type="button" class="cart-remove-link" onclick="openClearCartModal()" style="font-size: 0.82rem; color: var(--text-muted); cursor: pointer; text-decoration: underline; background: none; border: none; padding: 4px;">
                Kosongkan Keranjang
            </button>
        </div>

        <!-- Summary & CTA Sheet -->
        <div id="cart-summary-wrap" class="cart-summary-sheet" style="display: none;">
            <div class="cart-summary-row">
                <span>Sub total</span>
                <span id="calc-subtotal" class="cart-summary-num">Rp 0</span>
            </div>
            <div class="cart-summary-row" id="calc-discount-row" style="display: none; color: #059669;">
                <span>Diskon Member (<?= $discountPercent ?>%)</span>
                <span id="calc-discount" class="cart-summary-num">- Rp 0</span>
            </div>
            <div class="cart-summary-row" id="calc-service-row" style="display: none;">
                <span>Biaya Layanan Dine-in (<?= $serviceRate ?>%)</span>
                <span id="calc-service" class="cart-summary-num">Rp 0</span>
            </div>
            <div class="cart-summary-row">
                <span>Pajak Restoran (PB1 <?= $taxRate ?>%)</span>
                <span id="calc-tax" class="cart-summary-num">Rp 0</span>
            </div>
            
            <div class="cart-summary-divider"></div>

            <div class="cart-summary-row cart-summary-total-row">
                <span class="cart-total-label">Total</span>
                <span id="calc-total" class="cart-total-val">Rp 0</span>
            </div>

            <!-- Pill Button -->
            <button type="button" class="cart-main-pill-btn" id="btn-next-step">
                <span>Lanjut ke Pembayaran &rarr;</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         STEP 2: CHECKOUT DETAILS & PAYMENT (INFORMASI & METODE BAYAR)
         ══════════════════════════════════════════════════════════════ -->
    <div id="step-checkout-details">
        <!-- Topbar Step 2 -->
        <div class="cart-topbar">
            <button type="button" class="cart-back-btn" id="btn-back-to-cart" aria-label="Kembali ke Keranjang">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <h1 class="cart-title">Detail Pembayaran</h1>
            <div style="width: 42px;"></div>
        </div>

        <form id="checkout-form" novalidate>
            <!-- Order Type -->
            <div class="checkout-step-card">
                <h3 class="checkout-step-title">Tipe Pemesanan</h3>
                <div class="order-type-grid">
                    <label class="order-type-option active" id="label-takeaway">
                        <input type="radio" name="order_type" value="takeaway" checked>
                        <div class="type-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        </div>
                        <div class="type-name">Take-Away</div>
                        <div class="type-sub">Bungkus bawa pulang</div>
                    </label>
                    <label class="order-type-option" id="label-dinein">
                        <input type="radio" name="order_type" value="dine_in">
                        <div class="type-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                        </div>
                        <div class="type-name">Dine-In</div>
                        <div class="type-sub">Makan di tempat (+5%)</div>
                    </label>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="checkout-step-card">
                <h3 class="checkout-step-title">Informasi Pemesan</h3>
                
                <?php if ($currentUser): ?>
                    <div style="background: rgba(245, 158, 11, 0.1); border-radius: 14px; padding: 10px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; font-weight: 700;">Member: <?= htmlspecialchars($currentUser['name']) ?></span>
                        <span class="badge badge-primary" style="font-size: 0.72rem; text-transform: uppercase;"><?= htmlspecialchars($memberTier) ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" for="customer_name" style="font-size: 0.85rem;">Nama Pemesan</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-input" placeholder="Masukkan nama Anda" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" required style="border-radius: 12px; height: 44px;">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" for="customer_phone" style="font-size: 0.85rem;">Nomor WhatsApp / HP</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="form-input" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" required style="border-radius: 12px; height: 44px;">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" for="customer_email" style="font-size: 0.85rem;">Alamat Email (Opsional)</label>
                    <input type="email" id="customer_email" name="customer_email" class="form-input" placeholder="nama@email.com" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" style="border-radius: 12px; height: 44px;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="notes" style="font-size: 0.85rem;">Catatan Pesanan</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="Contoh: Tolong pisahkan kantong roti manis..." style="border-radius: 12px;"></textarea>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="checkout-step-card">
                <h3 class="checkout-step-title">Metode Pembayaran</h3>
                <div class="pay-method-list">
                    <label class="pay-method-item active">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" name="payment_method" value="qris" checked>
                            <div>
                                <div class="pay-method-title">QRIS Instant</div>
                                <div class="pay-method-desc">BCA, GoPay, OVO, Dana, ShopeePay</div>
                            </div>
                        </div>
                        <span style="color: var(--primary);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </span>
                    </label>

                    <label class="pay-method-item">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" name="payment_method" value="va_bca">
                            <div>
                                <div class="pay-method-title">BCA Virtual Account</div>
                                <div class="pay-method-desc">Verifikasi otomatis 24 jam</div>
                            </div>
                        </div>
                        <span style="color: var(--primary);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line></svg>
                        </span>
                    </label>

                    <label class="pay-method-item">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" name="payment_method" value="kasir">
                            <div>
                                <div class="pay-method-title">Bayar di Kasir Outlet</div>
                                <div class="pay-method-desc">Tunai / EDC saat ambil pesanan</div>
                            </div>
                        </div>
                        <span style="color: var(--primary);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Final Cost Review & Submit -->
            <div class="cart-summary-sheet">
                <div class="cart-summary-row">
                    <span>Total Tagihan</span>
                    <span id="final-calc-total" class="cart-total-val" style="font-size: 1.25rem; color: var(--primary);">Rp 0</span>
                </div>
                <button type="submit" id="btn-submit-order" class="cart-main-pill-btn" style="margin-top: 12px;">
                    Konfirmasi & Buat Pesanan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Custom UI Confirmation Modal: Kosongkan Keranjang -->
<div class="modal" id="modal-clear-cart" style="z-index: 10050;">
    <div class="modal-content" style="max-width: 380px; border-radius: 24px; padding: 26px 20px; text-align: center; border: 1px solid var(--border); box-shadow: 0 20px 45px rgba(0,0,0,0.18);">
        <div style="width: 58px; height: 58px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
        </div>
        <h3 style="font-family: var(--font-display); font-size: 1.45rem; margin: 0 0 8px; color: var(--text-primary);">Kosongkan Keranjang?</h3>
        <p style="color: var(--text-secondary); font-size: 0.90rem; margin: 0 0 22px; line-height: 1.5;">Seluruh pilihan menu salt bread yang ada di keranjang pesanan Anda akan dihapus.</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn btn-secondary" onclick="Modal.close('modal-clear-cart')" style="flex: 1; border-radius: 14px; font-weight: 700; padding: 11px 16px; border: 1px solid var(--border);">Batal</button>
            <button type="button" class="btn btn-danger" onclick="confirmClearCart()" style="flex: 1; border-radius: 14px; font-weight: 700; padding: 11px 16px; background: #dc2626; color: #ffffff; border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);">Ya, Kosongkan</button>
        </div>
    </div>
</div>

<script>
const DISCOUNT_PERCENT = <?= (int) $discountPercent ?>;
const TAX_RATE = <?= (int) $taxRate ?>;
const SERVICE_RATE = <?= (int) $serviceRate ?>;
const BASE_URL = '<?= $baseUrl ?>';

document.addEventListener('DOMContentLoaded', () => {
    const stepCart = document.getElementById('step-cart-items');
    const stepCheckout = document.getElementById('step-checkout-details');
    const itemsContainer = document.getElementById('cart-items-container');
    const emptyAlert = document.getElementById('empty-cart-alert');
    const addMoreWrap = document.getElementById('add-more-wrap');
    const summaryWrap = document.getElementById('cart-summary-wrap');
    const btnNextStep = document.getElementById('btn-next-step');
    const btnBackToCart = document.getElementById('btn-back-to-cart');
    const form = document.getElementById('checkout-form');
    const btnSubmit = document.getElementById('btn-submit-order');

    // Theme toggle
    document.getElementById('cart-theme-toggle-btn')?.addEventListener('click', () => {
        if (typeof toggleTheme === 'function') {
            toggleTheme();
        } else {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('sb_theme', next);
        }
    });

    // Step switching
    if (btnNextStep) {
        btnNextStep.addEventListener('click', () => {
            const items = cart.getItems();
            if (!items || items.length === 0) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Keranjang masih kosong!');
                }
                return;
            }
            stepCart.style.display = 'none';
            stepCheckout.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (btnBackToCart) {
        btnBackToCart.addEventListener('click', () => {
            stepCheckout.style.display = 'none';
            stepCart.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Order type styling & radio handler
    document.querySelectorAll('input[name="order_type"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            document.querySelectorAll('.order-type-option').forEach(el => el.classList.remove('active'));
            const parent = e.target.closest('.order-type-option');
            if (parent) parent.classList.add('active');
            renderSummary();
        });
    });

    // Payment method radio styling
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            document.querySelectorAll('.pay-method-item').forEach(el => el.classList.remove('active'));
            const parent = e.target.closest('.pay-method-item');
            if (parent) parent.classList.add('active');
        });
    });

    function renderCartItems() {
        const items = cart.getItems();
        if (!items || items.length === 0) {
            emptyAlert.style.display = 'block';
            addMoreWrap.style.display = 'none';
            summaryWrap.style.display = 'none';
            itemsContainer.innerHTML = '';
            // If on step 2 and cart becomes empty, redirect to step 1
            stepCheckout.style.display = 'none';
            stepCart.style.display = 'block';
            return;
        }

        emptyAlert.style.display = 'none';
        addMoreWrap.style.display = 'block';
        summaryWrap.style.display = 'block';

        itemsContainer.innerHTML = items.map((item, idx) => {
            const opts = [];
            if (item.options?.sugar) opts.push(`Gula: ${item.options.sugar}`);
            if (item.options?.size) opts.push(`Size: ${item.options.size}`);
            if (item.options?.notes) opts.push(`"${item.options.notes}"`);
            
            const descText = opts.length > 0 ? opts.join(' • ') : (item.description || 'Artisanal salt bread fresh from oven');
            
            let imgPath = item.image || item.image_url || 'assets/img/salt_bread_plain.png';
            if (!imgPath.startsWith('http') && !imgPath.startsWith('/')) {
                imgPath = BASE_URL + '/' + imgPath.replace(/^\//, '');
            }

            return `
                <div class="cart-item-card">
                    <div class="cart-item-top">
                        <div class="cart-item-img-wrap">
                            <img src="${imgPath}" alt="${item.name}" onerror="this.src='${BASE_URL}/assets/img/salt_bread_plain.png'">
                        </div>
                        <div class="cart-item-details">
                            <h3 class="cart-item-name">${item.name}</h3>
                            <p class="cart-item-desc">${descText}</p>
                            <div class="cart-item-price">${formatRupiah(item.price)}</div>
                        </div>
                    </div>
                    <div class="cart-item-bottom">
                        <div class="cart-qty-control">
                            <button type="button" class="cart-qty-btn cart-qty-minus" onclick="cart.updateQuantity(${idx}, ${item.quantity - 1})" aria-label="Kurangi">-</button>
                            <span class="cart-qty-val">${item.quantity}</span>
                            <button type="button" class="cart-qty-btn cart-qty-plus" onclick="cart.updateQuantity(${idx}, ${item.quantity + 1})" aria-label="Tambah">+</button>
                        </div>
                        <button type="button" class="cart-remove-link" onclick="cart.removeItem(${idx})">Remove</button>
                    </div>
                </div>
            `;
        }).join('');

        renderSummary();
    }

    function renderSummary() {
        const subtotal = cart.getSubtotal();
        const orderType = document.querySelector('input[name="order_type"]:checked')?.value || 'takeaway';
        
        let discount = 0;
        const discountRow = document.getElementById('calc-discount-row');
        const discountEl = document.getElementById('calc-discount');
        if (DISCOUNT_PERCENT > 0) {
            discount = Math.round(subtotal * (DISCOUNT_PERCENT / 100));
            if (discountRow) discountRow.style.display = 'flex';
            if (discountEl) discountEl.textContent = '- ' + formatRupiah(discount);
        } else {
            if (discountRow) discountRow.style.display = 'none';
        }

        const discountedSubtotal = subtotal - discount;

        let service = 0;
        const serviceRow = document.getElementById('calc-service-row');
        const serviceEl = document.getElementById('calc-service');
        if (orderType === 'dine_in') {
            service = Math.round(discountedSubtotal * (SERVICE_RATE / 100));
            if (serviceRow) serviceRow.style.display = 'flex';
            if (serviceEl) serviceEl.textContent = formatRupiah(service);
        } else {
            if (serviceRow) serviceRow.style.display = 'none';
        }

        const tax = Math.round((discountedSubtotal + service) * (TAX_RATE / 100));
        const grandTotal = discountedSubtotal + service + tax;

        const subtotalEl = document.getElementById('calc-subtotal');
        if (subtotalEl) subtotalEl.textContent = formatRupiah(subtotal);

        const taxEl = document.getElementById('calc-tax');
        if (taxEl) taxEl.textContent = formatRupiah(tax);

        const totalEl = document.getElementById('calc-total');
        if (totalEl) totalEl.textContent = formatRupiah(grandTotal);

        const finalTotalEl = document.getElementById('final-calc-total');
        if (finalTotalEl) finalTotalEl.textContent = formatRupiah(grandTotal);
    }

    cart.onChange(renderCartItems);
    renderCartItems();

    // Form submit handler
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (typeof Validator !== 'undefined') {
                Validator.clearAll(form);
            }

            const items = cart.getItems();
            if (!items || items.length === 0) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Keranjang masih kosong!');
                }
                return;
            }

            const nameEl = document.getElementById('customer_name');
            const phoneEl = document.getElementById('customer_phone');
            const emailEl = document.getElementById('customer_email');
            const notesEl = document.getElementById('notes');

            const customer_name = nameEl ? nameEl.value.trim() : '';
            const customer_phone = phoneEl ? phoneEl.value.trim() : '';
            const customer_email = emailEl ? emailEl.value.trim() : '';
            const notes = notesEl ? notesEl.value.trim() : '';
            const order_type = document.querySelector('input[name="order_type"]:checked')?.value || 'takeaway';
            const payment_method = document.querySelector('input[name="payment_method"]:checked')?.value || 'qris';

            let hasError = false;
            if (!customer_name) {
                if (typeof Validator !== 'undefined' && nameEl) {
                    Validator.showError(nameEl, 'Nama pemesan wajib diisi.');
                }
                hasError = true;
            }

            if (!customer_phone) {
                if (typeof Validator !== 'undefined' && phoneEl) {
                    Validator.showError(phoneEl, 'Nomor WhatsApp / HP wajib diisi.');
                }
                hasError = true;
            }

            if (hasError) return;

            if (typeof Loading !== 'undefined' && btnSubmit) {
                Loading.start(btnSubmit, 'Membuat pesanan...');
            }

            const payload = {
                customer_name,
                customer_phone,
                customer_email,
                order_type,
                table_id: null,
                notes,
                payment_method,
                items: items.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    options: item.options || {}
                }))
            };

            try {
                const res = await postJSON('<?= $baseUrl ?>/api/create_order.php', payload);
                if (res.success && res.data?.order_code) {
                    cart.clear(); // Clear storage on success
                    if (typeof Toast !== 'undefined') {
                        Toast.success('Pesanan berhasil dibuat!');
                    }
                    setTimeout(() => {
                        window.location.href = `<?= $baseUrl ?>/views/customer/invoice.php?order_code=${encodeURIComponent(res.data.order_code)}`;
                    }, 800);
                } else {
                    if (typeof Toast !== 'undefined') {
                        Toast.error(res.message || 'Gagal membuat pesanan.');
                    }
                    if (typeof Loading !== 'undefined' && btnSubmit) {
                        Loading.stop(btnSubmit);
                    }
                }
            } catch (err) {
                if (typeof Toast !== 'undefined') {
                    Toast.error(err.message || 'Terjadi kesalahan sistem.');
                }
                if (typeof Loading !== 'undefined' && btnSubmit) {
                    Loading.stop(btnSubmit);
                }
            }
        });
    }
});

// Custom UI Modal Handlers for Clearing Cart
function openClearCartModal() {
    if (typeof Modal !== 'undefined') {
        Modal.open('modal-clear-cart');
    }
}

function confirmClearCart() {
    cart.clear();
    if (typeof Modal !== 'undefined') {
        Modal.close('modal-clear-cart');
    }
    if (typeof Toast !== 'undefined') {
        Toast.info('Keranjang berhasil dikosongkan');
    }
}
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
