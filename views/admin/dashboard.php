<?php
/**
 * Admin Dashboard POS — Clean, Modern, & Warm Aesthetic
 * Inspired by modern cafe/bakery POS interfaces (Little Salt Bread Blok M)
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/MenuController.php';

initSession();
requireAdmin('/views/auth/login.php');

$currentUser = getCurrentUser();
$pdo = getDb();

// Fetch Categories & Products for POS
$catRes = MenuController::getCategories();
$categories = $catRes['data'] ?? [];

$menuRes = MenuController::getAll();
$products = $menuRes['data'] ?? [];

// Fetch Live Stats
$today = date('Y-m-d');
$totalProducts = count($products);
$totalCategories = count($categories);

// Active queues (pending, processing, ready)
$stmtQueue = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'processing', 'ready')");
// Active queues (pending, confirmed, processing, ready, shelf)
$stmtQueue = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'confirmed', 'processing', 'ready', 'shelf')");
// Active queues (confirmed, processing, ready, shelf, or paid pending)
$stmtQueue = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE (order_status IN ('confirmed', 'processing', 'ready', 'shelf') OR (order_status = 'pending' AND payment_status = 'paid'))");
$stmtQueue->execute();
$activeQueueCount = (int)($stmtQueue->fetch()['count'] ?? 0);

// Today's orders & revenue
$stmtToday = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = ? AND order_status != 'cancelled'");
$stmtToday->execute([$today]);
$todayStat = $stmtToday->fetch();
$todayOrders = (int)($todayStat['count'] ?? 0);
$todayRevenue = (float)($todayStat['revenue'] ?? 0);

// Fetch active orders for live queue panel
// Fetch active orders for live queue panel (prioritize today's and newest orders)
$stmtLiveOrders = $pdo->prepare("
    SELECT o.id, o.order_code, o.queue_number, o.customer_name, o.order_type, o.order_status, o.total_amount, o.created_at
    SELECT o.id, o.order_code, o.queue_number, o.customer_name, o.order_type, o.order_status, o.payment_status, o.total_amount, o.created_at
    FROM orders o
    WHERE o.order_status IN ('pending', 'processing', 'ready')
    ORDER BY o.created_at ASC
    LIMIT 10
    WHERE o.order_status IN ('pending', 'confirmed', 'processing', 'ready', 'shelf')
    WHERE (o.order_status IN ('confirmed', 'processing', 'ready', 'shelf') OR (o.order_status = 'pending' AND o.payment_status = 'paid'))
    ORDER BY (DATE(o.created_at) = CURDATE()) DESC, o.id DESC
    LIMIT 20
");
$stmtLiveOrders->execute();
$liveOrders = $stmtLiveOrders->fetchAll();

// Get items for live orders
foreach ($liveOrders as &$lo) {
    $stmtItems = $pdo->prepare("SELECT item_name, quantity FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$lo['id']]);
    $lo['items'] = $stmtItems->fetchAll();
}
unset($lo);

$pageTitle = 'Dashboard Kasir & POS';
$bodyClass = 'admin-page pos-dashboard-view';
$extraCss = 'admin.css';

require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════════════
   CLEAN POS DASHBOARD — PALETTE & COMPONENT STYLING
   ═══════════════════════════════════════════════════════════════════ */
:root {
  --pos-bg: #ffffff;
  --pos-card-bg: #ffffff;
  --pos-card-border: rgba(0, 0, 0, 0.08);
  --pos-accent: #f59e0b;
  --pos-accent-dark: #d97706;
  --pos-accent-light: #fef3c7;
  --pos-text-main: #1f2937;
  --pos-text-sub: #6b7280;
  --pos-shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.04);
  --pos-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.08);
}

[data-theme='dark'] {
  --pos-bg: #111215;
  --pos-card-bg: #1c1d22;
  --pos-card-border: rgba(255, 255, 255, 0.08);
  --pos-accent: #f59e0b;
  --pos-accent-dark: #d97706;
  --pos-accent-light: rgba(245, 158, 11, 0.15);
  --pos-text-main: #f3f4f6;
  --pos-text-sub: #9ca3af;
  --pos-shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.25);
  --pos-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.4);
}

body.pos-dashboard-view {
  background-color: var(--pos-bg) !important;
  color: var(--pos-text-main);
  min-height: 100vh;
}

.pos-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 16px 20px 80px 20px;
}

/* ─── Top Control Bar ─── */
.pos-topbar {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 20px;
}

