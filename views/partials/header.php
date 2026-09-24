<?php
/**
 * Header Partial — HTML Head & Navigation
 * Little Salt Bread Blok M — POS System
 * 
 * Usage: require_once __DIR__ . '/header.php';
 * Variables available: $pageTitle, $pageDescription, $extraCss, $bodyClass
 */

require_once __DIR__ . '/../../config/session.php';
initSession();

$pageTitle = ($pageTitle ?? 'Little Salt Bread') . ' | Salt Bread Blok M';
$pageDescription = $pageDescription ?? 'Pesan Salt Bread renyah gurih favorit Anda langsung dari HP. Little Salt Bread Blok M.';
$extraCss = $extraCss ?? '';
$bodyClass = $bodyClass ?? '';
$hideNavbar = !empty($hideNavbar);
$hideFooter = !empty($hideFooter);
$currentUser = getCurrentUser();

if (!isset($baseUrl) || $baseUrl === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
        $baseUrl = rtrim($m[1], '/');
    } else {
        $baseUrl = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="theme-color" content="#d97706">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="<?= generateCsrfToken() ?>">
    
    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/img/icon-32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/assets/img/icon-192.png">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Anti-FOUC Theme Script (inline, runs before render) -->
    <script>
    (function(){
        var t = localStorage.getItem('sb_theme');
        if (!t) t = window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
        var m = document.querySelector('meta[name="theme-color"]');
        if (m) m.content = t === 'dark' ? '#0f172a' : '#d97706';
    })();
    </script>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css?v=3.2.5">
    <?php if ($extraCss): ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= $extraCss ?>?v=3.2.0">
    <?php endif; ?>

    <!-- Mobile UI, Typography & Overflow Safety (inline = immediate update) -->
    <style>
    <?php if ($hideNavbar): ?>
    body {
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      background: var(--bg-primary, #ffffff);
    }
    .main-content {
      width: 100%;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    <?php else: ?>
    body {
      padding-top: 68px;
    }
    @media (max-width: 639px) {
      body {
        padding-top: 52px;
      }
    }
    <?php endif; ?>
    @media (max-width: 768px) {
      html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
      }
      body {
        padding-bottom: <?= $hideNavbar ? '0 !important' : '90px' ?>;
      }
      .container {
        padding-left: 12px !important;
        padding-right: 12px !important;
        box-sizing: border-box !important;
      }
      /* Responsive typography for mobile */
      h1, .hero-heading {
        font-size: 1.9rem !important;
        line-height: 1.22 !important;
      }
      h2, .usp-heading, .featured-heading, .cta-banner-heading {
        font-size: 1.6rem !important;
        line-height: 1.28 !important;
      }
      h3 {
        font-size: 1.25rem !important;
      }
      p, .hero-desc {
        font-size: 0.92rem !important;
        line-height: 1.5 !important;
      }
      img {
        max-width: 100% !important;
      }
      /* Universal list reset for mobile */
      ul, ol {
        list-style: none !important;
      }
      /* Footer 3-column mobile layout: Kiri, Tengah, Kanan */
      .site-footer {
        padding: 24px 0 88px !important;
        font-size: 0.76rem !important;
      }
      .footer-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr 1.15fr !important;
        gap: 12px 6px !important;
      }
      .footer-col-brand {
        grid-column: 1 / -1 !important;
        padding-bottom: 12px !important;
        margin-bottom: 4px !important;
        border-bottom: 1px solid var(--border) !important;
      }
      .footer-meta-wrap {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
      }
      .footer-meta-pill {
        padding: 3px 8px !important;
        font-size: 0.68rem !important;
      }
      .footer-heading {
        font-size: 0.76rem !important;
        margin-bottom: 8px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
      }
      .footer-links,
      ul.footer-links {
        list-style: none !important;
        list-style-type: none !important;
        padding: 0 !important;
        margin: 0 !important;
        padding-left: 0 !important;
        margin-left: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
      }
      .footer-links li {
        list-style: none !important;
        list-style-type: none !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .footer-links li a {
        font-size: 0.70rem !important;
        line-height: 1.35 !important;
        display: block !important;
        text-decoration: none !important;
        color: var(--text-secondary) !important;
        white-space: nowrap !important;
      }
      .footer-links li a:hover {
        color: var(--primary) !important;
      }
      .footer-contact-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 7px !important;
      }
      .footer-contact-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 4px !important;
        font-size: 0.67rem !important;
        line-height: 1.25 !important;
      }
      .footer-contact-item svg {
        width: 13px !important;
        height: 13px !important;
        min-width: 13px !important;
        max-width: 13px !important;
        min-height: 13px !important;
        max-height: 13px !important;
        flex-shrink: 0 !important;
        margin-top: 2px !important;
        color: var(--primary) !important;
      }
      .footer-meta-pill svg {
        width: 14px !important;
        height: 14px !important;
        min-width: 14px !important;
        max-width: 14px !important;
        flex-shrink: 0 !important;
      }
      .footer-social-row {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        margin-top: 6px !important;
      }
      .footer-social-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        padding: 3px 4px !important;
        background: var(--bg-primary) !important;
        border: 1px solid var(--border) !important;
        border-radius: 4px !important;
        color: var(--text-secondary) !important;
        font-size: 0.64rem !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
      }
      .footer-social-btn svg {
        width: 12px !important;
        height: 12px !important;
        min-width: 12px !important;
        max-width: 12px !important;
        flex-shrink: 0 !important;
        color: var(--primary) !important;
      }
      .footer-bottom {
        font-size: 0.66rem !important;
        padding-top: 14px !important;
        margin-top: 12px !important;
        text-align: center !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        align-items: center !important;
        border-top: 1px solid var(--border) !important;
      }

      /* 3-Column Mobile Menu / Catalog Grid (Kiri, Tengah, Kanan) */
      .product-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 10px 8px !important;
      }
      .product-card {
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid var(--border) !important;
        background: var(--bg-card) !important;
      }
      .product-image-wrap {
        height: 105px !important;
        position: relative !important;
        overflow: hidden !important;
      }
      .product-image-wrap img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
      }
      .product-image-wrap .stock-badge {
        top: 4px !important;
        right: 4px !important;
        font-size: 0.52rem !important;
        padding: 2px 5px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
      }
      .product-card .card-body {
        padding: 7px 5px !important;
        display: flex !important;
        flex-direction: column !important;
        flex-grow: 1 !important;
      }
      .product-card .card-body > span:first-child {
        font-size: 0.58rem !important;
        letter-spacing: 0.3px !important;
        margin-bottom: 2px !important;
        display: block !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
      }
      .product-card .card-body h3 {
        font-size: 0.78rem !important;
        line-height: 1.2 !important;
        margin: 0 0 4px !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        height: 2.4em !important;
      }
      .product-card .card-body p {
        display: none !important;
      }
      .product-card .card-body > div:last-child {
        margin-top: auto !important;
        padding-top: 5px !important;
        border-top: 1px solid var(--border) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
        align-items: stretch !important;
      }
      .product-card .card-body > div:last-child span {
        font-size: 0.76rem !important;
        font-weight: 800 !important;
        text-align: center !important;
        display: block !important;
      }
      .product-card .card-body .btn-order,
      .product-card .card-body button {
        width: 100% !important;
        padding: 5px 2px !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        border-radius: 6px !important;
        text-align: center !important;
        justify-content: center !important;
        min-height: 26px !important;
        line-height: 1 !important;
      }
      /* Category filter tabs on mobile */
      .menu-filter .tab,
      .tab-pill {
        padding: 0.35rem 0.9rem !important;
        font-size: 0.76rem !important;
      }
      /* Floating Cart Action Bar (Customer Mobile) — floats cleanly above bottom navigation */
      #floating-cart,
      .floating-cart-bar {
        bottom: calc(92px + var(--safe-bottom, 0px)) !important;
        width: calc(100% - 32px) !important;
        max-width: 380px !important;
        z-index: 950 !important;
      }
      /* Landing Page Featured Showcase — 3-Column Grid on Mobile */
      .featured-section {
        padding: 32px 0 !important;
      }
      .featured-header {
        margin-bottom: 16px !important;
        gap: 10px !important;
        align-items: center !important;
      }
      .featured-heading {
        font-size: 1.4rem !important;
        line-height: 1.2 !important;
      }
      .featured-header .btn {
        padding: 5px 12px !important;
        font-size: 0.76rem !important;
      }
      .featured-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 10px 8px !important;
      }
      .featured-card {
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid var(--border) !important;
        background: var(--bg-card) !important;
      }
      .featured-img-wrap {
        height: 105px !important;
        position: relative !important;
        overflow: hidden !important;
      }
      .featured-img-wrap img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
      }
      .featured-body {
        padding: 7px 5px !important;
        display: flex !important;
        flex-direction: column !important;
        flex-grow: 1 !important;
      }
      .featured-title {
        font-size: 0.78rem !important;
        line-height: 1.2 !important;
        margin: 0 0 4px !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        height: 2.4em !important;
      }
      .featured-desc {
        display: none !important;
      }
      .featured-footer {
        margin-top: auto !important;
        padding-top: 5px !important;
        border-top: 1px solid var(--border) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
        align-items: stretch !important;
      }
      .featured-price {
        font-size: 0.76rem !important;
        font-weight: 800 !important;
        text-align: center !important;
        display: block !important;
      }
      .featured-btn {
        width: 100% !important;
        padding: 5px 2px !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        border-radius: 6px !important;
        text-align: center !important;
        justify-content: center !important;
        min-height: 26px !important;
        line-height: 1 !important;
      }
      /* Promo Banner Mobile */
      .promo-banner-container {
        margin-bottom: 16px !important;
        border-radius: 14px !important;
      }
      .promo-banner-slider {
        height: 142px !important;
      }
      .promo-banner-slide {
        padding: 14px 14px !important;
      }
      .promo-banner-tag {
        font-size: 0.62rem !important;
        letter-spacing: 0.8px !important;
        gap: 4px !important;
      }
      .promo-dot {
        width: 5px !important;
        height: 5px !important;
      }
      .promo-banner-title {
        font-size: 1.15rem !important;
        line-height: 1.15 !important;
      }
      .promo-banner-text {
        font-size: 0.72rem !important;
        line-height: 1.25 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        margin: 0 !important;
      }
      .promo-banner-badges {
        display: flex !important;
        gap: 4px !important;
        margin-top: 2px !important;
      }
      .promo-pill {
        font-size: 0.60rem !important;
        padding: 1px 6px !important;
      }
      .promo-banner-dots {
        bottom: 8px !important;
        right: 12px !important;
        gap: 4px !important;
      }
      .promo-dot-btn {
        width: 6px !important;
        height: 6px !important;
      }
      .promo-dot-btn.active {
        width: 16px !important;
      }
      /* Instagram Showcase Mobile */
      .insta-showcase-section {
        padding: 32px 0 !important;
      }
      .insta-heading {
        font-size: 1.4rem !important;
        line-height: 1.2 !important;
      }
      .insta-header .btn {
        padding: 6px 12px !important;
        font-size: 0.78rem !important;
      }
      .insta-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
      }
      .insta-card {
        height: 135px !important;
        border-radius: 12px !important;
      }
      .insta-overlay {
        padding: 8px !important;
      }
      .insta-tag {
        font-size: 0.58rem !important;
      }
      .insta-caption {
        font-size: 0.72rem !important;
        line-height: 1.2 !important;
      }
      /* Transparent Glass Banner on Mobile */
      .hero-glass-banner {
        bottom: 8px !important;
        left: 8px !important;
        right: 8px !important;
        padding: 8px 12px !important;
        border-radius: 12px !important;
        gap: 2px !important;
      }
      .glass-tag {
        font-size: 0.58rem !important;
        letter-spacing: 0.6px !important;
      }
      .glass-dot {
        width: 5px !important;
        height: 5px !important;
      }
      .glass-banner-title {
        font-size: 1.05rem !important;
        line-height: 1.15 !important;
      }
      .glass-banner-desc {
        font-size: 0.68rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
      }
      /* Bottom Banner on Mobile */
      .menu-bottom-banner {
        padding: 20px 16px !important;
        margin-top: 24px !important;
        margin-bottom: 24px !important;
        border-radius: 14px !important;
      }
      .menu-bottom-banner h3 {
        font-size: 1.35rem !important;
      }
      .menu-bottom-banner p {
        font-size: 0.8rem !important;
        margin-bottom: 14px !important;
      }
      .menu-bottom-banner .btn {
        font-size: 0.76rem !important;
        padding: 6px 14px !important;
      }
    }
    @media (max-width: 639px) {
      .navbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        z-index: 1000 !important;
        overflow: hidden;
      }
      .navbar-inner { height: 52px; gap: 4px; overflow: hidden; }
      /* Brand: hide text, keep SB icon only */
      .navbar-brand .brand-text { display: none !important; }
      .navbar-brand .brand-icon { width: 30px; height: 30px; font-size: 0.8rem; flex-shrink: 0; }
      /* Action buttons: icon-only */
      .navbar-actions { gap: 4px; flex-shrink: 0; }
      .theme-toggle > span,
      .cart-btn > span:not(.cart-badge),
      .tuku-menu-btn > span:not(.tuku-menu-icon) { display: none !important; }
      .theme-toggle, .cart-btn, .tuku-menu-btn {
        padding: 6px 8px !important;
        gap: 0 !important;
        min-width: 32px;
        justify-content: center;
      }
    }
    body.admin-page .cart-btn,
    .admin-layout .cart-btn,
    body[class*="admin"] .cart-btn {
      display: none !important;
    }
    /* Toast Notifications — Compact Pill at Top (Never blocks floating cart) */
    .toast-container {
      position: fixed !important;
      top: calc(14px + var(--safe-top, 0px)) !important;
      left: 50% !important;
      transform: translateX(-50%) !important;
      bottom: auto !important;
      right: auto !important;
      z-index: 99999 !important;
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      gap: 6px !important;
      pointer-events: none !important;
      width: auto !important;
      max-width: calc(100vw - 28px) !important;
    }
    .toast {
      padding: 6px 12px !important;
      border-radius: 9999px !important;
      color: #f8fafc !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 7px !important;
      background: #0f172a !important;
      border: 1px solid rgba(255, 255, 255, 0.18) !important;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28) !important;
      min-width: 0 !important;
      max-width: 90vw !important;
      font-size: 0.80rem !important;
      font-weight: 600 !important;
      pointer-events: auto !important;
      box-sizing: border-box !important;
      animation: toastDropIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
      transition: all 0.25s ease !important;
      line-height: 1.2 !important;
    }
    .toast-icon {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 18px !important;
      height: 18px !important;
      border-radius: 50% !important;
      flex-shrink: 0 !important;
      color: #ffffff !important;
    }
    .toast-success {
      background: #0f172a !important;
      border-color: rgba(16, 185, 129, 0.6) !important;
    }
    .toast-success .toast-icon {
      background: #10b981 !important;
    }
    .toast-error {
      background: #0f172a !important;
      border-color: rgba(239, 68, 68, 0.6) !important;
    }
    .toast-error .toast-icon {
      background: #ef4444 !important;
    }
    .toast-warning {
      background: #0f172a !important;
      border-color: rgba(245, 158, 11, 0.6) !important;
    }
    .toast-warning .toast-icon {
      background: #f59e0b !important;
    }
    .toast-info {
      background: #0f172a !important;
      border-color: rgba(59, 130, 246, 0.6) !important;
    }
    .toast-info .toast-icon {
      background: #3b82f6 !important;
    }
    .toast-message {
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      max-width: 250px !important;
      font-size: 0.80rem !important;
    }
    .toast-close {
      background: transparent !important;
      border: none !important;
      color: rgba(255, 255, 255, 0.6) !important;
      font-size: 1.05rem !important;
      line-height: 1 !important;
      cursor: pointer !important;
      padding: 0 2px 0 4px !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    .toast-close:hover {
      color: #ffffff !important;
    }
    .toast.toast-hide {
      animation: toastFadeUp 0.22s ease forwards !important;
    }
    @keyframes toastDropIn {
      from { opacity: 0; transform: translateY(-14px) scale(0.95); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes toastFadeUp {
      from { opacity: 1; transform: translateY(0) scale(1); }
      to { opacity: 0; transform: translateY(-10px) scale(0.96); }
    }
    /* Transparent Glass Banner (Hero Image Overlay) */
    .hero-glass-banner {
      position: absolute;
      bottom: 14px;
      left: 14px;
      right: 14px;
      padding: 12px 16px;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.22);
      border-radius: var(--radius-lg, 16px);
      color: #ffffff;
      text-align: left;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
      gap: 3px;
      transition: all 0.3s ease;
      pointer-events: none;
    }
    .glass-banner-top {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .glass-dot {
      width: 6px;
      height: 6px;
      background: #fbbf24;
      border-radius: 50%;
      display: inline-block;
      box-shadow: 0 0 8px #fbbf24;
    }
    .glass-tag {
      font-size: 0.68rem;
      font-weight: 800;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #fbbf24;
    }
    .glass-banner-title {
      font-family: var(--font-display, 'Patrick Hand', cursive);
      font-size: 1.4rem;
      font-weight: 700;
      color: #ffffff;
      line-height: 1.15;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    }
    .glass-banner-desc {
      font-size: 0.78rem;
      color: rgba(255, 255, 255, 0.9);
      margin: 0;
      line-height: 1.3;
    }
    /* Promo Banner Slider — Universal Guaranteed-Visible & Cache-Proof */
    .promo-banner-container {
      position: relative !important;
      width: 100% !important;
      border-radius: var(--radius-lg, 16px) !important;
      overflow: hidden !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
      margin-bottom: 22px !important;
      background: #1f1a17 !important;
      display: block !important;
    }
    .promo-banner-slider {
      position: relative !important;
      width: 100% !important;
      height: 185px !important;
      overflow: hidden !important;
      display: block !important;
    }
    .promo-banner-slide {
      position: absolute !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      background-size: cover !important;
      background-position: center !important;
      opacity: 0 !important;
      visibility: hidden !important;
      transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out !important;
      display: flex !important;
      align-items: center !important;
      padding: 24px 30px !important;
      box-sizing: border-box !important;
      z-index: 1 !important;
    }
    .promo-banner-slide.active {
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 2 !important;
    }
    .promo-banner-content {
      max-width: 540px !important;
      color: #ffffff !important;
      z-index: 3 !important;
      display: flex !important;
      flex-direction: column !important;
      gap: 6px !important;
    }
    .promo-banner-tag {
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      font-size: 0.72rem !important;
      font-weight: 800 !important;
      letter-spacing: 1.2px !important;
      text-transform: uppercase !important;
      color: #fbbf24 !important;
    }
    .promo-dot {
      width: 6px !important;
      height: 6px !important;
      border-radius: 50% !important;
      background: #fbbf24 !important;
      display: inline-block !important;
      box-shadow: 0 0 6px #fbbf24 !important;
      flex-shrink: 0 !important;
    }
    .promo-banner-title {
      font-family: var(--font-display, 'Patrick Hand', cursive) !important;
      font-size: 1.65rem !important;
      font-weight: 700 !important;
      color: #ffffff !important;
      margin: 0 !important;
      line-height: 1.15 !important;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5) !important;
    }
    .promo-banner-text {
      font-size: 0.85rem !important;
      color: rgba(255, 255, 255, 0.92) !important;
      margin: 0 !important;
      line-height: 1.35 !important;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4) !important;
    }
    .promo-banner-badges {
      display: flex !important;
      align-items: center !important;
      gap: 6px !important;
      margin-top: 4px !important;
      flex-wrap: wrap !important;
    }
    .promo-pill {
      display: inline-block !important;
      font-size: 0.68rem !important;
      font-weight: 700 !important;
      padding: 2px 9px !important;
      border-radius: 999px !important;
      background: rgba(255, 255, 255, 0.16) !important;
      backdrop-filter: blur(8px) !important;
      -webkit-backdrop-filter: blur(8px) !important;
      border: 1px solid rgba(255, 255, 255, 0.28) !important;
      color: #ffffff !important;
      white-space: nowrap !important;
    }
    .promo-banner-dots {
      position: absolute !important;
      bottom: 12px !important;
      right: 18px !important;
      display: flex !important;
      gap: 6px !important;
      z-index: 4 !important;
    }
    .promo-dot-btn {
      width: 8px !important;
      height: 8px !important;
      border-radius: 999px !important;
      background: rgba(255, 255, 255, 0.4) !important;
      border: none !important;
      padding: 0 !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
    }
    .promo-dot-btn.active {
      width: 22px !important;
      background: var(--primary, #d97706) !important;
      box-shadow: none !important;
    }
    </style>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    
    <?php if (empty($hideNavbar)): ?>
    <!-- Splash Screen -->
    <div class="splash-screen" id="splash">
        <div class="splash-content">
            <div class="splash-brand">
                <span class="brand-light">little</span>
                <span class="brand-bold">SALT BREAD</span>
            </div>
            <div class="splash-spinner"></div>
        </div>
    </div>
    <script>
    (function(){
        function hideSplashNow() {
            var s = document.getElementById('splash');
            if (s) {
                s.style.transition = 'opacity 0.35s ease';
                s.style.opacity = '0';
                s.style.pointerEvents = 'none';
                setTimeout(function(){ if (s && s.parentNode) s.parentNode.removeChild(s); }, 350);
            }
        }
        window.addEventListener('load', function(){ setTimeout(hideSplashNow, 200); });
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(hideSplashNow, 300); });
        setTimeout(hideSplashNow, 600);
    })();
    </script>

    <!-- Navigation Bar (Tuku Minimalist Style) -->
    <nav class="navbar" id="navbar">
        <div class="container navbar-inner">
            <!-- Brand -->
            <a href="<?= $baseUrl ?>/<?= isAdmin() ? 'views/admin/dashboard.php' : '' ?>" class="navbar-brand">
                <span class="brand-icon">SB</span>
                <span class="brand-text">
                    <span class="brand-light">little</span>
                    <span class="brand-bold">SALT BREAD</span>
                </span>
            </a>
            
            <!-- Desktop Nav Links -->
            <div class="nav-menu" id="nav-menu">
                <?php if (isAdmin()): ?>
                    <a href="<?= $baseUrl ?>/views/admin/dashboard.php" class="nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'admin/dashboard') ? 'active' : '' ?>">Dashboard</a>
                    <a href="<?= $baseUrl ?>/views/admin/orders.php" class="nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'admin/orders') ? 'active' : '' ?>">Live POS Antrean</a>
                    <a href="<?= $baseUrl ?>/views/admin/menu.php" class="nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'admin/menu') ? 'active' : '' ?>">Kelola Menu</a>
                    <a href="<?= $baseUrl ?>/views/admin/reports.php" class="nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'admin/reports') ? 'active' : '' ?>">Laporan</a>
                    <a href="<?= $baseUrl ?>/monitor.php" target="_blank" class="nav-link">Monitor TV</a>
                    <script>
                        localStorage.removeItem('sb_cart');
                    </script>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/" class="nav-link <?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' && !strpos($_SERVER['SCRIPT_NAME'], 'views') ? 'active' : '' ?>">Beranda</a>
                    <a href="<?= $baseUrl ?>/views/customer/index.php" class="nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'customer/index') ? 'active' : '' ?>">Menu</a>
                <?php endif; ?>
                
                <?php if ($currentUser): ?>
                    <div class="nav-user-menu">
                        <button class="nav-user-btn" id="user-menu-btn" aria-haspopup="true" aria-expanded="false">
                            <span class="user-avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></span>
                            <span class="user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
                        </button>
                        <div class="user-dropdown" id="user-dropdown">
                            <?php if (isAdmin()): ?>
                            <a href="<?= $baseUrl ?>/views/admin/dashboard.php" class="dropdown-item">Dashboard POS</a>
                            <a href="<?= $baseUrl ?>/views/admin/orders.php" class="dropdown-item">Live Antrean Dapur</a>
                            <a href="<?= $baseUrl ?>/views/customer/index.php" class="dropdown-item">Pratinjau Web Menu</a>
                            <hr class="dropdown-divider">
                            <?php else: ?>
                            <a href="<?= $baseUrl ?>/views/customer/profile.php" class="dropdown-item">Profil Saya</a>
                            <a href="<?= $baseUrl ?>/views/customer/history.php" class="dropdown-item">Riwayat Pesanan</a>
                            <hr class="dropdown-divider">
                            <?php endif; ?>
                            <a href="#" class="dropdown-item" onclick="handleLogout(event)">Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/views/auth/login.php" class="nav-link">Masuk</a>
                <?php endif; ?>
            </div>
            
            <!-- Right Actions -->
            <div class="navbar-actions">
                <!-- Theme Toggle -->
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle tema" title="Mode Gelap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                </button>
                
                <!-- Cart Button (customer only) -->
                <?php if (!isAdmin() && strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') === false): ?>
                <a href="<?= $baseUrl ?>/views/customer/checkout.php" class="cart-btn" aria-label="Keranjang Belanja">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    <span>Keranjang</span>
                    <span class="cart-badge" style="display:none;">0</span>
                </a>
                <?php endif; ?>
                
                <!-- Tuku-Style Sidebar Menu Trigger Button -->
                <button class="tuku-menu-btn" id="tuku-drawer-toggle" aria-label="Buka Menu Sidebar" aria-controls="tuku-sidebar-drawer" aria-expanded="false">
                    <span class="tuku-menu-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                    </span>
                    <span>Menu</span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- TUKU-STYLE SLIDE-OUT NAVIGATION DRAWER -->
    <div class="tuku-drawer-overlay" id="tuku-drawer-overlay" aria-hidden="true"></div>
    <aside class="tuku-drawer" id="tuku-sidebar-drawer" role="dialog" aria-modal="true" aria-label="Menu Navigasi Utama" aria-hidden="true">
        <!-- Drawer Header -->
        <div class="tuku-drawer-header">
            <a href="<?= $baseUrl ?>/" class="navbar-brand" style="margin: 0;">
                <span class="brand-icon" style="width: 32px; height: 32px; font-size: 0.95rem;">SB</span>
                <span class="brand-text">
                    <span class="brand-light" style="font-size: 0.85rem;">little</span>
                    <span class="brand-bold" style="font-size: 1.15rem;">SALT BREAD</span>
                </span>
            </a>
            <button class="tuku-drawer-close" id="tuku-drawer-close" aria-label="Tutup Menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <!-- Drawer Body -->
        <div class="tuku-drawer-body">
            <!-- Section 1: Katalog & Produk -->
            <div class="drawer-section">
                <span class="drawer-section-title">Menu & Navigasi</span>
                <div class="drawer-nav-list">
                    <a href="<?= $baseUrl ?>/index.php" class="drawer-nav-link <?= (!empty($isBeranda) || (strpos($_SERVER['SCRIPT_NAME'], 'customer') === false && preg_match('#/(index\.php)?$#i', $_SERVER['SCRIPT_NAME']))) ? 'active' : '' ?>">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <span>Beranda</span>
                        </span>
                    </a>
                    <a href="<?= $baseUrl ?>/views/customer/index.php" class="drawer-nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'customer/index') ? 'active' : '' ?>">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>Semua Menu Salt Bread</span>
                        </span>
                        <span class="drawer-badge">11 Menu</span>
                    </a>
                    <a href="<?= $baseUrl ?>/views/customer/index.php?cat=classic" class="drawer-nav-link">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                            <span>Salt Bread Klasik</span>
                        </span>
                    </a>
                    <a href="<?= $baseUrl ?>/views/customer/index.php?cat=savory" class="drawer-nav-link">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <span>Truffle & Garlic Butter</span>
                        </span>
                    </a>
                    <a href="<?= $baseUrl ?>/views/customer/index.php?cat=sweet" class="drawer-nav-link">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <span>Sweet & Cheese Series</span>
                        </span>
                    </a>
                </div>
            </div>
            
            <!-- Section 2: Pemesanan & Antrean -->
            <div class="drawer-section">
                <span class="drawer-section-title">Pemesanan</span>
                <div class="drawer-nav-list">
                    <?php if (!isAdmin()): ?>
                    <a href="<?= $baseUrl ?>/views/customer/checkout.php" class="drawer-nav-link <?= strpos($_SERVER['SCRIPT_NAME'], 'checkout') ? 'active' : '' ?>">
                        <span class="drawer-nav-link-left">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            <span>Keranjang & Checkout</span>
                        </span>
                        <span class="cart-badge drawer-cart-badge" style="display:none; position: static;">0</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Section 3: Outlet Tetangga Blok M (Khas Tuku) -->
            <div class="drawer-section">
                <span class="drawer-section-title">Outlet Kami</span>
                <div class="drawer-store-card">
                    <div class="drawer-store-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>Little Salt Bread Blok M</span>
                    </div>
                    <div class="drawer-store-desc">
                        Jl. Melawai Raya No. 12, Kebayoran Baru, Jakarta Selatan (Dekat Stasiun MRT Blok M).
                    </div>
                    <div class="drawer-store-hours">
                        Buka Setiap Hari: 08.00 - 21.00 WIB
                    </div>
                    <a href="https://wa.me/6281200000001" target="_blank" rel="noopener noreferrer" class="drawer-store-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>Hubungi WhatsApp Outlet</span>
                    </a>
                </div>
            </div>
            
            <!-- Section 4: Akun & Akses -->
            <div class="drawer-section">
                <span class="drawer-section-title">Akun & Layanan</span>
                <div class="drawer-nav-list">
                    <?php if ($currentUser): ?>
                        <div style="padding: 12px 14px; background: rgba(217, 119, 6, 0.08); border-radius: var(--radius-sm); margin-bottom: 8px;">
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;"><?= htmlspecialchars($currentUser['name']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= htmlspecialchars($currentUser['email']) ?></div>
                        </div>
                        <?php if (isAdmin()): ?>
                        <a href="<?= $baseUrl ?>/views/admin/dashboard.php" class="drawer-nav-link">
                            <span class="drawer-nav-link-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                <span>Dashboard Kasir / Admin</span>
                            </span>
                        </a>
                        <?php else: ?>
                        <a href="<?= $baseUrl ?>/views/customer/profile.php" class="drawer-nav-link">
                            <span class="drawer-nav-link-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <span>Profil Saya</span>
                            </span>
                        </a>
                        <?php endif; ?>
                        <a href="#" class="drawer-nav-link" onclick="handleLogout(event)" style="color: var(--error);">
                            <span class="drawer-nav-link-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span>Keluar dari Akun</span>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="<?= $baseUrl ?>/views/auth/login.php" class="drawer-nav-link">
                            <span class="drawer-nav-link-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                <span>Masuk ke Akun</span>
                            </span>
                        </a>
                        <a href="<?= $baseUrl ?>/views/auth/register.php" class="drawer-nav-link" style="color: var(--primary); font-weight: 700;">
                            <span class="drawer-nav-link-left">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                <span>Daftar Member Baru</span>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Drawer Footer -->
        <div class="tuku-drawer-footer">
            <span>PWA v1.0.4 Online</span>
            <span>Little Salt Bread Blok M</span>
        </div>
    </aside>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?= renderFlash() ?>
    
    <!-- Main Content Wrapper -->
    <main class="main-content" id="main-content">

