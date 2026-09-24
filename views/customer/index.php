<?php
/**
 * Customer Menu Catalog — Clean, Modern, & Warm POS Aesthetic
 * Inspired by modern cafe/bakery POS interfaces (Little Salt Bread Blok M)
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/MenuController.php';

initSession();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$pageTitle = 'Katalog Menu — Little Salt Bread Blok M';
$pageDescription = 'Pilih Salt Bread segar langsung dari oven dan minuman favorit Anda.';
$bodyClass = 'customer-menu-view';
$extraCss = 'customer.css';

$pdo = getDb();
$categoryFilter = $_GET['category'] ?? null;
$searchFilter = $_GET['search'] ?? null;

$categories = MenuController::getCategories()['data'] ?? [];
$products = MenuController::getAll($categoryFilter, $searchFilter)['data'] ?? [];

// Active queue count
$stmtQueue = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'processing', 'ready')");
$stmtQueue->execute();
$activeQueueCount = (int)($stmtQueue->fetch()['count'] ?? 0);

require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════════════
   CLEAN CUSTOMER MENU — PALETTE & COMPONENT STYLING
   ═══════════════════════════════════════════════════════════════════ */
:root {
  --cust-bg: #ffffff;
  --cust-card-bg: #ffffff;
  --cust-card-border: rgba(0, 0, 0, 0.08);
  --cust-accent: #f59e0b;
  --cust-accent-dark: #d97706;
  --cust-accent-light: #fef3c7;
  --cust-text-main: #1f2937;
  --cust-text-sub: #6b7280;
  --cust-shadow-soft: 0 4px 18px rgba(0, 0, 0, 0.05);
  --cust-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.09);
}

[data-theme='dark'] {
  --cust-bg: #111215;
  --cust-card-bg: #1c1d22;
  --cust-card-border: rgba(255, 255, 255, 0.08);
  --cust-accent: #f59e0b;
  --cust-accent-dark: #d97706;
  --cust-accent-light: rgba(245, 158, 11, 0.15);
  --cust-text-main: #f3f4f6;
  --cust-text-sub: #9ca3af;
  --cust-shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.25);
  --cust-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.4);
}

body.customer-menu-view {
  background-color: var(--cust-bg) !important;
  color: var(--cust-text-main);
  min-height: 100vh;
}

.cust-menu-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 16px 20px 140px 20px;
}

/* ─── Top Control Bar ─── */
.cust-topbar {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 20px;
}

.cust-menu-trigger {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: var(--cust-accent);
  border: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  transition: transform 0.15s ease, background 0.15s ease;
  flex-shrink: 0;
}
.cust-menu-trigger:hover {
  transform: scale(1.04);
  background: var(--cust-accent-dark);
}
.cust-menu-trigger span {
  display: block;
  width: 18px;
  height: 2.5px;
  background: #ffffff;
  border-radius: 2px;
}

.cust-search-box {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}
.cust-search-input {
  width: 100%;
  height: 44px;
  background: var(--cust-card-bg);
  border: 1px solid var(--cust-card-border);
  border-radius: 9999px;
  padding: 0 20px 0 44px;
  font-size: 0.92rem;
  font-family: inherit;
  color: var(--cust-text-main);
  box-shadow: var(--cust-shadow-soft);
  transition: all 0.2s ease;
  outline: none;
}
.cust-search-input:focus {
  border-color: var(--cust-accent);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}
.cust-search-icon {
  position: absolute;
  left: 16px;
  color: var(--cust-text-sub);
  pointer-events: none;
  display: flex;
  align-items: center;
}

.cust-top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.cust-track-btn {
  height: 44px;
  padding: 0 16px;
  border-radius: 14px;
  background: var(--cust-card-bg);
  border: 1px solid var(--cust-card-border);
  color: var(--cust-text-main);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  box-shadow: var(--cust-shadow-soft);
  transition: all 0.15s ease;
}
.cust-track-btn:hover {
  border-color: var(--cust-accent);
  color: var(--cust-accent);
}

/* ─── Store Hero Banner ─── */
.cust-store-banner {
  background: #18191d;
  border-radius: 20px;
  padding: 22px 26px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.08);
  position: relative;
  overflow: hidden;
  margin-bottom: 20px;
}
.cust-store-banner::after {
  display: none;
}
.cust-store-profile {
  display: flex;
  align-items: center;
  gap: 16px;
  z-index: 1;
}
.cust-store-avatar {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  background: var(--cust-accent);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display, cursive);
  font-size: 1.6rem;
  font-weight: 800;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
}
.cust-store-name {
  font-family: var(--font-display, 'Patrick Hand', cursive);
  font-size: 1.7rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.1;
  color: #ffffff;
  letter-spacing: 0.5px;
}
.cust-store-tagline {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 3px 0 0;
}

.cust-store-stats {
  display: flex;
  align-items: center;
  gap: 20px;
  z-index: 1;
}
.cust-stat-col {
  text-align: center;
  padding: 0 10px;
}
.cust-stat-col:not(:last-child) {
  border-right: 1px solid rgba(255, 255, 255, 0.15);
  padding-right: 20px;
}
.cust-stat-num {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--cust-accent);
  line-height: 1;
  font-feature-settings: 'tnum';
}
.cust-stat-lbl {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 4px;
  white-space: nowrap;
}

/* ─── Promotional Banners Carousel (Smooth Infinite Forward Loop) ─── */
.cust-promo-section {
  margin-bottom: 22px;
  position: relative;
  overflow: hidden;
  border-radius: 20px;
}

.cust-promo-viewport {
  width: 100%;
  overflow: hidden;
  position: relative;
  border-radius: 20px;
  padding: 4px 0 8px 0;
  touch-action: pan-y;
}

.cust-promo-track {
  display: flex;
  gap: 16px;
  width: max-content;
  will-change: transform;
  transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
}

.cust-promo-card {
  width: 580px;
  max-width: calc(50vw - 28px);
  height: 165px;
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
  border: 1px solid var(--cust-card-border);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 18px 20px;
  color: #ffffff;
  cursor: pointer;
  user-select: none;
  flex-shrink: 0;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease, border-color 0.3s ease;
  background: #1a1a24;
}