.pos-menu-trigger {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: var(--pos-accent);
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
.pos-menu-trigger:hover {
  transform: scale(1.04);
  background: var(--pos-accent-dark);
}
.pos-menu-trigger span {
  display: block;
  width: 18px;
  height: 2.5px;
  background: #ffffff;
  border-radius: 2px;
}

.pos-search-box {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}
.pos-search-input {
  width: 100%;
  height: 44px;
  background: var(--pos-card-bg);
  border: 1px solid var(--pos-card-border);
  border-radius: 9999px;
  padding: 0 20px 0 44px;
  font-size: 0.92rem;
  font-family: inherit;
  color: var(--pos-text-main);
  box-shadow: var(--pos-shadow-soft);
  transition: all 0.2s ease;
  outline: none;
}
.pos-search-input:focus {
  border-color: var(--pos-accent);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}
.pos-search-icon {
  position: absolute;
  left: 16px;
  color: var(--pos-text-sub);
  pointer-events: none;
  display: flex;
  align-items: center;
}

.pos-top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.pos-icon-btn {
  height: 44px;
  padding: 0 14px;
  border-radius: 14px;
  background: var(--pos-card-bg);
  border: 1px solid var(--pos-card-border);
  color: var(--pos-text-main);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  box-shadow: var(--pos-shadow-soft);
  transition: all 0.15s ease;
}
.pos-icon-btn:hover {
  border-color: var(--pos-accent);
  color: var(--pos-accent);
}

.pos-cart-toggle-btn {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: var(--pos-accent-light);
  border: 1px solid rgba(245, 158, 11, 0.2);
  color: var(--pos-accent-dark);
  display: none; /* Visible on mobile */
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.15s ease;
}
.pos-cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #10b981;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 800;
  border-radius: 9999px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* ─── Two-Column POS Layout ─── */
.pos-grid-container {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 22px;
  align-items: start;
}

/* ─── Left Column: Store Hero, Categories, Products ─── */
.pos-main-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Store Hero Banner */
.pos-store-banner {
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
}
.pos-store-banner::after {
  display: none;
}
.pos-store-profile {
  display: flex;
  align-items: center;
  gap: 16px;
  z-index: 1;
}
.pos-store-avatar {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  background: var(--pos-accent);
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
.pos-store-name {
  font-family: var(--font-display, 'Patrick Hand', cursive);
  font-size: 1.7rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.1;
  color: #ffffff;
  letter-spacing: 0.5px;
}
.pos-store-tagline {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 3px 0 0;
}

.pos-store-stats {
  display: flex;
  align-items: center;
  gap: 20px;
  z-index: 1;
}
.pos-stat-col {
  text-align: center;
  padding: 0 10px;
}
.pos-stat-col:not(:last-child) {
  border-right: 1px solid rgba(255, 255, 255, 0.15);
  padding-right: 20px;
}
.pos-stat-num {
  font-size: 1.55rem;
  font-weight: 800;
  color: var(--pos-accent);
  line-height: 1;
  font-feature-settings: 'tnum';
}
.pos-stat-lbl {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 4px;
  white-space: nowrap;
}

/* Category Filter Carousel */
.pos-categories-carousel {
  display: flex;
  align-items: center;
  gap: 12px;
  overflow-x: auto;
  padding: 2px 2px 10px 2px;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}
.pos-categories-carousel::-webkit-scrollbar {
  display: none;
}
.pos-cat-card {
  flex: 0 0 auto;
  background: var(--pos-card-bg);
  border: 1px solid var(--pos-card-border);
  border-radius: 18px;
  padding: 12px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: var(--pos-shadow-soft);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 90px;
  user-select: none;
}
.pos-cat-card:hover {
  transform: translateY(-2px);
  border-color: var(--pos-accent);
}
.pos-cat-card.active {
  background: var(--pos-accent) !important;
  border-color: var(--pos-accent) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
}
.pos-cat-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pos-cat-card.active .pos-cat-icon svg {
  stroke: #ffffff;
}
.pos-cat-card:not(.active) .pos-cat-icon svg {
  stroke: var(--pos-accent-dark);
}
.pos-cat-name {
  font-size: 0.82rem;
  font-weight: 700;
  white-space: nowrap;
}

/* Product Section Header */
.pos-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
}
.pos-section-title {
  font-family: var(--font-display, cursive);
  font-size: 1.55rem;
  font-weight: 700;
  margin: 0;
  color: var(--pos-text-main);
}
.pos-product-count-badge {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--pos-text-sub);
  background: var(--pos-card-bg);
  padding: 4px 12px;
  border-radius: 9999px;
  border: 1px solid var(--pos-card-border);
}

/* Product Grid */
.pos-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
  gap: 16px;
}

.pos-product-card {
  background: var(--pos-card-bg);
  border: 1px solid var(--pos-card-border);
  border-radius: 22px;
  padding: 16px 14px 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  box-shadow: var(--pos-shadow-soft);
  position: relative;
  transition: all 0.22s ease;
  cursor: pointer;
  user-select: none;
}
.pos-product-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--pos-shadow-hover);
  border-color: rgba(245, 158, 11, 0.3);
}
.pos-product-card.has-cart {
  border-color: var(--pos-accent);
}
.pos-product-card.disabled {
  opacity: 0.55;
  cursor: not-allowed;
  filter: grayscale(0.6);
}
.pos-product-card.is-hidden {
  display: none !important;
}

/* Floating Dish Image */
.pos-product-img-wrap {
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
.pos-product-img {
  width: 100%;
  max-width: 100%;
  height: 100%;
  max-height: 115px;
  object-fit: contain;
  filter: drop-shadow(0 8px 14px rgba(0, 0, 0, 0.12));
  transition: transform 0.25s ease, filter 0.25s ease;
}
.pos-product-card:hover .pos-product-img {
  transform: scale(1.08) translateY(-3px);
  filter: drop-shadow(0 14px 22px rgba(0, 0, 0, 0.16));
}

.pos-item-cart-count {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--pos-accent);
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

.pos-product-name {
  font-size: 0.92rem;
  font-weight: 700;
  margin: 0 0 4px;
  color: var(--pos-text-main);
  line-height: 1.25;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.5em;
}
.pos-product-meta {
  font-size: 0.72rem;
  color: var(--pos-text-sub);
  margin-bottom: 12px;
}

.pos-product-bottom {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px dashed var(--pos-card-border);
}
.pos-product-price {
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--pos-text-main);
  font-feature-settings: 'tnum';
}
.pos-add-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--pos-accent);
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
.pos-add-btn:hover {
  transform: scale(1.12);
  background: var(--pos-accent-dark);
}
.pos-product-card.disabled .pos-add-btn {
  background: #cbd5e1;
  box-shadow: none;
  pointer-events: none;
}

/* ─── Right Column: Cashier Panel (My Cart & Live Queue) ─── */
.pos-sidebar-panel {
  background: var(--pos-card-bg);
  border: 1px solid var(--pos-card-border);
  border-radius: 24px;
  padding: 20px;
  box-shadow: var(--pos-shadow-soft);
  position: sticky;
  top: 85px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-height: calc(100vh - 100px);
}

.pos-panel-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
  background: var(--pos-bg);
  padding: 4px;
  border-radius: 14px;
}
.pos-tab-btn {
  padding: 8px 10px;
  font-size: 0.84rem;
  font-weight: 700;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: var(--pos-text-sub);
  cursor: pointer;
  transition: all 0.15s ease;
}
.pos-tab-btn.active {
  background: var(--pos-card-bg);
  color: var(--pos-accent-dark);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Tab 1: POS Cart View */
.pos-cart-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
  min-height: 0;
}
.pos-cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pos-cart-title {
  font-family: var(--font-display, cursive);
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}
.pos-clear-btn {
  background: none;
  border: none;
  color: var(--pos-text-sub);
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
}
.pos-clear-btn:hover {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.08);
}

/* Order Settings */
.pos-order-opts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.pos-opt-btn {
  padding: 7px;
  font-size: 0.8rem;
  font-weight: 700;
  border-radius: 10px;
  border: 1px solid var(--pos-card-border);
  background: var(--pos-bg);
  color: var(--pos-text-sub);
  cursor: pointer;
  text-align: center;
  transition: all 0.15s ease;
}
.pos-opt-btn.active {
  background: var(--pos-accent-light);
  border-color: var(--pos-accent);
  color: var(--pos-accent-dark);
}

