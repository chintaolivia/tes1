<?php
/**
 * Main Landing Page
 * Little Salt Bread Blok M — POS & Ordering System
 */

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/MenuController.php';
initSession();

$featuredProducts = MenuController::getFeatured()['data'] ?? [];
if (empty($featuredProducts)) {
    $all = MenuController::getAll()['data'] ?? [];
    $featuredProducts = array_slice($all, 0, 3);
}

// Active queue count
$pdo = getDb();
$activeCountStmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('confirmed', 'processing', 'ready') AND DATE(created_at) = CURDATE()");
$activeQueueCount = (int) ($activeCountStmt->fetch()['count'] ?? 0);

$pageTitle = 'Little Salt Bread Blok M — Salt Bread Renyah Gurih Dari Oven';
$pageDescription = 'Nikmati salt bread artisan renyah gurih dengan butter premium dan sea salt langsung dari oven. Pesan online sekarang tanpa antre lama.';
$extraCss = 'landing.css';
$isBeranda = true;

require_once __DIR__ . '/views/partials/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-badge-pill">
                <span>Blok M, Jakarta Selatan • 10:00 - 20:00</span>
            </div>

            <h1 class="hero-heading">
                Salt Bread <span class="hero-highlight">Renyah Gurih</span> Langsung Dari Oven
            </h1>

            <p class="hero-desc">
                Dibuat dengan butter premium dan taburan garam laut alami. Renyah di luar, lembut di dalam. Pesan mandiri dari HP Anda dan pantau antrean secara real-time!
            </p>

            <div class="hero-actions">
                <a href="<?= $baseUrl ?>/views/customer/index.php" class="btn btn-primary btn-lg hero-cta-btn">
                    Mulai Pesan Sekarang &rarr;
                </a>
            </div>
        </div>

        <!-- Hero Image & Multi-Banner Showcase Carousel -->
        <div class="hero-media-wrap">
            <div class="hero-image-card hero-carousel" id="heroCarousel">
                <div class="hero-slides-wrapper">
                    <!-- Slide 1: Blok M Flagship Storefront -->
                    <div class="hero-slide active">
                        <img src="<?= $baseUrl ?>/assets/img/banners/banner_storefront_blokm.jpg" alt="Little Salt Bread Blok M Storefront">
                        <div class="hero-glass-banner">
                            <div class="glass-banner-top">
                                <span class="glass-dot"></span>
                                <span class="glass-tag">COFFEE BAR • MUSIC • SALTBREAD</span>
                            </div>
                            <div class="glass-banner-title">Little Salt Bread Blok M</div>
                            <div class="glass-banner-desc">Outlet Resmi Melawai • Fresh Baked Everyday</div>
                        </div>
                    </div>
                    <!-- Slide 2: Open Kitchen Artisan Oven -->
                    <div class="hero-slide">
                        <img src="<?= $baseUrl ?>/assets/img/banners/banner_saltbread_oven.jpg" alt="Artisan Salt Bread Open Kitchen">
                        <div class="hero-glass-banner">
                            <div class="glass-banner-top">
                                <span class="glass-dot"></span>
                                <span class="glass-tag">OPEN KITCHEN ARTISAN</span>
                            </div>
                            <div class="glass-banner-title">Dipanggang Baru Setiap Jam</div>
                            <div class="glass-banner-desc">Adonan butter murni dengan aroma semerbak</div>
                        </div>
                    </div>
                    <!-- Slide 3: 5 Signature Flavors Platter -->
                    <div class="hero-slide">
                        <img src="<?= $baseUrl ?>/assets/img/banners/banner_flavor_variants.jpg" alt="5 Varian Rasa Little Salt Bread">
                        <div class="hero-glass-banner">
                            <div class="glass-banner-top">
                                <span class="glass-dot"></span>
                                <span class="glass-tag">SIGNATURE SELECTION</span>
                            </div>
                            <div class="glass-banner-title">5 Pilihan Rasa Favorit</div>
                            <div class="glass-banner-desc">Plain Sea Salt, Keju Gurih, Garlic & Truffle Egg</div>
                        </div>
                    </div>
                    <!-- Slide 4: Signature White Box 20 Pcs -->
                    <div class="hero-slide">
                        <img src="<?= $baseUrl ?>/assets/img/banners/banner_white_box_packaging.jpg" alt="Little Salt Bread Signature Box 20 Pcs">
                        <div class="hero-glass-banner">
                            <div class="glass-banner-top">
                                <span class="glass-dot"></span>
                                <span class="glass-tag">PARTY BOX & HAMPERS</span>
                            </div>
                            <div class="glass-banner-title">Signature Box 20 Pcs</div>
                            <div class="glass-banner-desc">Kemasan elegan untuk berbagi kehangatan</div>
                        </div>
                    </div>
                    <!-- Slide 5: Japanese Iced Coffee & Salt Bread Pairing -->
                    <div class="hero-slide">
                        <img src="<?= $baseUrl ?>/assets/img/banners/banner_coffee_pairing.jpg" alt="Salt Bread & Japanese Iced Coffee Pairing">
                        <div class="hero-glass-banner">
                            <div class="glass-banner-top">
                                <span class="glass-dot"></span>
                                <span class="glass-tag">PERFECT PAIRING</span>
                            </div>
                            <div class="glass-banner-title">Kopi Dingin Khas Little</div>
                            <div class="glass-banner-desc">Kombinasi gurih asin dan manis segar otentik</div>
                        </div>
                    </div>
                </div>

                <!-- Carousel Controls & Indicators -->
                <div class="hero-carousel-dots" id="heroCarouselDots">
                    <button type="button" class="hero-dot active" aria-label="Slide 1"></button>
                    <button type="button" class="hero-dot" aria-label="Slide 2"></button>
                    <button type="button" class="hero-dot" aria-label="Slide 3"></button>
                    <button type="button" class="hero-dot" aria-label="Slide 4"></button>
                    <button type="button" class="hero-dot" aria-label="Slide 5"></button>
                </div>
                <button type="button" class="hero-arrow hero-arrow-prev" id="heroPrevBtn" aria-label="Sebelumnya">&lsaquo;</button>
                <button type="button" class="hero-arrow hero-arrow-next" id="heroNextBtn" aria-label="Berikutnya">&rsaquo;</button>
            </div>
        </div>
    </div>
