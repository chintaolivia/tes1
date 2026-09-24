<?php
/**
 * Bottom Navigation Partial — Mobile Bottom Nav Bar
 * Little Salt Bread Blok M — POS System
 * 
 * Shows only on mobile screens. Clean SVG icons (Zero emojis).
 */

require_once __DIR__ . '/../../config/session.php';
initSession();

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
if (!isset($baseUrl) || $baseUrl === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
        $baseUrl = rtrim($m[1], '/');
    } else {
        $baseUrl = '';
    }
}
?>

<?php if (isAdmin()): ?>
<!-- Admin Bottom Nav -->
<nav class="bottom-nav admin-bottom-nav" aria-label="Navigasi admin">
    <a href="<?= $baseUrl ?>/views/admin/dashboard.php" class="bottom-nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </span>
        <span class="bottom-nav-label">Dashboard</span>
    </a>
    <a href="<?= $baseUrl ?>/views/admin/orders.php" class="bottom-nav-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </span>
        <span class="bottom-nav-label">Pesanan</span>
    </a>
    <a href="<?= $baseUrl ?>/views/admin/menu.php" class="bottom-nav-item <?= $currentPage === 'menu' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a10 10 0 0 0-10 10c0 5.52 4.48 10 10 10s10-4.48 10-10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"></path></svg>
        </span>
        <span class="bottom-nav-label">Menu</span>
    </a>
    <a href="<?= $baseUrl ?>/views/admin/reports.php" class="bottom-nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        </span>
        <span class="bottom-nav-label">Laporan</span>
    </a>
</nav>
<?php else: ?>
<?php
$isBerandaActive = !empty($isBeranda) || ($currentPage === 'index' && $currentDir !== 'customer');
$isMenuActive = ($currentPage === 'index' && $currentDir === 'customer') || ($currentPage === 'menu');
$isCheckoutActive = ($currentPage === 'checkout');
$isProfileActive = ($currentPage === 'profile' || $currentPage === 'login' || $currentPage === 'register' || $currentPage === 'history');
?>
<!-- Customer Bottom Nav -->
<nav class="bottom-nav" aria-label="Navigasi utama">
    <a href="<?= $baseUrl ?>/index.php" class="bottom-nav-item <?= $isBerandaActive ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </span>
        <span class="bottom-nav-label">Beranda</span>
    </a>
    <a href="<?= $baseUrl ?>/views/customer/index.php" class="bottom-nav-item <?= $isMenuActive ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </span>
        <span class="bottom-nav-label">Menu</span>
    </a>
    <a href="<?= $baseUrl ?>/views/customer/checkout.php" class="bottom-nav-item <?= $isCheckoutActive ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        </span>
        <span class="bottom-nav-label">Keranjang</span>
    </a>
    <a href="<?= $baseUrl ?>/views/customer/profile.php" class="bottom-nav-item <?= $isProfileActive ? 'active' : '' ?>">
        <span class="bottom-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </span>
        <span class="bottom-nav-label">Profil</span>
    </a>
</nav>
<?php endif; ?>