.pos-input-compact {
  width: 100%;
  padding: 8px 12px;
  font-size: 0.82rem;
  border-radius: 10px;
  border: 1px solid var(--pos-card-border);
  background: var(--pos-bg);
  color: var(--pos-text-main);
  outline: none;
  font-family: inherit;
}
.pos-input-compact:focus {
  border-color: var(--pos-accent);
}

/* Cart Items List */
.pos-cart-list {
  flex: 1;
  overflow-y: auto;
  max-height: 250px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-right: 4px;
}
.pos-cart-list::-webkit-scrollbar {
  width: 4px;
}
.pos-cart-list::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 4px;
}

.pos-cart-empty {
  text-align: center;
  padding: 30px 10px;
  color: var(--pos-text-sub);
  font-size: 0.85rem;
}
.pos-cart-empty svg {
  margin-bottom: 8px;
  opacity: 0.4;
}

.pos-cart-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid var(--pos-card-border);
}
.pos-cart-item-img {
  width: 38px;
  height: 38px;
  border-radius: 0;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.12));
  flex-shrink: 0;
  background: transparent;
}
.pos-cart-item-info {
  flex: 1;
  min-width: 0;
}
.pos-cart-item-name {
  font-size: 0.84rem;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--pos-text-main);
}
.pos-cart-item-price {
  font-size: 0.78rem;
  color: var(--pos-text-sub);
}
.pos-qty-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}
.pos-qty-btn {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 1px solid var(--pos-card-border);
  background: var(--pos-bg);
  color: var(--pos-text-main);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
}
.pos-qty-val {
  font-size: 0.82rem;
  font-weight: 800;
  min-width: 14px;
  text-align: center;
}

/* Pricing Summary Box */
.pos-summary-box {
  background: var(--pos-bg);
  border-radius: 16px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  border: 1px solid var(--pos-card-border);
}
.pos-summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
  color: var(--pos-text-sub);
}
.pos-summary-row.total {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--pos-text-main);
  border-top: 1px dashed var(--pos-card-border);
  padding-top: 6px;
  margin-top: 2px;
}
.pos-summary-row.total span:last-child {
  color: var(--pos-accent-dark);
}

/* Big Green Checkout Button */
.pos-checkout-btn {
  width: 100%;
  background: #10b981;
  color: #ffffff;
  border: none;
  border-radius: 14px;
  padding: 14px;
  font-size: 0.95rem;
  font-weight: 800;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  transition: all 0.2s ease;
}
.pos-checkout-btn:hover {
  background: #059669;
  transform: translateY(-1px);
}
.pos-checkout-btn:disabled {
  background: #cbd5e1;
  box-shadow: none;
  cursor: not-allowed;
  transform: none;
}