</section>

<!-- USP / Keunggulan Section -->
<section class="section usp-section">
    <div class="container">
        <div class="usp-header">
            <span class="badge badge-primary" style="margin-bottom: 8px;">Kenapa Memilih Kami?</span>
            <h2 class="usp-heading">Keunggulan Little Salt Bread</h2>
            <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">Kami menghadirkan pengalaman artisan bakery modern dengan perpaduan rasa autentik dan kemudahan teknologi.</p>
        </div>

        <div class="usp-grid">
            <div class="card usp-card">
                <div class="usp-num">1</div>
                <h3>Dipanggang Setiap Jam</h3>
                <p>
                    Kami memanggang dalam batch kecil sepanjang hari sehingga roti selalu hangat dan renyah saat sampai di tangan Anda.
                </p>
            </div>

            <div class="card usp-card">
                <div class="usp-num">2</div>
                <h3>Sea Salt & Butter Pilihan</h3>
                <p>
                    Menggunakan pure creamery butter berkualitas tinggi dan kristal garam laut gurih tanpa bahan pengawet buatan.
                </p>
            </div>

            <div class="card usp-card">
                <div class="usp-num">3</div>
                <h3>Rak Mandiri Berpenghangat</h3>
                <p>
                    Roti yang belum diambil lebih dari 20 menit langsung disimpan di heated shelf agar kelembutan dan aroma tetap prima.
                </p>
            </div>

            <div class="card usp-card">
                <div class="usp-num">4</div>
                <h3>Diskon Loyalitas Member</h3>
                <p>
                    Daftar akun gratis dan nikmati diskon otomatis hingga 10% di setiap transaksi pemesanan Anda.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Showcase Section -->
<section class="section featured-section" id="featured">
    <div class="container">
        <div class="featured-header">
            <div>
                <span class="badge badge-primary" style="margin-bottom: 6px;">Paling Diminati</span>
                <h2 class="featured-heading">Varian Favorit Minggu Ini</h2>
            </div>
            <a href="<?= $baseUrl ?>/views/customer/index.php" class="btn btn-secondary featured-header-btn">
                <span>Lihat Semua 11 Menu</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
        </div>

        <div class="featured-grid">
            <?php foreach ($featuredProducts as $fp): ?>
                <div class="card featured-card">
                    <div class="featured-img-wrap">
                        <img src="<?= $baseUrl ?>/<?= htmlspecialchars(ltrim($fp['image_url'] ?? 'assets/img/salt_bread_plain.png', '/')) ?>" 
                             alt="<?= htmlspecialchars($fp['name']) ?>" 
                             loading="lazy"
                             onerror="this.src='<?= $baseUrl ?>/assets/img/salt_bread_plain.png'">
                    </div>
                    <div class="featured-body">
                        <h3 class="featured-title">
                            <?= htmlspecialchars($fp['name']) ?>
                        </h3>
                        <p class="featured-desc">
                            <?= htmlspecialchars($fp['description'] ?? 'Renyah di luar, lembut di dalam dengan lelehan butter.') ?>
                        </p>
                        <div class="featured-footer">
                            <span class="featured-price">
                                <?= formatRupiah($fp['price']) ?>
                            </span>
                            <a href="<?= $baseUrl ?>/views/customer/index.php" class="btn btn-primary featured-btn">
                                <span>Pesan</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Instagram Atmosphere Showcase Section -->