.cust-promo-card.active-promo {
  border-color: rgba(245, 158, 11, 0.6);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.cust-promo-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.22);
}

.cust-promo-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  z-index: 0;
  transition: transform 0.4s ease;
}

.cust-promo-card:hover .cust-promo-bg {
  transform: scale(1.05);
}

.cust-promo-overlay {
  position: absolute;
  inset: 0;
  background: rgba(17, 18, 22, 0.72);
  z-index: 1;
}

.cust-promo-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 2;
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 4px 10px;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: 0.3px;
}

.cust-promo-content {
  position: relative;
  z-index: 2;
  max-width: 80%;
}

.cust-promo-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--cust-accent);
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 9999px;
  margin-bottom: 5px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.cust-promo-title {
  font-family: var(--font-display, 'Patrick Hand', cursive);
  font-size: 1.35rem;
  font-weight: 700;
  line-height: 1.15;
  margin: 0 0 3px 0;
  color: #ffffff;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.cust-promo-desc {
  font-size: 0.76rem;
  color: rgba(255, 255, 255, 0.88);
  margin: 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.cust-promo-dots {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
}

.cust-promo-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--cust-card-border);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all 0.25s ease;
}

.cust-promo-dot.active {
  background: var(--cust-accent);
  width: 16px;
  border-radius: 9999px;
}

@media (max-width: 768px) {
  .cust-promo-track {
    gap: 12px;
  }
  .cust-promo-card {
    width: calc(100vw - 58px);
    max-width: 345px;
    height: 152px;
    padding: 14px 16px;
  }
  .cust-promo-title {
    font-size: 1.2rem;
  }
  .cust-promo-desc {
    font-size: 0.72rem;
  }
  .cust-promo-content {
    max-width: 82%;
  }
}

/* ─── Category Filter Carousel ─── */
.cust-categories-carousel {
  display: flex;
  align-items: center;
  gap: 12px;
  overflow-x: auto;
  padding: 2px 2px 14px 2px;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}
.cust-categories-carousel::-webkit-scrollbar {
  display: none;
}
.cust-cat-card {
  flex: 0 0 auto;
  background: var(--cust-card-bg);
  border: 1px solid var(--cust-card-border);
  border-radius: 18px;
  padding: 12px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: var(--cust-shadow-soft);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 90px;
  user-select: none;
  text-decoration: none;
  color: var(--cust-text-main);
}
.cust-cat-card:hover {
  transform: translateY(-2px);
  border-color: var(--cust-accent);
}
.cust-cat-card.active {
  background: var(--cust-accent) !important;
  border-color: var(--cust-accent) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
}
.cust-cat-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cust-cat-card.active .cust-cat-icon svg {
  stroke: #ffffff;
}
.cust-cat-card:not(.active) .cust-cat-icon svg {
  stroke: var(--cust-accent-dark);
}
.cust-cat-name {
  font-size: 0.82rem;
  font-weight: 700;
  white-space: nowrap;
}

/* ─── Product Section Header ─── */
.cust-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.cust-section-title {
  font-family: var(--font-display, cursive);
  font-size: 1.55rem;
  font-weight: 700;
  margin: 0;
  color: var(--cust-text-main);
}
.cust-product-count-badge {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--cust-text-sub);
  background: var(--cust-card-bg);
  padding: 4px 12px;
  border-radius: 9999px;
  border: 1px solid var(--cust-card-border);
}

/* ─── Product Grid (Clean Floating Circle Aesthetic) ─── */
.cust-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 18px;
}

.cust-product-card {
  background: var(--cust-card-bg);
  border: 1px solid var(--cust-card-border);
  border-radius: 22px;
  padding: 16px 14px 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  box-shadow: var(--cust-shadow-soft);
  position: relative;
  transition: all 0.22s ease;
  cursor: pointer;
  user-select: none;
}
.cust-product-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--cust-shadow-hover);
  border-color: rgba(245, 158, 11, 0.3);
}
.cust-product-card.has-cart {
  border-color: var(--cust-accent);
}
.cust-product-card.disabled {
  opacity: 0.55;
  cursor: not-allowed;
  filter: grayscale(0.6);
}
.cust-product-card.is-hidden {
  display: none !important;
}

/* Floating Dish Image */
.cust-product-img-wrap {
  width: 100%;
  height: 120px;
  border-radius: 0;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  position: relative;
  padding: 4px;
}
.cust-product-img {
  width: 100%;
  max-width: 100%;
  height: 100%;
  max-height: 115px;
  object-fit: contain;
  filter: drop-shadow(0 8px 14px rgba(0, 0, 0, 0.12));
  transition: transform 0.25s ease, filter 0.25s ease;
}
.cust-product-card:hover .cust-product-img {
  transform: scale(1.08) translateY(-3px);
  filter: drop-shadow(0 14px 22px rgba(0, 0, 0, 0.16));
}

.cust-item-cart-count {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--cust-accent);
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 800;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.cust-product-name {
  font-size: 0.94rem;
  font-weight: 700;
  margin: 0 0 4px;
  color: var(--cust-text-main);
  line-height: 1.25;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.5em;
}
.cust-product-meta {
  font-size: 0.72rem;
  color: var(--cust-text-sub);
  margin-bottom: 14px;
}

.cust-product-bottom {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px dashed var(--cust-card-border);
}
.cust-product-price {
  font-size: 0.98rem;
  font-weight: 800;
  color: var(--cust-text-main);
  font-feature-settings: 'tnum';
}
.cust-add-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--cust-accent);
  color: #ffffff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.15s ease, background 0.15s ease;
  flex-shrink: 0;
}
.cust-add-btn:hover {
  transform: scale(1.12);
  background: var(--cust-accent-dark);
}
.cust-product-card.disabled .cust-add-btn {
  background: #cbd5e1;
  box-shadow: none;
  pointer-events: none;
}