/* Tab 2: Live Queue View */
.pos-queue-section {
  display: none;
  flex-direction: column;
  gap: 10px;
  overflow-y: auto;
  max-height: 480px;
}
.pos-queue-card {
  background: var(--pos-bg);
  border-radius: 14px;
  padding: 12px;
  border: 1px solid var(--pos-card-border);
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.pos-queue-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pos-queue-badge {
  font-family: var(--font-display, cursive);
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--pos-accent-dark);
}
.pos-status-badge {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 9999px;
  text-transform: capitalize;
}
.pos-status-pending { background: #fef3c7; color: #b45309; }
.pos-status-processing { background: #dbeafe; color: #1e40af; }
.pos-status-ready { background: #d1fae5; color: #065f46; }

.pos-queue-items {
  font-size: 0.78rem;
  color: var(--pos-text-sub);
  line-height: 1.3;
}
.pos-queue-actions {
  display: flex;
  gap: 6px;
  margin-top: 4px;
}
.pos-action-btn-sm {
  flex: 1;
  padding: 5px;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  text-align: center;
}
.pos-action-btn-ready {
  background: #10b981;
  color: #fff;
}
.pos-action-btn-done {
  background: var(--pos-accent);
  color: #fff;
}

/* ─── Modal Struk POS ─── */
.pos-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 10000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.pos-modal-card {
  background: #ffffff;
  color: #1f2937;
  border-radius: 20px;
  max-width: 380px;
  width: 100%;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  text-align: center;
  position: relative;
  animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes modalPopIn {
  from { transform: scale(0.92); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.pos-receipt-qnum {
  font-family: var(--font-display, cursive);
  font-size: 2.8rem;
  font-weight: 800;
  color: var(--pos-accent-dark);
  margin: 10px 0;
  line-height: 1;
}

/* ─── Responsive Queries ─── */
@media (max-width: 1024px) {
  .pos-grid-container {
    grid-template-columns: 1fr;
  }
  .pos-cart-toggle-btn {
    display: inline-flex;
  }
  .pos-sidebar-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    top: auto;
    border-radius: 24px 24px 0 0;
    z-index: 9998;
    max-height: 80vh;
    box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.2);
    transform: translateY(105%);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .pos-sidebar-panel.mobile-open {
    transform: translateY(0);
  }
  .pos-mobile-cart-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 9997;
    display: none;
  }
  .pos-mobile-cart-backdrop.active {
    display: block;
  }
  .pos-mobile-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--pos-card-bg);
    border-top: 1px solid var(--pos-card-border);
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 9996;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
  }
}

@media (max-width: 640px) {
  .pos-wrapper {
    padding: 12px 12px 90px 12px;
    padding: 12px 10px 100px 10px;
  }
  .pos-topbar {
    gap: 8px;
    margin-bottom: 14px;
  }
  .pos-menu-trigger {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }
  .pos-search-input {
    height: 40px;
    padding: 0 14px 0 38px;
    font-size: 0.84rem;
  }
  .pos-search-icon {
    left: 12px;
  }
  .pos-top-actions {
    display: none !important;
  }
  .pos-store-banner {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    padding: 18px;
    gap: 12px;
    padding: 14px 14px;
    border-radius: 16px;
    margin-bottom: 14px;
  }
  .pos-store-avatar {
    width: 42px;
    height: 42px;
    font-size: 1.05rem;
  }
  .pos-store-name {
    font-size: 1.15rem;
  }
  .pos-store-tagline {
    font-size: 0.72rem;
  }
  .pos-store-stats {
    width: 100%;
    justify-content: space-between;
  }
  .pos-stat-col {
    padding: 0 4px;
    padding: 0 2px;
  }
  .pos-stat-col:not(:last-child) {
    padding-right: 12px;
    padding-right: 8px;
  }
  .pos-stat-val {
    font-size: 1rem;
  }
  .pos-stat-lbl {
    font-size: 0.65rem;
  }

  /* 3 Categories fit seamlessly without cut-off */
  .pos-categories-carousel {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 6px !important;
    overflow-x: visible !important;
    padding: 2px 0 12px 0 !important;
  }
  .pos-cat-card {
    min-width: 0 !important;
    width: 100% !important;
    padding: 9px 3px 7px !important;
    border-radius: 13px !important;
    gap: 4px !important;
  }
  .pos-cat-icon {
    width: 26px !important;
    height: 26px !important;
  }
  .pos-cat-icon svg {
    width: 17px !important;
    height: 17px !important;
  }
  .pos-cat-name {
    font-size: 0.69rem !important;
    white-space: normal !important;
    text-align: center !important;
    line-height: 1.15 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
  }

  /* Section Header */
  .pos-section-header {
    margin-bottom: 10px;
    padding: 0 2px;
  }
  .pos-section-title {
    font-size: 1.25rem;
  }
  .pos-product-count-badge {
    font-size: 0.70rem;
    padding: 3px 8px;
  }

  /* 3 Product Columns (3 menu per baris horizontal) */
  .pos-products-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 8px !important;
  }
  .pos-product-card {
    padding: 10px 6px 9px !important;
    border-radius: 16px !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    min-height: 160px !important;
  }
  .pos-product-img-wrap {
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
  .pos-product-img {
    width: 100% !important;
    max-width: 100% !important;
    height: 100% !important;
    max-height: 82px !important;
    object-fit: contain !important;
    filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.12)) !important;
  }
  .pos-product-name {
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
  .pos-product-meta {
    display: none !important;
  }
  .pos-product-soldout {
    display: inline-block !important;
    font-size: 0.60rem !important;
    color: var(--error, #ef4444) !important;
    font-weight: 700 !important;
    margin-bottom: 4px !important;
  }
  .pos-product-bottom {
    padding-top: 6px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    border-top: 1px dashed var(--pos-card-border) !important;
  }
  .pos-product-price {
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    white-space: nowrap !important;
  }
  .pos-add-btn {
    width: 26px !important;
    height: 26px !important;
  }
  .pos-add-btn svg {
    width: 14px !important;
    height: 14px !important;
  }
  .pos-item-cart-count {
    width: 18px !important;
    height: 18px !important;
    font-size: 0.62rem !important;
    top: -2px !important;
    right: -2px !important;
  }
}
</style>

<div class="pos-wrapper">
    <!-- Top Control Bar (Search + Menu + Actions) -->
    <div class="pos-topbar">
        <!-- Amber Menu Drawer Button -->
        <button class="pos-menu-trigger" id="pos-drawer-btn" title="Buka Menu Navigasi" aria-label="Menu Navigasi">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Search Box -->
        <div class="pos-search-box">
            <span class="pos-search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" class="pos-search-input" id="pos-search-input" placeholder="Cari menu salt bread, varian, atau rasa..." autocomplete="off">
            <input type="text" class="pos-search-input" id="pos-search-input" placeholder="Cari menu salt bread..." autocomplete="off">
        </div>

        <!-- Right Quick Actions -->
        <div class="pos-top-actions">
            <a href="<?= $baseUrl ?>/monitor.php" target="_blank" class="pos-icon-btn" title="Buka TV Display Monitor">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg>
                <span class="d-none d-sm-inline">TV Monitor</span>
            </a>

            <!-- Mobile Cart Button -->
            <button class="pos-cart-toggle-btn" id="mobile-cart-toggle" aria-label="Buka Keranjang Kasir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                <span class="pos-cart-badge" id="mobile-cart-badge">0</span>
            </button>
        </div>
    </div>

    <!-- Main POS Grid (Left: Menu, Right: Cart Panel) -->
    <div class="pos-grid-container">
        <!-- LEFT COLUMN -->
        <div class="pos-main-content">
            <!-- Store Hero Banner -->
            <div class="pos-store-banner">
                <div class="pos-store-profile">
                    <div class="pos-store-avatar">SB</div>
                    <div>
                        <h2 class="pos-store-name">Little Salt Bread</h2>
                        <p class="pos-store-tagline">Fresh & warm artisanal salt bread recipe • Blok M</p>
                    </div>
                </div>

                <div class="pos-store-stats">
                    <div class="pos-stat-col">
                        <div class="pos-stat-num"><?= $totalProducts ?></div>
                        <div class="pos-stat-lbl">Total Item</div>
                    </div>
                    <div class="pos-stat-col">
                        <div class="pos-stat-num"><?= $totalCategories ?></div>
                        <div class="pos-stat-lbl">Kategori</div>
                    </div>
                    <div class="pos-stat-col">
                        <div class="pos-stat-num" id="stat-active-queue"><?= $activeQueueCount ?></div>
                        <div class="pos-stat-lbl">Antrean Aktif</div>
                    </div>
                </div>
            </div>

            <!-- Categories Filter Carousel -->
            <div class="pos-categories-carousel" id="pos-categories-bar">
                <div class="pos-cat-card active" data-category="all">
                    <div class="pos-cat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <span class="pos-cat-name">Semua Menu</span>
                </div>

                <?php foreach ($categories as $cat): ?>
                    <div class="pos-cat-card" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                        <div class="pos-cat-icon">
                            <?php if (strpos($cat['slug'], 'roti') !== false): ?>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M7 12a5 5 0 0 1 10 0"></path></svg>
                            <?php elseif (strpos($cat['slug'], 'minum') !== false || strpos($cat['slug'], 'kopi') !== false): ?>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                            <?php else: ?>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                            <?php endif; ?>
                        </div>
                        <span class="pos-cat-name"><?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Product Section Title & Count -->
            <div class="pos-section-header">
                <h3 class="pos-section-title">Menu Salt Bread Populer</h3>
                <span class="pos-product-count-badge" id="product-count-label"><?= count($products) ?> Pilihan</span>
            </div>

            <!-- Product Cards Grid -->
            <div class="pos-products-grid" id="pos-products-container">
                <?php foreach ($products as $p): 
                    $isAvailable = isset($p['is_available']) ? (int)$p['is_available'] === 1 : true;
                    if ($p['stock'] <= 0) $isAvailable = false;
                    $imgUrl = !empty($p['image_url']) ? $baseUrl . '/' . ltrim($p['image_url'], '/') : $baseUrl . '/assets/img/salt_bread_plain.png';
                ?>
                    <div class="pos-product-card <?= !$isAvailable ? 'disabled' : '' ?>" 
                         data-id="<?= $p['id'] ?>" 
                         data-name="<?= htmlspecialchars($p['name']) ?>" 
                         data-price="<?= (float)$p['price'] ?>" 
                         data-category="<?= htmlspecialchars($p['category_slug'] ?? '') ?>" 
                         data-img="<?= htmlspecialchars($imgUrl) ?>"
                         data-stock="<?= (int)$p['stock'] ?>"
                         data-available="<?= $isAvailable ? '1' : '0' ?>"
                         onclick="handleProductClick(<?= $p['id'] ?>, event)">
                        
                        <div class="pos-product-img-wrap">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="pos-product-img" loading="lazy" onerror="this.src='<?= $baseUrl ?>/assets/img/salt_bread_plain.png'">
                            <span class="pos-item-cart-count" id="badge-count-<?= $p['id'] ?>" style="display: none;">0</span>
                        </div>

                        <h4 class="pos-product-name"><?= htmlspecialchars($p['name']) ?></h4>
                        <?php if (!$isAvailable): ?>
                            <div class="pos-product-soldout">Habis</div>
                        <?php endif; ?>

                        <div class="pos-product-bottom">
                            <span class="pos-product-price"><?= formatRupiah($p['price']) ?></span>
                            <span class="pos-add-btn" aria-hidden="true" style="pointer-events: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT COLUMN: CASHIER CART & QUEUE PANEL -->
        <aside class="pos-sidebar-panel" id="pos-cart-panel">
            <!-- Tabs Switcher -->
            <div class="pos-panel-tabs">
                <button type="button" class="pos-tab-btn active" id="tab-btn-cart" onclick="switchPosTab('cart')">
                    Kasir POS
                </button>
                <button type="button" class="pos-tab-btn" id="tab-btn-queue" onclick="switchPosTab('queue')">
                    Antrean Dapur (<span id="queue-badge-count"><?= count($liveOrders) ?></span>)
                </button>
            </div>

            <!-- Tab 1 Content: POS Cart -->
            <div class="pos-cart-section" id="panel-cart-view">
                <div class="pos-cart-header">
                    <h3 class="pos-cart-title">Pesanan Kasir</h3>
                    <button type="button" class="pos-clear-btn" onclick="clearPosCart()">Reset</button>
                </div>

                <!-- Order Settings (Dine in / Takeaway) -->
                <div class="pos-order-opts">
                    <button type="button" class="pos-opt-btn active" id="btn-type-takeaway" onclick="setOrderType('takeaway')">Take-away</button>
                    <button type="button" class="pos-opt-btn" id="btn-type-dinein" onclick="setOrderType('dine_in')">Dine-in</button>
                </div>

                <!-- Customer Name -->
                <input type="text" class="pos-input-compact" id="pos-customer-name" placeholder="Nama Pelanggan (contoh: Kak Budi)" autocomplete="off">

                <!-- Payment Method Toggle -->
                <div class="pos-order-opts">
                    <button type="button" class="pos-opt-btn active" id="btn-pay-cash" onclick="setPaymentMethod('cash')">Tunai (Cash)</button>
                    <button type="button" class="pos-opt-btn" id="btn-pay-qris" onclick="setPaymentMethod('qris')">QRIS / Instant</button>
                </div>

                <!-- Cart Items Container -->
                <div class="pos-cart-list" id="pos-cart-items-list">
                    <div class="pos-cart-empty" id="pos-cart-empty-state">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <div>Keranjang kasir masih kosong.<br>Klik menu di samping untuk tambah.</div>
                    </div>
                </div>

                <!-- Pricing Summary -->
                <div class="pos-summary-box">
                    <div class="pos-summary-row">
                        <span>Subtotal</span>
                        <span id="pos-summary-subtotal">Rp 0</span>
                    </div>
                    <div class="pos-summary-row">
                        <span>Diskon</span>
                        <span id="pos-summary-discount">Rp 0</span>
                    </div>
                    <div class="pos-summary-row total">
                        <span>Total Akhir</span>
                        <span id="pos-summary-total">Rp 0</span>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button type="button" class="pos-checkout-btn" id="pos-checkout-btn" onclick="processPosCheckout()" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>Checkout & Buat Pesanan</span>
                </button>
            </div>

            <!-- Tab 2 Content: Live Kitchen Queue -->
            <div class="pos-queue-section" id="panel-queue-view">
                <div class="pos-cart-header">
                    <h3 class="pos-cart-title">Antrean Dapur</h3>
                    <a href="<?= $baseUrl ?>/views/admin/orders.php" style="font-size: 0.8rem; color: var(--pos-accent-dark); text-decoration: none; font-weight: 700;">Buka Penuh &rarr;</a>
                </div>

                <div id="pos-live-queue-list">
                    <?php if (empty($liveOrders)): ?>
                        <div class="pos-cart-empty">Tidak ada antrean pesanan aktif saat ini.</div>
                    <?php else: ?>
                        <?php foreach ($liveOrders as $lo): ?>
                            <div class="pos-queue-card" id="q-card-<?= $lo['id'] ?>">
                                <div class="pos-queue-top">
                                    <span class="pos-queue-badge"><?= htmlspecialchars($lo['queue_number']) ?></span>
                                    <span class="pos-status-badge pos-status-<?= $lo['order_status'] ?>">
                                        <?= $lo['order_status'] === 'processing' ? 'Dipanggang' : ($lo['order_status'] === 'ready' ? 'Siap' : 'Menunggu') ?>
                                        <?= $lo['order_status'] === 'processing' ? 'Dipanggang' : ($lo['order_status'] === 'ready' ? 'Siap' : ($lo['order_status'] === 'shelf' ? 'Rak Mandiri' : ($lo['order_status'] === 'confirmed' ? 'Dikonfirmasi' : ($lo['payment_status'] === 'paid' ? 'Dibayar' : 'Menunggu Bayar')))) ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; font-weight: 700;">
                                    <?= htmlspecialchars($lo['customer_name'] ?? 'Pelanggan') ?> • <span style="font-weight: 400; text-transform: capitalize;"><?= $lo['order_type'] === 'dine_in' ? 'Dine-in' : 'Takeaway' ?></span>
                                </div>
                                <div class="pos-queue-items">
                                    <?php 
                                    $itemSummaries = [];
                                    foreach ($lo['items'] as $item) {
                                        $itemSummaries[] = $item['quantity'] . 'x ' . $item['item_name'];
                                    }
                                    echo htmlspecialchars(implode(', ', $itemSummaries));
                                    ?>
                                </div>
                                <div class="pos-queue-actions">
                                    <?php if ($lo['order_status'] === 'pending' && ($lo['payment_status'] ?? '') !== 'paid'): ?>
                                        <button type="button" class="pos-action-btn-sm" style="background: var(--pos-accent); color: #fff;" onclick="quickConfirmPayment(<?= $lo['id'] ?>)">
                                            Terima Bayar
                                        </button>
                                        <button type="button" class="pos-action-btn-sm pos-action-btn-ready" onclick="quickUpdateStatus(<?= $lo['id'] ?>, 'processing')">
                                            Panggang
                                        </button>
                                    <?php elseif ($lo['order_status'] === 'pending' || $lo['order_status'] === 'confirmed'): ?>
                                        <button type="button" class="pos-action-btn-sm pos-action-btn-ready" onclick="quickUpdateStatus(<?= $lo['id'] ?>, 'processing')">
                                            Mulai Panggang
                                        </button>
                                    <?php elseif ($lo['order_status'] === 'processing'): ?>
                                        <button type="button" class="pos-action-btn-sm pos-action-btn-ready" onclick="quickUpdateStatus(<?= $lo['id'] ?>, 'ready')">
                                            Tandai Siap
                                        </button>
                                         <?php elseif ($lo['order_status'] === 'ready' || $lo['order_status'] === 'shelf'): ?>
                                        <button type="button" class="pos-action-btn-sm pos-action-btn-done" onclick="quickUpdateStatus(<?= $lo['id'] ?>, 'completed')">
                                            Serahkan & Selesai
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Backdrop for mobile cart bottom sheet -->
<div class="pos-mobile-cart-backdrop" id="mobile-cart-backdrop" onclick="closeMobileCart()"></div>

<!-- Floating Bar on Mobile -->
<div class="pos-mobile-bar d-lg-none" id="pos-mobile-bar" style="display: none;">
    <div>
        <div style="font-size: 0.75rem; color: var(--pos-text-sub);">Pesanan Kasir:</div>
        <strong style="font-size: 0.95rem; color: var(--pos-accent-dark);" id="mobile-bar-total">0 Item • Rp 0</strong>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openMobileCart()" style="border-radius: 12px; font-weight: 700; padding: 8px 16px;">
        Buka Kasir
    </button>
</div>

<!-- Receipt Confirmation Modal -->
<div class="pos-modal-backdrop" id="pos-receipt-modal">
    <div class="pos-modal-card">
        <div style="width: 52px; height: 52px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800;">Pesanan Berhasil Dibuat</h3>
        <p style="margin: 4px 0 14px; font-size: 0.85rem; color: var(--pos-text-sub);">Pesanan telah diteruskan ke dapur pemanggangan</p>

        <div style="background: #f9fafb; border-radius: 16px; padding: 16px; border: 1px dashed #e2e8f0; margin-bottom: 16px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700;">Nomor Antrean Kasir</div>
            <div class="pos-receipt-qnum" id="modal-queue-num">#A000</div>
            <div style="font-size: 0.9rem; font-weight: 700;" id="modal-cust-info">Pelanggan: -</div>
            <div style="font-size: 0.82rem; color: #64748b;" id="modal-total-info">Total: Rp 0</div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?= $baseUrl ?>/monitor.php" target="_blank" class="btn btn-secondary btn-block" style="border-radius: 12px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                TV Monitor
            </a>
            <button type="button" class="btn btn-primary btn-block" style="border-radius: 12px; font-size: 0.85rem; font-weight: 700;" onclick="closeReceiptModal()">
                Pesanan Baru
            </button>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════════════
// POS CASHIER DASHBOARD JAVASCRIPT LOGIC
// ═══════════════════════════════════════════════════════════════════

const BASE_URL = '<?= $baseUrl ?>';
let posCart = {}; // { productId: { id, name, price, quantity, img } }
let currentOrderType = 'takeaway';
let currentPaymentMethod = 'cash';
let activeCategory = 'all';

document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initCategories();
    initDrawerTrigger();
});