<section class="section insta-showcase-section">
    <div class="container">
        <div class="insta-header">
            <div>
                <span class="badge badge-primary" style="margin-bottom: 6px;">Follow Us @little.saltbread</span>
                <h2 class="insta-heading">Vibes Autentik Little Salt Bread Blok M</h2>
                <p style="color: var(--text-secondary); margin: 4px 0 0; font-size: 0.95rem;">Dari aroma butter hangat yang semerbak hingga racikan kopi artisan dan antrean penikmat salt bread.</p>
            </div>
            <a href="https://www.instagram.com/little.saltbread/" target="_blank" rel="noopener noreferrer" class="btn btn-secondary insta-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                <span>Lihat Instagram &rarr;</span>
            </a>
        </div>

        <div class="insta-grid">
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_storefront_blokm.jpg" alt="Storefront Little Salt Bread Blok M" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Blok M Outlet</span>
                    <p class="insta-caption">Coffee Bar • Music • Salt Bread</p>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_saltbread_oven.jpg" alt="Baking Salt Bread Open Kitchen" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Fresh From Oven</span>
                    <p class="insta-caption">Dipanggang hangat tiap 15 menit</p>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_flavor_variants.jpg" alt="Signature Salt Bread Platter" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Signature Platter</span>
                    <p class="insta-caption">Keju, Truffle, Garlic & Plain Salt Bread</p>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_saltbread_plain_closeup.jpg" alt="Golden Butter Sea Salt Crust" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Melted Butter</span>
                    <p class="insta-caption">Kristal sea salt gurih & tekstur renyah</p>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_coffee_pairing.jpg" alt="Japanese Cold Brew & Coffee Sleeve" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Coffee & Salt Bread</span>
                    <p class="insta-caption">Racikan kopi artisan pendamping salt bread</p>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= $baseUrl ?>/assets/img/banners/banner_white_box_packaging.jpg" alt="Signature White Box 20 Pcs" loading="lazy">
                <div class="insta-overlay">
                    <span class="insta-tag">Signature Box</span>
                    <p class="insta-caption">Kemasan 20 pcs hampers & keluarga</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="cta-banner-section">
    <div class="container cta-banner-inner">
        <h2 class="cta-banner-heading">
            Lapar? Jangan Tunggu Kehabisan!
        </h2>
        <p class="cta-banner-desc">
            Stok salt bread kami dipanggang dalam jumlah terbatas per siklus oven. Pesan lebih awal untuk memastikan varian favorit Anda tetap tersedia.
        </p>
        <a href="<?= $baseUrl ?>/views/customer/index.php" class="cta-banner-btn">
            Mulai Pesan Sekarang &rarr;
        </a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('heroCarousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('.hero-slide');
    const dots = carousel.querySelectorAll('.hero-dot');
    const prevBtn = document.getElementById('heroPrevBtn');
    const nextBtn = document.getElementById('heroNextBtn');
    if (slides.length <= 1) return;

    let currentIndex = 0;
    let timer = null;

    function goToSlide(index) {
        slides[currentIndex].classList.remove('active');
        if (dots[currentIndex]) dots[currentIndex].classList.remove('active');
        
        currentIndex = (index + slides.length) % slides.length;
        
        slides[currentIndex].classList.add('active');
        if (dots[currentIndex]) dots[currentIndex].classList.add('active');
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    function startAutoPlay() {
        stopAutoPlay();
        timer = setInterval(nextSlide, 3800);
    }

    function stopAutoPlay() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            nextSlide();
            startAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            prevSlide();
            startAutoPlay();
        });
    }

    dots.forEach(function(dot, idx) {
        dot.addEventListener('click', function(e) {
            e.preventDefault();
            goToSlide(idx);
            startAutoPlay();
        });
    });

    let heroTouchTimer = null;
    carousel.addEventListener('touchstart', function() {
        stopAutoPlay();
        clearTimeout(heroTouchTimer);
    }, { passive: true });

    carousel.addEventListener('touchend', function() {
        clearTimeout(heroTouchTimer);
        heroTouchTimer = setTimeout(startAutoPlay, 1000);
    }, { passive: true });

    startAutoPlay();
});
</script>

<?php 
require_once __DIR__ . '/views/partials/bottom_nav.php';
require_once __DIR__ . '/views/partials/footer.php'; 
?>