/* ─── Floating Bottom Cart Bar (Customer Mobile & Desktop) ─── */
.cust-floating-cart {
  position: fixed;
  bottom: 28px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 32px);
  max-width: 440px;
  background: #18191d;
  color: #ffffff;
  border-radius: 9999px;
  padding: 12px 18px;
  display: none;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.12);
  z-index: 9990;
  animation: slideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
[data-theme='dark'] .cust-floating-cart {
  background: #111215;
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
}
@keyframes slideUp {
  from { transform: translate(-50%, 30px); opacity: 0; }
  to { transform: translate(-50%, 0); opacity: 1; }
}
.cust-float-info {
  display: flex;
  align-items: center;
  gap: 12px;
}
.cust-float-badge {
  background: var(--cust-accent);
  color: #ffffff;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.92rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
.cust-float-text {
  display: flex;
  flex-direction: column;
}
.cust-float-lbl {
  font-size: 0.72rem;
  opacity: 0.85;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
}
.cust-float-total {
  font-weight: 800;
  font-size: 1.08rem;
  line-height: 1.1;
  font-feature-settings: 'tnum';
}
.cust-float-clear-btn {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s ease;
}
.cust-float-clear-btn:hover {
  background: #dc2626;
  border-color: #dc2626;
  transform: scale(1.08);
}
.cust-float-btn {
  background: var(--cust-accent);
  color: #ffffff;
  font-weight: 800;
  font-size: 0.88rem;
  border-radius: 9999px;
  padding: 9px 20px;
  text-decoration: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: transform 0.15s ease, background 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.cust-float-btn:hover {
  transform: scale(1.04);
  background: var(--cust-accent-dark);
  color: #ffffff;
}

/* ─── Admin Preview Notice ─── */
.admin-preview-banner {
  background: rgba(245, 158, 11, 0.1);
  border: 1px dashed var(--cust-accent);
  border-radius: 16px;
  padding: 12px 18px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  font-size: 0.85rem;
}

/* ─── Responsive Queries (Mobile View Optimization) ─── */
@media (max-width: 640px) {
  .cust-menu-wrapper {
    padding: 12px 12px 150px 12px;
    padding: 12px 10px 140px 10px;
  }
  .cust-topbar {
    gap: 8px;
    margin-bottom: 14px;
  }
  .cust-menu-trigger {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }
  .cust-search-input {
    height: 40px;
    padding: 0 14px 0 38px;
    font-size: 0.84rem;
  }
  .cust-search-icon {
    left: 12px;
  }
  .cust-top-actions {
    display: none !important;
  }
  .cust-store-banner {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    padding: 18px;
    gap: 12px;
    padding: 14px 14px;
    border-radius: 16px;
    margin-bottom: 14px;
  }
  .cust-store-avatar {
    width: 42px;
    height: 42px;
    font-size: 1.05rem;
  }
  .cust-store-name {
    font-size: 1.15rem;
  }
  .cust-store-tagline {
    font-size: 0.72rem;
  }
  .cust-store-stats {
    width: 100%;
    justify-content: space-between;
  }
  .cust-stat-col {
    padding: 0 4px;
    padding: 0 2px;
  }
  .cust-stat-col:not(:last-child) {
    padding-right: 12px;
    padding-right: 8px;
  }
  .cust-stat-val {
    font-size: 1rem;
  }
  .cust-stat-lbl {
    font-size: 0.65rem;
  }

  /* 3 Categories fit seamlessly without cut-off */
  .cust-categories-carousel {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 6px !important;
    overflow-x: visible !important;
    padding: 2px 0 12px 0 !important;
  }
  .cust-cat-card {
    min-width: 0 !important;
    width: 100% !important;
    padding: 9px 3px 7px !important;
    border-radius: 13px !important;
    gap: 4px !important;
  }
  .cust-cat-icon {
    width: 26px !important;
    height: 26px !important;
  }
  .cust-cat-icon svg {
    width: 17px !important;
    height: 17px !important;
  }
  .cust-cat-name {
    font-size: 0.69rem !important;
    white-space: normal !important;
    text-align: center !important;
    line-height: 1.15 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
  }

  /* Section Title & Count */
  .cust-section-header {
    margin-bottom: 10px;
    padding: 0 2px;
  }
  .cust-section-title {
    font-size: 1.25rem;
  }
  .cust-product-count-badge {
    font-size: 0.70rem;
    padding: 3px 8px;
  }

  /* 3 Product Columns (3 menu per baris horizontal) */
  .cust-products-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 8px !important;
  }
  .cust-product-card {
    padding: 10px 6px 9px !important;
    border-radius: 16px !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    min-height: 160px !important;
  }
  .cust-product-img-wrap {
    width: 100% !important;
    height: 86px !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    margin: 0 auto 4px auto !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 2px !important;
  }
  .cust-product-img {
    width: 100% !important;
    max-width: 100% !important;
    height: 100% !important;
    max-height: 82px !important;
    object-fit: contain !important;
    filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.12)) !important;
  }
  .cust-product-name {
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    height: 2.5em !important;
    margin: 0 0 4px !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    word-break: break-word !important;
  }
  .cust-product-meta {
    display: none !important;
  }
  .cust-product-soldout {
    display: inline-block !important;
    font-size: 0.60rem !important;
    color: var(--error, #ef4444) !important;
    font-weight: 700 !important;
    margin-bottom: 4px !important;
  }
  .cust-product-bottom {
    padding-top: 6px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    border-top: 1px dashed var(--cust-card-border) !important;
  }
  .cust-product-price {
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    white-space: nowrap !important;
  }
  .cust-add-btn {
    width: 26px !important;
    height: 26px !important;
  }
  .cust-add-btn svg {
    width: 14px !important;
    height: 14px !important;
  }
  .cust-item-cart-count {
    width: 18px !important;
    height: 18px !important;
    font-size: 0.62rem !important;
    top: -2px !important;
    right: -2px !important;
  }
  .cust-menu-wrapper {
    padding: 12px 12px 185px 12px !important;
  }
  .cust-floating-cart {
    bottom: calc(96px + var(--safe-bottom, 0px)) !important;
    width: calc(100% - 32px) !important;
    max-width: 380px !important;
    padding: 10px 16px !important;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25) !important;
  }
  .cust-float-btn {
    padding: 7px 14px !important;
    font-size: 0.82rem !important;
  }
  .cust-float-total {
    font-size: 0.98rem !important;
  }
}
</style>