// Format Rupiah Helper
function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}

// Drawer Menu Trigger
function initDrawerTrigger() {
    const trigger = document.getElementById('pos-drawer-btn');
    const headerDrawerBtn = document.getElementById('tuku-drawer-toggle');
    if (trigger && headerDrawerBtn) {
        trigger.addEventListener('click', () => {
            headerDrawerBtn.click();
        });
    }
}

// Search Filter
function initSearch() {
    const searchInput = document.getElementById('pos-search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        filterProducts(query, activeCategory);
    });
}

// Categories Filter
function initCategories() {
    const catCards = document.querySelectorAll('.pos-cat-card');
    catCards.forEach(card => {
        card.addEventListener('click', () => {
            catCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            activeCategory = card.dataset.category || 'all';

            const searchQuery = (document.getElementById('pos-search-input')?.value || '').toLowerCase().trim();
            filterProducts(searchQuery, activeCategory);
        });
    });
}

function filterProducts(query, category) {
    const cards = document.querySelectorAll('.pos-product-card');
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

// Switch between Cart and Live Queue tabs
function switchPosTab(tab) {
    const btnCart = document.getElementById('tab-btn-cart');
    const btnQueue = document.getElementById('tab-btn-queue');
    const panelCart = document.getElementById('panel-cart-view');
    const panelQueue = document.getElementById('panel-queue-view');

    if (tab === 'cart') {
        btnCart.classList.add('active');
        btnQueue.classList.remove('active');
        panelCart.style.display = 'flex';
        panelQueue.style.display = 'none';
    } else {
        btnQueue.classList.add('active');
        btnCart.classList.remove('active');
        panelQueue.style.display = 'flex';
        panelCart.style.display = 'none';
    }
}

// Order Type Selection
function setOrderType(type) {
    currentOrderType = type;
    document.getElementById('btn-type-takeaway').classList.toggle('active', type === 'takeaway');
    document.getElementById('btn-type-dinein').classList.toggle('active', type === 'dine_in');
}

// Payment Method Selection
function setPaymentMethod(method) {
    currentPaymentMethod = method;
    document.getElementById('btn-pay-cash').classList.toggle('active', method === 'cash');
    document.getElementById('btn-pay-qris').classList.toggle('active', method === 'qris');
}

let lastPosClickTime = 0;
let lastPosClickId = null;

// Add Product to POS Cart
function handleProductClick(productId, e) {
    const evt = e || window.event;
    if (evt && evt.detail > 1) return;

    const now = Date.now();
    if (lastPosClickId === productId && (now - lastPosClickTime < 450)) return;
    lastPosClickTime = now;
    lastPosClickId = productId;

    const card = document.querySelector(`.pos-product-card[data-id="${productId}"]`);
    if (!card || card.classList.contains('disabled')) return;

    const id = parseInt(card.dataset.id);
    const name = card.dataset.name;
    const price = parseFloat(card.dataset.price);
    const img = card.dataset.img;
    const stock = parseInt(card.dataset.stock);

    if (!posCart[id]) {
        posCart[id] = { id, name, price, quantity: 1, img, stock };
    } else {
        if (posCart[id].quantity < stock) {
            posCart[id].quantity += 1;
        } else {
            if (typeof showToast === 'function') {
                showToast(`Stok ${name} tersisa ${stock} porsi`, 'warning');
            }
            return;
        }
    }

    renderPosCart();
}

// Change Quantity in Cart
function changeCartQty(productId, delta) {
    if (!posCart[productId]) return;

    posCart[productId].quantity += delta;
    if (posCart[productId].quantity <= 0) {
        delete posCart[productId];
    }

    renderPosCart();
}

// Reset Entire Cart
function clearPosCart() {
    posCart = {};
    renderPosCart();
}

// Render Cart DOM
function renderPosCart() {
    const list = document.getElementById('pos-cart-items-list');
    const emptyState = document.getElementById('pos-cart-empty-state');
    const checkoutBtn = document.getElementById('pos-checkout-btn');
    const subtotalEl = document.getElementById('pos-summary-subtotal');
    const totalEl = document.getElementById('pos-summary-total');
    const mobileCartBadge = document.getElementById('mobile-cart-badge');
    const mobileBarTotal = document.getElementById('mobile-bar-total');
    const mobileBar = document.getElementById('pos-mobile-bar');

    const items = Object.values(posCart);
    let totalItems = 0;
    let subtotal = 0;

    // Reset all product card badges
    document.querySelectorAll('.pos-product-card').forEach(card => {
        card.classList.remove('has-cart');
        const badge = card.querySelector('.pos-item-cart-count');
        if (badge) badge.style.display = 'none';
    });

    if (items.length === 0) {
        list.innerHTML = '';
        list.appendChild(emptyState);
        emptyState.style.display = 'block';
        checkoutBtn.disabled = true;
        subtotalEl.textContent = 'Rp 0';
        totalEl.textContent = 'Rp 0';
        if (mobileCartBadge) mobileCartBadge.textContent = '0';
        if (mobileBar) mobileBar.style.display = 'none';
        return;
    }

    emptyState.style.display = 'none';
    list.innerHTML = '';

    items.forEach(item => {
        totalItems += item.quantity;
        const itemSubtotal = item.price * item.quantity;
        subtotal += itemSubtotal;

        // Update product card badge in grid
        const card = document.querySelector(`.pos-product-card[data-id="${item.id}"]`);
        if (card) {
            card.classList.add('has-cart');
            const badge = card.querySelector('.pos-item-cart-count');
            if (badge) {
                badge.textContent = item.quantity;
                badge.style.display = 'flex';
            }
        }

        // Create Cart Item DOM
        const itemRow = document.createElement('div');
        itemRow.className = 'pos-cart-item';
        itemRow.innerHTML = `
            <img src="${item.img}" alt="${item.name}" class="pos-cart-item-img" onerror="this.src='${BASE_URL}/assets/img/salt_bread_plain.png'">
            <div class="pos-cart-item-info">
                <div class="pos-cart-item-name">${item.name}</div>
                <div class="pos-cart-item-price">${formatRupiah(item.price)}</div>
            </div>
            <div class="pos-qty-controls">
                <button type="button" class="pos-qty-btn" onclick="changeCartQty(${item.id}, -1)">&minus;</button>
                <span class="pos-qty-val">${item.quantity}</span>
                <button type="button" class="pos-qty-btn" onclick="changeCartQty(${item.id}, 1)">&plus;</button>
            </div>
        `;
        list.appendChild(itemRow);
    });

    subtotalEl.textContent = formatRupiah(subtotal);
    totalEl.textContent = formatRupiah(subtotal);
    checkoutBtn.disabled = false;

    if (mobileCartBadge) mobileCartBadge.textContent = totalItems;
    if (mobileBarTotal) mobileBarTotal.textContent = `${totalItems} Item • ${formatRupiah(subtotal)}`;
    if (mobileBar) mobileBar.style.display = 'flex';
}

// Mobile Cart Sheet Controls
function openMobileCart() {
    const panel = document.getElementById('pos-cart-panel');
    const backdrop = document.getElementById('mobile-cart-backdrop');
    if (panel) panel.classList.add('mobile-open');
    if (backdrop) backdrop.classList.add('active');
}

function closeMobileCart() {
    const panel = document.getElementById('pos-cart-panel');
    const backdrop = document.getElementById('mobile-cart-backdrop');
    if (panel) panel.classList.remove('mobile-open');
    if (backdrop) backdrop.classList.remove('active');
}

const mobileToggleBtn = document.getElementById('mobile-cart-toggle');
if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', () => {
        const panel = document.getElementById('pos-cart-panel');
        if (panel.classList.contains('mobile-open')) {
            closeMobileCart();
        } else {
            openMobileCart();
        }
    });
}

