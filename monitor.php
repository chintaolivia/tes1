<?php
/**
 * Outlet TV Monitor Display
 * Little Salt Bread Blok M — POS System
 * 
 * High-contrast, brand-aligned 4-column display for TV screens in store.
 * Columns: Sedang Dipanggang | Sedang Dikemas | Siap Diambil (Counter) | Rak Mandiri (>20 Mnt)
 * Aesthetics: Warm Artisan Bakery branding matching Little Salt Bread website.
 */

require_once __DIR__ . '/config/database.php';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
    $baseUrl = rtrim($m[1], '/');
} else {
    $baseUrl = '';
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Monitor Antrean Outlet — Little Salt Bread Blok M</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Warm Espresso Bakery Palette (Default Dark TV Mode) */
            --mon-bg: #14110e;
            --mon-surface: #1e1915;
            --mon-surface-card: #27211b;
            --mon-surface-hover: #312a23;
            --mon-border: #3d342a;
            --mon-border-subtle: #2d261e;
            --mon-text: #faf5ee;
            --mon-text-secondary: #c7bdaf;
            --mon-text-muted: #8c8275;
            
            --mon-primary: #d97706;
            --mon-primary-glow: rgba(217, 119, 6, 0.25);
            --mon-amber-light: #fde68a;
            --mon-amber: #f59e0b;
            
            --mon-ready: #10b981;
            --mon-ready-bg: rgba(16, 185, 129, 0.12);
            --mon-ready-border: rgba(16, 185, 129, 0.35);
            --mon-ready-glow: rgba(16, 185, 129, 0.25);
            
            --mon-shelf: #f97316;
            --mon-shelf-bg: rgba(249, 115, 22, 0.12);
            --mon-shelf-border: rgba(249, 115, 22, 0.35);
            
            --mon-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
            --mon-radius: 16px;
            --mon-radius-sm: 10px;
            --mon-radius-pill: 9999px;
            
            --font-body: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Patrick Hand', cursive;
        }

        /* Light Artisan Cream Mode */
        [data-theme="light"] {
            --mon-bg: #fbf7f0;
            --mon-surface: #ffffff;
            --mon-surface-card: #fffdf9;
            --mon-surface-hover: #f7eee1;
            --mon-border: #e9decb;
            --mon-border-subtle: #f0e6d6;
            --mon-text: #241e17;
            --mon-text-secondary: #5e5143;
            --mon-text-muted: #968674;
            
            --mon-primary: #b45309;
            --mon-primary-glow: rgba(180, 83, 9, 0.15);
            --mon-amber-light: #78350f;
            --mon-amber: #b45309;
            
            --mon-ready: #059669;
            --mon-ready-bg: rgba(5, 150, 105, 0.08);
            --mon-ready-border: rgba(5, 150, 105, 0.3);
            --mon-ready-glow: rgba(5, 150, 105, 0.15);
            
            --mon-shelf: #ea580c;
            --mon-shelf-bg: rgba(234, 88, 12, 0.08);
            --mon-shelf-border: rgba(234, 88, 12, 0.3);
            
            --mon-shadow: 0 4px 20px rgba(78, 48, 19, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--mon-bg);
            color: var(--mon-text);
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Header Bar */
        .monitor-header {
            background-color: var(--mon-surface);
            border-bottom: 1px solid var(--mon-border);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            box-shadow: var(--mon-shadow);
            z-index: 20;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon-box {
            width: 44px;
            height: 44px;
            background: var(--mon-primary, #d97706);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            flex-shrink: 0;
        }

        .brand-icon-box svg {
            width: 24px;
            height: 24px;
        }

        .brand-title-wrap h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--mon-text);
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .brand-title-wrap .brand-light {
            font-weight: 400;
            opacity: 0.9;
        }

        .brand-title-wrap .brand-bold {
            font-weight: 900;
            letter-spacing: 0.5px;
            color: var(--mon-amber);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--mon-primary-glow);
            color: var(--mon-amber);
            border: 1px solid var(--mon-border);
            padding: 2px 8px;
            border-radius: var(--mon-radius-pill);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .brand-badge-dot {
            width: 7px;
            height: 7px;
            background-color: var(--mon-ready);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px var(--mon-ready);
            animation: livePulse 2s infinite ease-in-out;
        }

        .brand-subtitle {
            font-size: 0.8rem;
            color: var(--mon-text-secondary);
            margin-top: 3px;
        }

        /* Controls / Actions */
        .controls-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--mon-radius-pill);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid var(--mon-border);
            background-color: var(--mon-surface-card);
            color: var(--mon-text);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-pill:hover {
            background-color: var(--mon-surface-hover);
            border-color: var(--mon-primary);
            transform: translateY(-1px);
        }

        .btn-pill svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .btn-pill-primary {
            background: #d97706;
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-pill-primary:hover {
            background: #b45309;
            color: #ffffff;
        }

        .btn-pill-danger {
            background-color: rgba(220, 38, 38, 0.12);
            color: #ef4444;
            border-color: rgba(220, 38, 38, 0.3);
        }

        .btn-pill-danger:hover {
            background-color: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* Theme Switcher: Bulan & Matahari */
        .theme-switch-wrap {
            display: inline-flex;
            align-items: center;
            background-color: var(--mon-surface-card);
            border: 1px solid var(--mon-border);
            border-radius: var(--mon-radius-pill);
            padding: 3px;
            gap: 2px;
        }

        .theme-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: var(--mon-text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }

        .theme-icon-btn svg {
            width: 17px;
            height: 17px;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .theme-icon-btn:hover {
            color: var(--mon-text);
        }

        .theme-icon-btn.active {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .theme-icon-btn.active svg {
            transform: scale(1.08);
        }

        /* Clock */
        .clock-widget {
            text-align: right;
            padding-left: 14px;
            border-left: 1px solid var(--mon-border);
            min-width: 140px;
        }

        .clock-time {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--mon-amber);
            font-family: 'Plus Jakarta Sans', monospace;
            line-height: 1;
            letter-spacing: 0.5px;
        }

        .clock-date {
            font-size: 0.72rem;
            color: var(--mon-text-secondary);
            margin-top: 3px;
        }

        /* 4-Column Live Grid */
        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            padding: 18px 22px;
            flex: 1;
            align-items: stretch;
        }

        .monitor-column {
            background-color: var(--mon-surface);
            border-radius: var(--mon-radius);
            border: 1px solid var(--mon-border);
            display: flex;
            flex-direction: column;
            box-shadow: var(--mon-shadow);
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .column-header {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--mon-border);
            background-color: var(--mon-surface-card);
        }

        .column-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .column-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .column-icon svg {
            width: 18px;
            height: 18px;
        }

        .column-name {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .column-count-badge {
            background-color: var(--mon-surface);
            border: 1px solid var(--mon-border);
            color: var(--mon-text-secondary);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: var(--mon-radius-pill);
        }

        /* Column Themes */
        .col-baking .column-icon {
            background: rgba(217, 119, 6, 0.15);
            color: var(--mon-amber);
            border: 1px solid rgba(217, 119, 6, 0.3);
        }
        .col-baking .column-name { color: var(--mon-amber); }

        .col-packing .column-icon {
            background: rgba(180, 83, 9, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(180, 83, 9, 0.3);
        }
        .col-packing .column-name { color: #f59e0b; }

        .col-ready {
            border-color: var(--mon-ready-border);
            box-shadow: 0 4px 25px var(--mon-ready-glow);
        }
        .col-ready .column-header {
            background-color: var(--mon-ready-bg);
            border-bottom-color: var(--mon-ready-border);
        }
        .col-ready .column-icon {
            background: var(--mon-ready);
            color: #ffffff;
            box-shadow: 0 0 10px var(--mon-ready-glow);
        }
        .col-ready .column-name { color: var(--mon-ready); }
        .col-ready .column-count-badge {
            background: var(--mon-ready);
            color: #ffffff;
            border-color: var(--mon-ready);
        }

        .col-shelf {
            border-color: var(--mon-shelf-border);
        }
        .col-shelf .column-header {
            background-color: var(--mon-shelf-bg);
            border-bottom-color: var(--mon-shelf-border);
        }
        .col-shelf .column-icon {
            background: var(--mon-shelf);
            color: #ffffff;
        }
        .col-shelf .column-name { color: var(--mon-shelf); }

        /* Card List */
        .cards-list {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
            overflow-y: auto;
            max-height: calc(100vh - 225px);
        }

        /* Order Card */
        .queue-card {
            background-color: var(--mon-surface-card);
            border-radius: var(--mon-radius-sm);
            border: 1px solid var(--mon-border);
            padding: 16px 14px;
            text-align: center;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            animation: cardEnter 0.3s ease-out;
        }

        .queue-card:hover {
            transform: translateY(-2px);
            border-color: var(--mon-primary);
        }

        .queue-number {
            font-family: var(--font-display);
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1;
            color: var(--mon-amber);
            letter-spacing: 1px;
        }

        .customer-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--mon-text);
            margin-top: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .order-meta-info {
            font-size: 0.78rem;
            color: var(--mon-text-secondary);
            margin-top: 4px;
        }

        /* Ready Card Highlight */
        .queue-card.card-ready {
            background-color: var(--mon-ready-bg);
            border: 2px solid var(--mon-ready);
            box-shadow: 0 4px 18px var(--mon-ready-glow);
        }

        .queue-card.card-ready .queue-number {
            color: var(--mon-ready);
            text-shadow: 0 0 12px var(--mon-ready-glow);
        }

        .pill-badge-ready {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--mon-ready);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: var(--mon-radius-pill);
            margin-top: 8px;
            box-shadow: 0 2px 8px var(--mon-ready-glow);
        }

        /* Shelf Card Highlight */
        .queue-card.card-shelf {
            background-color: var(--mon-shelf-bg);
            border: 2px solid var(--mon-shelf);
        }

        .queue-card.card-shelf .queue-number {
            color: var(--mon-shelf);
        }

        .pill-badge-shelf {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--mon-shelf);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: var(--mon-radius-pill);
            margin-top: 8px;
        }

        /* Empty State */
        .empty-col-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            color: var(--mon-text-muted);
            text-align: center;
            gap: 10px;
            margin: auto 0;
        }

        .empty-col-placeholder svg {
            width: 36px;
            height: 36px;
            opacity: 0.4;
            stroke-width: 1.5;
        }

        .empty-col-placeholder span {
            font-size: 0.88rem;
            font-weight: 500;
        }

        /* Footer Ticker */
        .monitor-footer {
            background-color: var(--mon-surface);
            border-top: 1px solid var(--mon-border);
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: var(--mon-text-secondary);
            gap: 16px;
        }

        .ticker-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ticker-tag {
            background: var(--mon-primary-glow);
            color: var(--mon-amber);
            border: 1px solid var(--mon-border);
            padding: 2px 8px;
            border-radius: var(--mon-radius-pill);
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .sync-status {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            color: var(--mon-text-muted);
            font-size: 0.78rem;
        }

        .sync-dot {
            width: 6px;
            height: 6px;
            background-color: var(--mon-ready);
            border-radius: 50%;
            display: inline-block;
        }

        /* Animations */
        @keyframes livePulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.6; }
        }

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .monitor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .brand-subtitle { display: none; }
        }

        @media (max-width: 680px) {
            .monitor-header {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px;
            }
            .controls-section {
                justify-content: space-between;
                flex-wrap: wrap;
            }
            .clock-widget {
                display: none;
            }
            .monitor-grid {
                grid-template-columns: 1fr;
                padding: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="monitor-header">
        <div class="brand-section">
            <div class="brand-icon-box" title="Little Salt Bread" style="font-weight: 900; font-size: 1.2rem; letter-spacing: 0.5px;">
                SB
            </div>
            <div class="brand-title-wrap">
                <h1>
                    <span class="brand-light">little</span>
                    <span class="brand-bold">SALT BREAD</span>
                    <span class="brand-badge">
                        <span class="brand-badge-dot"></span>
                        Status Antrean Live
                    </span>
                </h1>
                <div class="brand-subtitle">
                    Outlet Blok M • Harap perhatikan nomor antrean pada struk pemesanan Anda
                </div>
            </div>
        </div>

        <div class="controls-section">
            <!-- Audio Toggle Button -->
            <button type="button" id="btn-audio" class="btn-pill" onclick="toggleAudio()" title="Aktifkan suara lonceng panggilan saat pesanan siap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                </svg>
                <span id="audio-label">Aktifkan Suara</span>
            </button>

            <!-- Fullscreen Button -->
            <button type="button" class="btn-pill" onclick="toggleFullScreen()" title="Tampilan layar penuh untuk TV display">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                </svg>
                <span>Layar Penuh</span>
            </button>

            <!-- Theme Switcher: Bulan & Matahari (Dual Toggle Capsule) -->
            <div class="theme-switch-wrap" role="group" aria-label="Pilihan Tema Matahari dan Bulan">
                <button type="button" class="theme-icon-btn" id="btn-theme-sun" onclick="setThemeMode('light')" title="Mode Terang (Matahari)" aria-label="Mode Terang (Matahari)">
                    <!-- Sun / Matahari SVG -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </button>
                <button type="button" class="theme-icon-btn" id="btn-theme-moon" onclick="setThemeMode('dark')" title="Mode Gelap (Bulan)" aria-label="Mode Gelap (Bulan)">
                    <!-- Moon / Bulan SVG -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Tombol Keluar Langsung ke Beranda (Pill modern, bersih tanpa dropdown / tanpa emoji) -->
            <a href="/" class="btn-pill btn-pill-danger" title="Kembali ke halaman utama website pemesanan">
            <a href="<?= $baseUrl ?>/" class="btn-pill btn-pill-danger" title="Kembali ke halaman utama website pemesanan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            <!-- Jam Digital -->
            <div class="clock-widget">
                <div id="liveClock" class="clock-time">00:00:00</div>
                <div id="liveDate" class="clock-date">-</div>
            </div>
        </div>
    </header>

    <!-- 4-Column Live Grid -->
    <main class="monitor-grid">
        <!-- Kolom 1: Sedang Dipanggang -->
        <div class="monitor-column col-baking">
            <div class="column-header">
                <div class="column-title-wrap">
                    <div class="column-icon">
                        <!-- Flame / Oven SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                        </svg>
                    </div>
                    <div class="column-name">Sedang Dipanggang</div>
                </div>
                <div class="column-count-badge" id="count-baking">0</div>
            </div>
            <div class="cards-list" id="col-baking">
                <div class="empty-col-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Tidak ada panggangan aktif</span>
                </div>
            </div>
        </div>

        <!-- Kolom 2: Sedang Dikemas -->
        <div class="monitor-column col-packing">
            <div class="column-header">
                <div class="column-title-wrap">
                    <div class="column-icon">
                        <!-- Box / Package SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <div class="column-name">Sedang Dikemas</div>
                </div>
                <div class="column-count-badge" id="count-packing">0</div>
            </div>
            <div class="cards-list" id="col-packing">
                <div class="empty-col-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span>Tidak ada kemasan aktif</span>
                </div>
            </div>
        </div>

        <!-- Kolom 3: Siap Diambil (Counter) -->
        <div class="monitor-column col-ready">
            <div class="column-header">
                <div class="column-title-wrap">
                    <div class="column-icon">
                        <!-- Shopping Bag / Ready SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <div class="column-name">Siap Diambil (Counter)</div>
                </div>
                <div class="column-count-badge" id="count-ready">0</div>
            </div>
            <div class="cards-list" id="col-ready">
                <div class="empty-col-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                    <span>Belum ada pesanan siap</span>
                </div>
            </div>
        </div>

        <!-- Kolom 4: Rak Mandiri (>20 Mnt) -->
        <div class="monitor-column col-shelf">
            <div class="column-header">
                <div class="column-title-wrap">
                    <div class="column-icon">
                        <!-- Warming Shelf / Archive SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="5" rx="1"></rect>
                            <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"></path>
                            <path d="M10 12h4"></path>
                        </svg>
                    </div>
                    <div class="column-name">Rak Mandiri (>20 Mnt)</div>
                </div>
                <div class="column-count-badge" id="count-shelf">0</div>
            </div>
            <div class="cards-list" id="col-shelf">
                <div class="empty-col-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                    <span>Rak mandiri kosong</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Ticker Footer -->
    <footer class="monitor-footer">
        <div class="ticker-text">
            <span class="ticker-tag">Pemberitahuan</span>
            <span>Roti yang belum diambil lebih dari <strong>20 menit</strong> otomatis dipindahkan ke <strong>Rak Mandiri Berpenghangat</strong> agar kerenyahan & kehangatan tetap sempurna.</span>
        </div>
        <div class="sync-status">
            <span class="sync-dot"></span>
            <span>Live Sync 3s</span>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/main.js"></script>
    <script>
        let audioActive = false;
        let knownReadyQueues = new Set();
        let isInitialLoad = true;

        // Toggle Audio Chime
        function toggleAudio() {
            if (typeof soundNotifier !== 'undefined') {
                soundNotifier.init();
                soundNotifier.play('ready');
            }
            audioActive = true;
            const btn = document.getElementById('btn-audio');
            const label = document.getElementById('audio-label');
            label.textContent = 'Suara Aktif';
            btn.style.backgroundColor = 'var(--mon-ready-bg)';
            btn.style.borderColor = 'var(--mon-ready)';
            btn.style.color = 'var(--mon-ready)';
        }

        // Fullscreen Toggle
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen failed:', err);
                });
            } else {
                document.exitFullscreen().catch(err => {});
            }
        }

        // Theme Mode Handler (Matahari / Bulan)
        function setThemeMode(theme) {
            const html = document.documentElement;
            html.setAttribute('data-theme', theme);
            localStorage.setItem('monitor_theme', theme);
            updateThemeButtons(theme);
        }

        function updateThemeButtons(theme) {
            const btnSun = document.getElementById('btn-theme-sun');
            const btnMoon = document.getElementById('btn-theme-moon');
            if (!btnSun || !btnMoon) return;

            if (theme === 'light') {
                btnSun.classList.add('active');
                btnMoon.classList.remove('active');
            } else {
                btnMoon.classList.add('active');
                btnSun.classList.remove('active');
            }
        }

        // Restore saved theme
        const savedTheme = localStorage.getItem('monitor_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeButtons(savedTheme);

        // Live Clock
        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('liveClock');
            const dateEl = document.getElementById('liveDate');
            if (clockEl) clockEl.textContent = now.toLocaleTimeString('id-ID');
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Render Cards
        function renderQueueCards(items, type) {
            if (!items || items.length === 0) {
                const emptyMeta = {
                    baking: { text: 'Tidak ada panggangan aktif', icon: '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>' },
                    packing: { text: 'Tidak ada kemasan aktif', icon: '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>' },
                    ready: { text: 'Belum ada pesanan siap', icon: '<circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line>' },
                    shelf: { text: 'Rak mandiri kosong', icon: '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line>' }
                }[type] || { text: 'Kosong', icon: '<circle cx="12" cy="12" r="10"></circle>' };

                return `
                    <div class="empty-col-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">${emptyMeta.icon}</svg>
                        <span>${emptyMeta.text}</span>
                    </div>
                `;
            }

            return items.map(it => {
                let extraClass = '';
                let extraBadge = '';

                if (type === 'ready') {
                    extraClass = 'card-ready';
                    extraBadge = `
                        <div class="pill-badge-ready">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Silakan ke Counter</span>
                        </div>
                    `;
                } else if (type === 'shelf') {
                    extraClass = 'card-shelf';
                    extraBadge = `
                        <div class="pill-badge-shelf">
                            <span>Rak: ${it.shelf_slot || 'A-01'}</span>
                        </div>
                    `;
                }

                const orderTypeBadge = it.order_type === 'dine_in' ? 'Dine In' : 'Takeaway';

                return `
                    <div class="queue-card ${extraClass}">
                        <div class="queue-number">${it.queue_number}</div>
                        <div class="customer-name">${escapeHtml(it.customer_name || 'Pelanggan')}</div>
                        <div class="order-meta-info">${orderTypeBadge}</div>
                        ${extraBadge}
                    </div>
                `;
            }).join('');
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Fetch Live Queue API
        async function fetchQueueData() {
            try {
                const res = await fetch('<?= $baseUrl ?>/api/get_live_queue.php');
                const json = await res.json();
                if (!json.success || !json.data) return;

                const data = json.data;

                // Update Counts
                const bakingList = data.baking || [];
                const packingList = data.packing || [];
                const readyList = data.ready || [];
                const shelfList = data.shelf || [];

                document.getElementById('count-baking').textContent = bakingList.length;
                document.getElementById('count-packing').textContent = packingList.length;
                document.getElementById('count-ready').textContent = readyList.length;
                document.getElementById('count-shelf').textContent = shelfList.length;

                // Render Columns
                document.getElementById('col-baking').innerHTML = renderQueueCards(bakingList, 'baking');
                document.getElementById('col-packing').innerHTML = renderQueueCards(packingList, 'packing');
                document.getElementById('col-ready').innerHTML = renderQueueCards(readyList, 'ready');
                document.getElementById('col-shelf').innerHTML = renderQueueCards(shelfList, 'shelf');

                // Sound notification for new ready items
                const currentReadySet = new Set(readyList.map(r => r.queue_number));
                if (!isInitialLoad && audioActive && typeof soundNotifier !== 'undefined') {
                    currentReadySet.forEach(num => {
                        if (!knownReadyQueues.has(num)) {
                            soundNotifier.play('ready');
                        }
                    });
                }
                knownReadyQueues = currentReadySet;
                isInitialLoad = false;

            } catch (err) {
                console.error('Fetch live queue failed:', err);
            }
        }

        setInterval(fetchQueueData, 3000);
        fetchQueueData();
    </script>
</body>
</html>