<div class="cust-menu-wrapper">
    <?php if (isAdmin()): ?>
    <!-- Admin Preview Banner -->
    <div class="admin-preview-banner">
        <div>
            <strong>Mode Pratinjau Admin</strong> &bull; Anda melihat tampilan menu pelanggan.
        </div>
        <a href="<?= $baseUrl ?>/views/admin/dashboard.php" class="btn btn-primary btn-sm" style="font-weight: 700; border-radius: 10px; padding: 6px 14px;">
            Kembali ke Dashboard POS &rarr;
        </a>
    </div>
    <script>
        localStorage.removeItem('sb_cart');
    </script>
    <?php endif; ?>

    <!-- Top Control Bar (Search + Menu + Actions) -->
    <div class="cust-topbar">
        <!-- Amber Menu Drawer Button -->
        <button class="cust-menu-trigger" id="cust-drawer-btn" title="Buka Menu Navigasi" aria-label="Menu Navigasi">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Search Box -->
        <div class="cust-search-box">
            <span class="cust-search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" class="cust-search-input" id="cust-search-input" placeholder="Cari menu salt bread..." autocomplete="off" value="<?= htmlspecialchars($searchFilter ?? '') ?>">
        </div>
    </div>

    <!-- Store Hero Banner -->
    <div class="cust-store-banner">
        <div class="cust-store-profile">
            <div class="cust-store-avatar">SB</div>
            <div>
                <h2 class="cust-store-name">Little Salt Bread</h2>
                <p class="cust-store-tagline">Fresh & warm artisanal salt bread recipe • Blok M, Melawai</p>
            </div>
        </div>

        <div class="cust-store-stats">
            <div class="cust-stat-col">
                <div class="cust-stat-num">15 Mnt</div>
                <div class="cust-stat-lbl">Fresh Oven</div>
            </div>
            <div class="cust-stat-col">
                <div class="cust-stat-num"><?= count($products) ?></div>
                <div class="cust-stat-lbl">Pilihan Menu</div>
            </div>
            <div class="cust-stat-col">
                <div class="cust-stat-num"><?= $activeQueueCount ?></div>
                <div class="cust-stat-lbl">Antrean Dapur</div>
            </div>
        </div>
    </div>

    <!-- Promotional Banners Carousel -->
    <section class="cust-promo-section" aria-label="Promo & Rekomendasi Menu">
        <div class="cust-promo-viewport" id="custPromoViewport">
            <div class="cust-promo-track" id="custPromoTrack">
                <!-- Promo 1: Signature White Box 20 Pcs -->
                <div class="cust-promo-card" data-promo="box" data-index="0">
                    <img src="<?= $baseUrl ?>/assets/img/banners/banner_white_box_packaging.jpg" alt="Signature Box 20 Pcs" class="cust-promo-bg" loading="lazy">
                    <div class="cust-promo-overlay"></div>
                    <div class="cust-promo-badge">Hemat 15%</div>
                    <div class="cust-promo-content">
                        <span class="cust-promo-pill">PAKET SPESIAL</span>
                        <h4 class="cust-promo-title">Signature Box 20 Pcs</h4>
                        <p class="cust-promo-desc">Salt bread hangat isi 20 pcs untuk acara, hampers, dan kumpul keluarga.</p>
                    </div>
                </div>

                <!-- Promo 2: 5 Varian Rasa Autentik -->
                <div class="cust-promo-card" data-promo="variants" data-index="1">
                    <img src="<?= $baseUrl ?>/assets/img/banners/banner_flavor_variants.jpg" alt="5 Varian Rasa Little Salt Bread" class="cust-promo-bg" loading="lazy">
                    <div class="cust-promo-overlay"></div>
                    <div class="cust-promo-badge">Bestseller</div>
                    <div class="cust-promo-content">
                        <span class="cust-promo-pill">FAVORIT PELANGGAN</span>
                        <h4 class="cust-promo-title">5 Varian Rasa Autentik</h4>
                        <p class="cust-promo-desc">Plain Sea Salt, Keju Gurih, Garlic Butter, Truffle Egg & Miso.</p>
                    </div>
                </div>

                <!-- Promo 3: Combo Kopi Dingin & Salt Bread -->
                <div class="cust-promo-card" data-promo="pairing" data-index="2">
                    <img src="<?= $baseUrl ?>/assets/img/banners/banner_coffee_pairing.jpg" alt="Salt Bread & Japanese Coffee" class="cust-promo-bg" loading="lazy">
                    <div class="cust-promo-overlay"></div>
                    <div class="cust-promo-badge">Pairing Mantap</div>
                    <div class="cust-promo-content">
                        <span class="cust-promo-pill">COMBO HEMAT</span>
                        <h4 class="cust-promo-title">Salt Bread + Kopi Khas Little</h4>
                        <p class="cust-promo-desc">Sensasi gurih asin berpadu dengan segarnya racikan kopi artisan.</p>
                    </div>
                </div>

                <!-- Promo 4: Dipanggang Hangat Setiap Jam -->
                <div class="cust-promo-card" data-promo="oven" data-index="3">
                    <img src="<?= $baseUrl ?>/assets/img/banners/banner_saltbread_oven.jpg" alt="Fresh From Oven Blok M" class="cust-promo-bg" loading="lazy">
                    <div class="cust-promo-overlay"></div>
                    <div class="cust-promo-badge">Fresh Batch</div>
                    <div class="cust-promo-content">
                        <span class="cust-promo-pill">FRESH FROM OVEN</span>
                        <h4 class="cust-promo-title">Dipanggang Baru Tiap Jam</h4>
                        <p class="cust-promo-desc">Dibuat langsung di open kitchen Blok M dengan butter creamery murni.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo Indicators -->
        <div class="cust-promo-dots" id="custPromoDots">
            <button type="button" class="cust-promo-dot active" aria-label="Promo 1" data-index="0"></button>
            <button type="button" class="cust-promo-dot" aria-label="Promo 2" data-index="1"></button>
            <button type="button" class="cust-promo-dot" aria-label="Promo 3" data-index="2"></button>
            <button type="button" class="cust-promo-dot" aria-label="Promo 4" data-index="3"></button>
        </div>
    </section>

    <!-- Category Filter Carousel -->
    <div class="cust-categories-carousel" id="cust-categories-bar">
        <div class="cust-cat-card <?= empty($categoryFilter) ? 'active' : '' ?>" data-category="all">
            <div class="cust-cat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            </div>
            <span class="cust-cat-name">Semua Menu</span>
        </div>

        <?php foreach ($categories as $cat): 
            $isActiveCat = ($categoryFilter === $cat['slug']);
        ?>
            <div class="cust-cat-card <?= $isActiveCat ? 'active' : '' ?>" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                <div class="cust-cat-icon">
                    <?php if (strpos($cat['slug'], 'roti') !== false): ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M7 12a5 5 0 0 1 10 0"></path></svg>
                    <?php elseif (strpos($cat['slug'], 'minum') !== false || strpos($cat['slug'], 'kopi') !== false): ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                    <?php else: ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                    <?php endif; ?>
                </div>
                <span class="cust-cat-name"><?= htmlspecialchars($cat['name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Product Section Title & Count -->
    <div class="cust-section-header">
        <h3 class="cust-section-title">Menu Salt Bread Populer</h3>
        <span class="cust-product-count-badge" id="product-count-label"><?= count($products) ?> Pilihan</span>
    </div>

    <!-- Product Cards Grid -->
    <div class="cust-products-grid" id="cust-products-container">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--cust-card-bg); border-radius: 20px; border: 1px dashed var(--cust-card-border);">
                <h4 style="margin: 0 0 6px; font-weight: 800;">Menu Tidak Ditemukan</h4>
                <p style="color: var(--cust-text-sub); margin: 0;">Silakan pilih kategori lain atau gunakan kata kunci pencarian berbeda.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): 
                $isAvailable = (!isset($p['is_available']) || (int)$p['is_available'] === 1) && (int)$p['stock'] > 0;
                $imgUrl = !empty($p['image_url']) ? $baseUrl . '/' . ltrim($p['image_url'], '/') : $baseUrl . '/assets/img/salt_bread_plain.png';
                $options = !empty($p['options_json']) ? json_decode($p['options_json'], true) : [];
                $hasCustomization = !empty($options['sugar_levels']) || !empty($options['sizes']);
            ?>
                <div class="cust-product-card <?= !$isAvailable ? 'disabled' : '' ?>" 
                     data-id="<?= $p['id'] ?>" 
                     data-name="<?= htmlspecialchars($p['name']) ?>" 
                     data-price="<?= (float)$p['price'] ?>" 
                     data-category="<?= htmlspecialchars($p['category_slug'] ?? '') ?>" 
                     data-img="<?= htmlspecialchars($imgUrl) ?>"
                     data-stock="<?= (int)$p['stock'] ?>"
                     data-has-options="<?= $hasCustomization ? '1' : '0' ?>"
                     data-options='<?= htmlspecialchars($p['options_json'] ?? "{}") ?>'
                     data-available="<?= $isAvailable ? '1' : '0' ?>">
                    
                    <div class="cust-product-img-wrap">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="cust-product-img" loading="lazy" onerror="this.src='<?= $baseUrl ?>/assets/img/salt_bread_plain.png'">
                        <span class="cust-item-cart-count" id="badge-count-<?= $p['id'] ?>" style="display: none;">0</span>
                    </div>

                    <h4 class="cust-product-name"><?= htmlspecialchars($p['name']) ?></h4>
                    <?php if (!$isAvailable): ?>
                        <div class="cust-product-soldout">Habis</div>
                    <?php endif; ?>

                    <div class="cust-product-bottom">
                        <span class="cust-product-price"><?= formatRupiah($p['price']) ?></span>
                        <span class="cust-add-btn" aria-hidden="true" style="pointer-events: none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Product Customization Modal -->