// Process POS Checkout via API
async function processPosCheckout() {
    const items = Object.values(posCart).map(it => ({
        product_id: it.id,
        quantity: it.quantity
    }));

    if (items.length === 0) return;

    const customerNameInput = document.getElementById('pos-customer-name');
    const customerName = (customerNameInput?.value || '').trim() || 'Pelanggan Kasir';
    const checkoutBtn = document.getElementById('pos-checkout-btn');

    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
        <span>Memproses...</span>
    `;

    try {
        const response = await fetch(`${BASE_URL}/api/create_order.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: items,
                customer_name: customerName,
                order_type: currentOrderType,
                payment_method: currentPaymentMethod,
                is_pos: true,
                source: 'pos'
            })
        });

        const result = await response.json();

        if (result.success) {
            const data = result.data || {};
            const queueNumber = data.queue_number || '#A001';
            const totalAmount = data.total_amount || 0;

            // Show Receipt Modal
            document.getElementById('modal-queue-num').textContent = queueNumber;
            document.getElementById('modal-cust-info').textContent = `Pelanggan: ${customerName} (${currentOrderType === 'dine_in' ? 'Dine-in' : 'Takeaway'})`;
            document.getElementById('modal-total-info').textContent = `Total: ${formatRupiah(totalAmount)} • Metode: ${currentPaymentMethod.toUpperCase()}`;
            document.getElementById('pos-receipt-modal').style.display = 'flex';

            // Clear Cart and Input
            clearPosCart();
            if (customerNameInput) customerNameInput.value = '';
            closeMobileCart();

            // Refresh queue counts
            refreshLiveQueue();
        } else {
            alert(result.message || 'Gagal memproses pesanan.');
        }
    } catch (err) {
        console.error('Checkout error:', err);
        alert('Terjadi kesalahan saat memproses pesanan POS.');
    } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>Checkout & Buat Pesanan</span>
        `;
    }
}

function closeReceiptModal() {
    document.getElementById('pos-receipt-modal').style.display = 'none';
}

// 1-Click Update Status from Live Queue
async function quickUpdateStatus(orderId, newStatus) {
    try {
        const response = await fetch(`${BASE_URL}/api/update_order_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId,
                new_status: newStatus
            })
        });
        const res = await response.json();
        if (res.success) {
            const card = document.getElementById(`q-card-${orderId}`);
            if (card) {
                if (newStatus === 'completed') {
                    card.remove();
                } else if (newStatus === 'ready') {
                    const badge = card.querySelector('.pos-status-badge');
                    if (badge) {
                        badge.className = 'pos-status-badge pos-status-ready';
                        badge.textContent = 'Siap';
                    }
                    const actionWrap = card.querySelector('.pos-queue-actions');
                    if (actionWrap) {
                        actionWrap.innerHTML = `
                            <button type="button" class="pos-action-btn-sm pos-action-btn-done" onclick="quickUpdateStatus(${orderId}, 'completed')">
                                Serahkan & Selesai
                            </button>
                        `;
                    }
                }
            }
            if (typeof showToast === 'function') {
                showToast(`Status pesanan diperbarui`, 'success');
            }
        }
    } catch (err) {
        console.error('Status update failed:', err);
    }
}

