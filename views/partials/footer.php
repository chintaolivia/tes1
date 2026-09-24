<?php
/**
 * Footer Partial — Site Footer & Scripts
 * Little Salt Bread Blok M — POS System
 */

if (!isset($baseUrl) || $baseUrl === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
        $baseUrl = rtrim($m[1], '/');
    } else {
        $baseUrl = '';
    }
}
$extraJs = $extraJs ?? '';
?>
    </main><!-- /.main-content -->
    
    <?php 
    // Tampilkan footer HANYA di Beranda (Homepage / index.php), sembunyikan di halaman lain
    $isBerandaPage = false;
    $currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (!empty($isBeranda) || (strpos($currentScript, '/views/') === false && preg_match('#/(index\.php)?$#i', $currentScript))) {
        $isBerandaPage = true;
    }
    if (!empty($hideFooter)) {
        $isBerandaPage = false;
    }
    ?>
    
    <?php if ($isBerandaPage): ?>
    <!-- Footer (Artisan Bakery Responsive Layout - Khusus Beranda) -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Kolom 1 (Atas/Brand): Brand & Filosofi Bakery -->
                <div class="footer-col footer-col-brand">
                    <a href="<?= $baseUrl ?>/" class="footer-brand">
                        <span class="brand-icon">SB</span>
                        <span class="brand-text">
                            <span class="brand-light">little</span>
                            <span class="brand-bold">SALT BREAD</span>
                        </span>
                    </a>
                    <p class="footer-tagline">
                        Artisan Salt Bread khas Blok M dengan butter premium dan kristal garam laut gurih. Dipanggang segar setiap 15 menit langsung dari oven hangat.
                    </p>
                    
                    <div class="footer-meta-wrap">
                        <div class="footer-meta-pill" title="Jam Buka Outlet">
                            <!-- Clock SVG -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>10:00 – 20:00 WIB</span>
                        </div>
                        
                        <div class="footer-meta-pill" title="Fresh Oven Bake">
                            <!-- Flame / Oven SVG -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                            </svg>
                            <span>Oven Tiap 15 Mnt</span>
                        </div>
                    </div>
                </div>
                
                <!-- Kolom 2 (Kiri): Menu & Pesanan -->
                <div class="footer-col footer-col-menu">
                    <h4 class="footer-heading">Menu & Pesanan</h4>
                    <ul class="footer-links" style="list-style: none !important; padding: 0 !important; margin: 0 !important;">
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/customer/index.php">
                                <span>Katalog Menu</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/customer/index.php?category=roti">
                                <span>Varian Roti</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/customer/index.php?category=minuman">
                                <span>Kopi & Minuman</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/customer/index.php">
                                <span>Semua Menu</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Kolom 3 (Tengah): Layanan & Keanggotaan -->
                <div class="footer-col footer-col-service">
                    <h4 class="footer-heading">Layanan & Member</h4>
                    <ul class="footer-links" style="list-style: none !important; padding: 0 !important; margin: 0 !important;">
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/auth/register.php">
                                <span>Daftar Member</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/auth/login.php">
                                <span>Masuk Akun</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/views/customer/profile.php">
                                <span>Diskon Member</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/#keunggulan">
                                <span>Keunggulan Kami</span>
                            </a>
                        </li>
                        <li style="list-style: none !important; margin: 0 !important; padding: 0 !important;">
                            <a href="<?= $baseUrl ?>/#tentang">
                                <span>Tentang Kami</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Kolom 4 (Kanan): Outlet Blok M & Kontak -->
                <div class="footer-col footer-col-contact">
                    <h4 class="footer-heading">Outlet & Kontak</h4>
                    <div class="footer-contact-list">
                        <div class="footer-contact-item">
                            <!-- Map Pin SVG -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 14px; height: 14px; min-width: 14px; max-width: 14px; flex-shrink: 0; margin-top: 2px;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div>
                                <strong>Blok M Hub</strong><br>
                                <span style="color: var(--text-muted); font-size: 0.65rem;">Melawai, Jaksel</span>
                            </div>
                        </div>

                        <div class="footer-contact-item">
                            <!-- Phone / WhatsApp SVG -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 14px; height: 14px; min-width: 14px; max-width: 14px; flex-shrink: 0; margin-top: 2px;">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <div>
                                <span style="white-space: nowrap; font-size: 0.68rem; font-weight: 600;">0812-3456-7890</span>
                            </div>
                        </div>

                        <div class="footer-contact-item">
                            <!-- Mail SVG -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 14px; height: 14px; min-width: 14px; max-width: 14px; flex-shrink: 0; margin-top: 2px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div>
                                <span style="font-size: 0.65rem; word-break: break-all;">halo@saltbread.id</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Pills -->
                    <div class="footer-social-row">
                        <a href="https://instagram.com" target="_blank" rel="noopener" class="footer-social-btn" title="Instagram">
                            <!-- Instagram SVG -->
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                            <span>Instagram</span>
                        </a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener" class="footer-social-btn" title="TikTok">
                            <!-- TikTok SVG -->
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                            </svg>
                            <span>TikTok</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Legal Bar -->
            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> Little Salt Bread Blok M. Seluruh hak cipta dilindungi.
                </div>
                <div>
                    100% Bahan Halal • Freshly Baked Everyday • Blok M Culinary Hub
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Scripts (Cache-Busted with timestamp) -->
    <script src="<?= $baseUrl ?>/assets/js/theme.js?v=<?= time() ?>"></script>
    <script src="<?= $baseUrl ?>/assets/js/main.js?v=<?= time() ?>"></script>
    <?php if ($extraJs): ?>
    <script src="<?= $baseUrl ?>/assets/js/<?= $extraJs ?>?v=<?= time() ?>"></script>
    <?php endif; ?>
    
    <script>
    // ─── Mobile Menu Toggle ──────────────────────────────────────
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('nav-open');
        });
    }

    // ─── User Dropdown Toggle ────────────────────────────────────
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
    }
    
    // ─── Logout Handler ──────────────────────────────────────────
    async function handleLogout(e) {
        e.preventDefault();
        try {
            await postJSON('<?= $baseUrl ?>/api/auth/logout.php', {});
            window.location.href = '<?= $baseUrl ?>/';
        } catch (err) {
            Toast.error('Gagal logout: ' + err.message);
        }
    }
    
    // ─── Splash Screen Auto-hide ─────────────────────────────────
    function dismissSplash() {
        const splash = document.getElementById('splash');
        if (splash && !splash.classList.contains('splash-hidden')) {
            splash.classList.add('splash-hidden');
            setTimeout(() => { if (splash && splash.parentNode) splash.remove(); }, 300);
        }
    }
    window.addEventListener('load', () => setTimeout(dismissSplash, 100));
    document.addEventListener('DOMContentLoaded', () => setTimeout(dismissSplash, 200));
    setTimeout(dismissSplash, 600);
    
    // ─── Navbar Scroll Effect ────────────────────────────────────
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (!navbar) return;
        
        const currentScroll = window.pageYOffset;
        if (currentScroll > 60) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
        lastScroll = currentScroll;
    }, { passive: true });
    
    // ─── Unregister All Service Workers & Purge All Caches ────────
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(registrations => {
            for (let reg of registrations) {
                reg.unregister();
            }
        });
    }
    if ('caches' in window) {
        caches.keys().then(keys => {
            keys.forEach(key => caches.delete(key));
        });
    }
    </script>
</body>
</html>