<div class="modal" id="custom-modal">
    <div class="modal-content" style="max-width: 480px; border-radius: 24px; padding: 22px;">
        <div class="modal-header" style="padding-bottom: 12px; border-bottom: 1px solid var(--border);">
            <h3 id="modal-title" style="font-family: var(--font-display); font-size: 1.4rem; margin: 0;">Kustomisasi Pesanan</h3>
            <button class="modal-close" onclick="Modal.close('custom-modal')">&times;</button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 16px 0;">
            <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                <img id="modal-img" src="" alt="" style="width: 70px; height: 70px; object-fit: contain; border-radius: 0; background: transparent; filter: drop-shadow(0 6px 12px rgba(0,0,0,0.12));">
                <div>
                    <h4 id="modal-prod-name" style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800;">Nama Produk</h4>
                    <span id="modal-prod-price" style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">Rp 0</span>
                </div>
            </div>

            <!-- Sugar Level Options -->
            <div id="option-sugar-wrap" class="form-group" style="margin-bottom: 16px; display: none;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Tingkat Manis / Level Gula</label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;" id="sugar-options">
                    <label class="custom-radio-card">
                        <input type="radio" name="sugar" value="normal" checked>
                        <span>Normal (100%)</span>
                    </label>
                    <label class="custom-radio-card">
                        <input type="radio" name="sugar" value="less">
                        <span>Less (50%)</span>
                    </label>
                    <label class="custom-radio-card">
                        <input type="radio" name="sugar" value="none">
                        <span>No Sugar</span>
                    </label>
                </div>
            </div>

            <!-- Size Options -->
            <div id="option-size-wrap" class="form-group" style="margin-bottom: 16px; display: none;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Ukuran Gelas</label>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;" id="size-options">
                    <label class="custom-radio-card">
                        <input type="radio" name="size" value="regular" checked>
                        <span>Regular</span>
                    </label>
                    <label class="custom-radio-card">
                        <input type="radio" name="size" value="large">
                        <span>Large (+Rp 6.000)</span>
                    </label>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" for="modal-notes" style="font-weight: 700; font-size: 0.85rem;">Catatan Khusus (Opsional)</label>
                <textarea id="modal-notes" class="form-textarea" rows="2" placeholder="Contoh: Sedikit es batu, pisahkan saus..." style="border-radius: 12px;"></textarea>
            </div>

            <!-- Quantity Selector -->
            <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Jumlah Porsi</label>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <button type="button" class="btn btn-secondary btn-icon" id="qty-minus" style="width: 36px; height: 36px; font-size: 1.2rem; border-radius: 10px;">-</button>
                    <span id="qty-val" style="font-size: 1.25rem; font-weight: 800; min-width: 30px; text-align: center;">1</span>
                    <button type="button" class="btn btn-secondary btn-icon" id="qty-plus" style="width: 36px; height: 36px; font-size: 1.2rem; border-radius: 10px;">+</button>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border);">
            <span id="modal-total-calc" style="font-size: 1.25rem; font-weight: 800; color: var(--cust-accent-dark);">Rp 0</span>
            <button type="button" class="btn btn-primary" id="btn-add-modal" style="font-weight: 800; border-radius: 14px; padding: 10px 20px;">
                + Tambah ke Keranjang
            </button>
        </div>
    </div>