// Auto poll live queue count every 15 seconds
// 1-Click Confirm Payment from Live Queue
async function quickConfirmPayment(orderId) {
    try {
        const response = await fetch(`${BASE_URL}/api/update_order_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId,
                new_status: 'confirmed',
                payment_status: 'paid'
            })
        });
        const res = await response.json();
        if (res.success) {
            if (typeof showToast === 'function') {
                showToast('Pembayaran berhasil dikonfirmasi!', 'success');
            }
            refreshLiveQueue();
        } else {
            if (typeof showToast === 'function') {
                showToast(res.message || 'Gagal konfirmasi pembayaran', 'error');
            }
        }
    } catch (err) {
        console.error('Confirm payment failed:', err);
    }
}

// Auto poll live queue every 4 seconds and dynamically re-render
async function refreshLiveQueue() {
    try {
        const res = await fetch(`${BASE_URL}/api/get_orders.php?type=queue`);
        const json = await res.json();
        if (json && json.success && json.data) {
            const orders = json.data.orders || [];
            const totalCount = json.data.counts ? json.data.counts.total : orders.length;
            const qEl = document.getElementById('stat-active-queue');
            const bEl = document.getElementById('queue-badge-count');
            if (qEl) qEl.textContent = totalCount;
            if (bEl) bEl.textContent = totalCount;

            const listEl = document.getElementById('pos-live-queue-list');
            if (listEl) {
                if (orders.length === 0) {
                    listEl.innerHTML = '<div class="pos-cart-empty">Tidak ada antrean pesanan aktif saat ini.</div>';
                } else {
                    listEl.innerHTML = orders.map(lo => {
                        let stLabel = 'Menunggu Bayar';
                        if (lo.order_status === 'processing') stLabel = 'Dipanggang';
                        else if (lo.order_status === 'ready') stLabel = 'Siap';
                        else if (lo.order_status === 'shelf') stLabel = 'Rak Mandiri';
                        else if (lo.order_status === 'confirmed') stLabel = 'Dikonfirmasi';
                        else if (lo.payment_status === 'paid') stLabel = 'Dibayar';

                        let actionBtn = '';
                        if (lo.payment_status !== 'paid') {
                            actionBtn = `
                                <button type="button" class="pos-action-btn-sm" style="background: #10b981; color: #fff;" onclick="quickConfirmPayment(${lo.id})">
                                    Konfirmasi Bayar
                                </button>
                            `;
                        } else if (lo.order_status === 'pending' || lo.order_status === 'confirmed') {
                            actionBtn = `
                                <button type="button" class="pos-action-btn-sm pos-action-btn-ready" onclick="quickUpdateStatus(${lo.id}, 'processing')">
                                    Mulai Panggang
                                </button>
                            `;
                        } else if (lo.order_status === 'processing') {
                            actionBtn = `
                                <button type="button" class="pos-action-btn-sm pos-action-btn-ready" onclick="quickUpdateStatus(${lo.id}, 'ready')">
                                    Tandai Siap
                                </button>
                            `;
                        } else if (lo.order_status === 'ready' || lo.order_status === 'shelf') {
                            actionBtn = `
                                <button type="button" class="pos-action-btn-sm pos-action-btn-done" onclick="quickUpdateStatus(${lo.id}, 'completed')">
                                    Serahkan & Selesai
                                </button>
                            `;
                        }

                        const items = (lo.items || []).map(it => `${it.quantity}x ${it.name || it.item_name}`).join(', ');
                        const custName = lo.customer_name || 'Pelanggan';
                        const oType = lo.order_type === 'dine_in' ? 'Dine-in' : 'Takeaway';

                        return `
                            <div class="pos-queue-card" id="q-card-${lo.id}">
                                <div class="pos-queue-top">
                                    <span class="pos-queue-badge">${lo.queue_number}</span>
                                    <span class="pos-status-badge pos-status-${lo.order_status}">
                                        ${stLabel}
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; font-weight: 700;">
                                    ${custName} • <span style="font-weight: 400; text-transform: capitalize;">${oType}</span>
                                </div>
                                <div class="pos-queue-items">${items || 'Menu Salt Bread'}</div>
                                <div class="pos-queue-actions">${actionBtn}</div>
                            </div>
                        `;
                    }).join('');
                }
            }
        }
    } catch (e) {}
}
setInterval(refreshLiveQueue, 4000);
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