</div>

<?php if (!isAdmin()): ?>
<!-- Floating Bottom Cart Bar (Customer Mobile & Desktop) -->
<div id="cust-floating-cart" class="cust-floating-cart">
    <div class="cust-float-info">
        <span class="cust-float-badge" id="float-qty">0</span>
        <div class="cust-float-text">
            <span class="cust-float-lbl">Total Pesanan</span>
            <span class="cust-float-total" id="float-subtotal">Rp 0</span>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="cust-float-clear-btn" onclick="openClearCartModal()" title="Kosongkan Keranjang" aria-label="Kosongkan Keranjang">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
        </button>
        <a href="<?= $baseUrl ?>/views/customer/checkout.php" class="cust-float-btn">
            <span>Lihat Keranjang</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
    </div>
</div>

<!-- Custom UI Confirmation Modal: Kosongkan Keranjang -->
<div class="modal" id="modal-clear-cart" style="z-index: 10050;">
    <div class="modal-content" style="max-width: 380px; border-radius: 24px; padding: 26px 20px; text-align: center; border: 1px solid var(--border); box-shadow: 0 20px 45px rgba(0,0,0,0.18);">
        <div style="width: 58px; height: 58px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
        </div>
        <h3 style="font-family: var(--font-display); font-size: 1.45rem; margin: 0 0 8px; color: var(--cust-text-main);">Kosongkan Keranjang?</h3>
        <p style="color: var(--cust-text-sub); font-size: 0.90rem; margin: 0 0 22px; line-height: 1.5;">Seluruh pilihan menu salt bread yang ada di keranjang pesanan Anda akan dihapus.</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn btn-secondary" onclick="Modal.close('modal-clear-cart')" style="flex: 1; border-radius: 14px; font-weight: 700; padding: 11px 16px; border: 1px solid var(--border);">Batal</button>
            <button type="button" class="btn btn-danger" onclick="confirmClearCart()" style="flex: 1; border-radius: 14px; font-weight: 700; padding: 11px 16px; background: #dc2626; color: #ffffff; border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);">Ya, Kosongkan</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ═══════════════════════════════════════════════════════════════════
// CUSTOMER MENU CATALOG JAVASCRIPT LOGIC
// ═══════════════════════════════════════════════════════════════════

const BASE_URL = '<?= $baseUrl ?>';
let currentActiveCategory = '<?= htmlspecialchars($categoryFilter ?? 'all') ?>';
let currentSelectedProduct = null;
let currentQty = 1;

function initCustomerPage() {
    initCustomerSearch();
    initCustomerCategories();
    initCustomerDrawer();
    initCustomerCartSync();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomerPage);
} else {
    initCustomerPage();
}

// Format Rupiah Helper
function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}

// Drawer Trigger
function initCustomerDrawer() {
    const trigger = document.getElementById('cust-drawer-btn');
    const headerDrawerBtn = document.getElementById('tuku-drawer-toggle');
    if (trigger && headerDrawerBtn) {
        trigger.addEventListener('click', () => {
            headerDrawerBtn.click();
        });
    }
}

// Real-time Search Filter
function initCustomerSearch() {
    const searchInput = document.getElementById('cust-search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        filterCustomerProducts(query, currentActiveCategory);
    });
}

// Categories Filter
function initCustomerCategories() {
    const catCards = document.querySelectorAll('.cust-cat-card');
    catCards.forEach(card => {
        card.addEventListener('click', () => {
            catCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            currentActiveCategory = card.dataset.category || 'all';

            const searchQuery = (document.getElementById('cust-search-input')?.value || '').toLowerCase().trim();
            filterCustomerProducts(searchQuery, currentActiveCategory);
        });
    });
}

function filterCustomerProducts(query, category) {
    const cards = document.querySelectorAll('.cust-product-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const cat = (card.dataset.category || '').toLowerCase();

        const matchesQuery = !query || name.includes(query);
        const matchesCat = category === 'all' || cat.includes(category);

        if (matchesQuery && matchesCat) {
            card.classList.remove('is-hidden');
            card.style.removeProperty('display');
            visibleCount++;
        } else {
            card.classList.add('is-hidden');
            card.style.setProperty('display', 'none', 'important');
        }
    });

    const countLabel = document.getElementById('product-count-label');
    if (countLabel) {
        countLabel.textContent = visibleCount + ' Pilihan';
    }
}

// Product Click & Modal Handlers (Unified robust logic for mobile & desktop)
const custProductsContainer = document.getElementById('cust-products-container');
let lastActionTime = 0;
let lastActionCardId = null;

function handleCardSelection(card, e) {
    if (!card || card.classList.contains('disabled')) return;

    if (e) {
        e.preventDefault();
        e.stopPropagation();

        // Reject rapid multi-click/double-click gestures (Windows/desktop rapid clicks or bounce)
        if (e.detail > 1) {
            return;
        }
    }

    <?php if (isAdmin()): ?>
    return;
    <?php endif; ?>

    const productId = parseInt(card.dataset.id);

    // Filter dual-dispatch from mobile touch simulators / hybrid input devices
    const now = Date.now();
    if (lastActionCardId === productId && (now - lastActionTime < 450)) {
        return;
    }
    lastActionTime = now;
    lastActionCardId = productId;
    const hasOptions = card.dataset.hasOptions === '1';
    const prodData = {
        id: productId,
        name: card.dataset.name,
        price: parseFloat(card.dataset.price),
        image: card.dataset.img,
        stock: parseInt(card.dataset.stock)
    };
    const options = JSON.parse(card.dataset.options || '{}');

    if (hasOptions) {
        // Open Customization Modal
        currentSelectedProduct = prodData;
        currentQty = 1;
        document.getElementById('qty-val').textContent = '1';
        document.getElementById('modal-title').textContent = 'Kustomisasi ' + prodData.name;
        document.getElementById('modal-prod-name').textContent = prodData.name;
        document.getElementById('modal-prod-price').textContent = formatRupiah(prodData.price);
        document.getElementById('modal-img').src = prodData.image;
        document.getElementById('modal-notes').value = '';

        const sugarWrap = document.getElementById('option-sugar-wrap');
        const sizeWrap = document.getElementById('option-size-wrap');
        sugarWrap.style.display = options.sugar_levels ? 'block' : 'none';
        sizeWrap.style.display = options.sizes ? 'block' : 'none';

        updateModalTotal();
        Modal.open('custom-modal');
    } else {
        // Instant Add exactly 1 to Cart
        cart.addItem(prodData, 1);
        if (typeof soundNotifier !== 'undefined' && soundNotifier.play) {
            soundNotifier.play('order');
        }
    }
}

if (custProductsContainer) {
    custProductsContainer.addEventListener('click', function(e) {
        const card = e.target.closest('.cust-product-card');
        if (card) {
            handleCardSelection(card, e);
        }
    });
}

// Modal Qty handlers
const qtyMinus = document.getElementById('qty-minus');
const qtyPlus = document.getElementById('qty-plus');
const qtyVal = document.getElementById('qty-val');
const totalCalc = document.getElementById('modal-total-calc');

function updateModalTotal() {
    if (!currentSelectedProduct) return;
    let base = parseInt(currentSelectedProduct.price) || 0;
    const sizeRadio = document.querySelector('input[name="size"]:checked');
    if (sizeRadio && sizeRadio.value === 'large') {
        base += 6000;
    }
    totalCalc.textContent = formatRupiah(base * currentQty);
}

if (qtyMinus && qtyPlus) {
    qtyMinus.addEventListener('click', () => {
        if (currentQty > 1) {
            currentQty--;
            qtyVal.textContent = currentQty;
            updateModalTotal();
        }
    });
    qtyPlus.addEventListener('click', () => {
        const maxStock = currentSelectedProduct ? parseInt(currentSelectedProduct.stock) : 99;
        if (currentQty < maxStock) {
            currentQty++;
            qtyVal.textContent = currentQty;
            updateModalTotal();
        } else {
            if (typeof showToast === 'function') {
                showToast('Jumlah pesanan telah mencapai batas stok.', 'warning');
            }
        }
    });
}

// Listen to size change in modal
document.querySelectorAll('input[name="size"]').forEach(radio => {
    radio.addEventListener('change', updateModalTotal);
});

// Add to cart from modal
let isModalAddLock = false;
const btnAddModal = document.getElementById('btn-add-modal');
if (btnAddModal) {
    btnAddModal.addEventListener('click', () => {
        if (!currentSelectedProduct || isModalAddLock) return;
        isModalAddLock = true;
        btnAddModal.disabled = true;
        setTimeout(() => {
            isModalAddLock = false;
            btnAddModal.disabled = false;
        }, 500);

        const options = {};
        const sugarRadio = document.querySelector('input[name="sugar"]:checked');
        if (sugarRadio && document.getElementById('option-sugar-wrap').style.display !== 'none') {
            options.sugar = sugarRadio.value;
        }

        const sizeRadio = document.querySelector('input[name="size"]:checked');
        let itemPrice = currentSelectedProduct.price;
        if (sizeRadio && document.getElementById('option-size-wrap').style.display !== 'none') {
            options.size = sizeRadio.value;
            if (sizeRadio.value === 'large') itemPrice += 6000;
        }

        const notes = document.getElementById('modal-notes').value.trim();
        if (notes) options.notes = notes;

        cart.addItem({
            ...currentSelectedProduct,
            price: itemPrice,
            options
        }, currentQty);

        Modal.close('custom-modal');
    });
}

// Synchronize card badge counts with Cart state
function initCustomerCartSync() {
    if (typeof cart === 'undefined') {
        document.addEventListener('DOMContentLoaded', initCustomerCartSync);
        return;
    }
    function syncCardBadges() {
        if (typeof cart === 'undefined') return;
        const items = cart.getItems();
        const counts = {};
        items.forEach(it => {
            counts[it.id] = (counts[it.id] || 0) + it.quantity;
        });

        document.querySelectorAll('.cust-product-card').forEach(card => {
            const id = parseInt(card.dataset.id);
            const count = counts[id] || 0;
            const badge = card.querySelector('.cust-item-cart-count');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'flex';
                    card.classList.add('has-cart');
                } else {
                    badge.style.display = 'none';
                    card.classList.remove('has-cart');
                }
            }
        });

        // Update Floating Cart Bar
        const bar = document.getElementById('cust-floating-cart');
        const qtyEl = document.getElementById('float-qty');
        const subtotalEl = document.getElementById('float-subtotal');
        if (bar) {
            const total = cart.getTotalItems();
            if (total > 0) {
                bar.style.display = 'flex';
                if (qtyEl) qtyEl.textContent = total;
                if (subtotalEl) subtotalEl.textContent = formatRupiah(cart.getSubtotal());
            } else {
                bar.style.display = 'none';
            }
        }
    }

    cart.onChange(syncCardBadges);
    syncCardBadges();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomerCartSync);
} else {
    initCustomerCartSync();
}

// Custom UI Modal Handlers for Clearing Cart
function openClearCartModal() {
    Modal.open('modal-clear-cart');
}

function confirmClearCart() {
    cart.clear();
    Modal.close('modal-clear-cart');
    if (typeof Toast !== 'undefined') {
        Toast.info('Keranjang berhasil dikosongkan');
    }
}

// Promotional Carousel: Smooth Infinite Forward Loop (Zero Zig-Zag, Paced at 3.2s)
function initPromoCarousel() {
    const viewport = document.getElementById('custPromoViewport');
    const track = document.getElementById('custPromoTrack');
    const dots = document.querySelectorAll('.cust-promo-dot');
    if (!viewport || !track || !dots.length) return;

    const originalCards = Array.from(track.querySelectorAll('.cust-promo-card'));
    const totalOriginals = originalCards.length;
    if (totalOriginals <= 1) return;

    // Clone all original cards and append to track for seamless infinite looping
    originalCards.forEach((card, idx) => {
        const clone = card.cloneNode(true);
        clone.setAttribute('data-is-clone', 'true');
        clone.setAttribute('data-orig-idx', idx);
        track.appendChild(clone);
    });

    let allCards = Array.from(track.querySelectorAll('.cust-promo-card'));
    let currentIndex = 0;
    let autoTimer = null;
    let isTransitioning = false;
    let isUserDragging = false;
    let startX = 0;
    let currentDeltaX = 0;
    let resumeTimer = null;

    function getStepWidth() {
        if (allCards.length < 2) return 320;
        return allCards[1].offsetLeft - allCards[0].offsetLeft;
    }

    function updateActiveDot(index) {
        const origIndex = ((index % totalOriginals) + totalOriginals) % totalOriginals;
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === origIndex);
        });
        allCards.forEach((card, i) => {
            card.classList.toggle('active-promo', (i % totalOriginals) === origIndex);
        });
    }

    function moveToSlide(index, animate = true) {
        currentIndex = index;
        const step = getStepWidth();
        const offset = currentIndex * step;

        if (animate) {
            isTransitioning = true;
            track.style.transition = 'transform 0.65s cubic-bezier(0.22, 1, 0.36, 1)';
        } else {
            track.style.transition = 'none';
        }
        track.style.transform = `translate3d(-${offset}px, 0, 0)`;
        updateActiveDot(currentIndex);
    }

    // Seamless loop reset when transition ends (eliminates all backward zig-zags)
    track.addEventListener('transitionend', () => {
        isTransitioning = false;
        if (currentIndex >= totalOriginals) {
            // Instantly snap back to the corresponding original slide with transition: none
            currentIndex = currentIndex % totalOriginals;
            moveToSlide(currentIndex, false);
        } else if (currentIndex < 0) {
            currentIndex = totalOriginals - 1;
            moveToSlide(currentIndex, false);
        }
    });

    function nextSlide() {
        if (isUserDragging || isTransitioning) return;
        moveToSlide(currentIndex + 1, true);
    }

    function prevSlide() {
        if (isUserDragging || isTransitioning) return;
        if (currentIndex === 0) {
            currentIndex = totalOriginals;
            moveToSlide(currentIndex, false);
            track.offsetHeight; // Force reflow
        }
        moveToSlide(currentIndex - 1, true);
    }

    function startAutoTimer() {
        stopAutoTimer();
        autoTimer = setInterval(nextSlide, 3200);
    }

    function stopAutoTimer() {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    }

    // Dots navigation
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            moveToSlide(idx, true);
            startAutoTimer();
        });
    });

    // Touch Swipe handling on mobile
    viewport.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        isUserDragging = true;
        startX = e.touches[0].clientX;
        currentDeltaX = 0;
        clearTimeout(resumeTimer);
        stopAutoTimer();
        track.style.transition = 'none';
    }, { passive: true });

    viewport.addEventListener('touchmove', (e) => {
        if (!isUserDragging) return;
        const x = e.touches[0].clientX;
        currentDeltaX = x - startX;
        const step = getStepWidth();
        const baseOffset = currentIndex * step;
        track.style.transform = `translate3d(-${baseOffset - currentDeltaX}px, 0, 0)`;
    }, { passive: true });

    viewport.addEventListener('touchend', () => {
        if (!isUserDragging) return;
        isUserDragging = false;
        const threshold = 40;
        if (currentDeltaX < -threshold) {
            moveToSlide(currentIndex + 1, true);
        } else if (currentDeltaX > threshold) {
            prevSlide();
        } else {
            moveToSlide(currentIndex, true);
        }
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(startAutoTimer, 2000);
    }, { passive: true });

    // Handle clicks on cards to filter or scroll to products
    allCards.forEach((card) => {
        card.addEventListener('click', () => {
            if (Math.abs(currentDeltaX) > 10) return; // Ignore drag clicks
            const promoType = card.getAttribute('data-promo');
            if (promoType === 'variants' || promoType === 'oven') {
                const allTab = document.querySelector('.cust-cat-card[data-category="all"]');
                if (allTab) allTab.click();
            } else if (promoType === 'pairing') {
                const drinkTab = document.querySelector('.cust-cat-card[data-category="minuman"]') ||
                                 document.querySelector('.cust-cat-card[data-category="kopi"]');
                if (drinkTab) drinkTab.click();
            }
            const grid = document.getElementById('cust-products-container');
            if (grid) {
                grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    window.addEventListener('resize', () => {
        moveToSlide(currentIndex % totalOriginals, false);
    });

    // Start immediately with initial slide
    moveToSlide(0, false);
    startAutoTimer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPromoCarousel);
} else {
    initPromoCarousel();
}
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
