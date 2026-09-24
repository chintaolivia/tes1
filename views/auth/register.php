<?php
/**
 * Register Page
 * Little Salt Bread Blok M — POS System
 * Standalone clean authentication view (no navbar, no footer)
 */

$hideNavbar = true;
$hideFooter = true;
$pageTitle = 'Daftar Member Baru';
$pageDescription = 'Daftar akun member Little Salt Bread dan dapatkan diskon eksklusif serta kemudahan pemesanan.';
$extraCss = 'customer.css';
$bodyClass = 'auth-page-clean';

require_once __DIR__ . '/../../config/session.php';
initSession();

// Determine baseUrl dynamically
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (preg_match('#^(.*?)(/(views|api|assets|admin|controllers|services|config|index\.php|monitor\.php))#', $scriptName, $m)) {
    $baseUrl = rtrim($m[1], '/');
} else {
    $baseUrl = '';
}

if (isLoggedIn()) {
    $target = isAdmin() ? ($baseUrl . '/views/admin/dashboard.php') : ($baseUrl . '/views/customer/index.php');
    header('Location: ' . $target);
    exit;
}

require_once __DIR__ . '/../partials/header.php';
?>

<style>
.auth-page-wrapper {
    width: 100%;
    max-width: 440px;
    margin: 0 auto;
    padding: 24px 16px;
    box-sizing: border-box;
}
.auth-top-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.auth-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 0.2s ease;
}
.auth-back-link:hover {
    color: var(--primary);
}
.auth-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 30px 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
    box-sizing: border-box;
}
.auth-brand-badge {
    margin: 0 auto 12px;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.auth-heading {
    font-family: var(--font-display);
    font-size: 1.85rem;
    margin: 0 0 6px;
    color: var(--primary-dark);
    text-align: center;
}
.auth-subheading {
    color: var(--text-secondary);
    margin: 0 0 20px;
    font-size: 0.88rem;
    text-align: center;
    line-height: 1.45;
}
@media (max-width: 480px) {
    .auth-page-wrapper {
        padding: 16px 12px;
    }
    .auth-card {
        padding: 22px 16px;
        border-radius: 16px;
    }
    .auth-heading {
        font-size: 1.65rem;
    }
}
</style>

<div class="auth-page-wrapper">
    <!-- Top Action Bar -->
    <div class="auth-top-actions">
        <a href="<?= $baseUrl ?>/" class="auth-back-link" title="Kembali ke Beranda">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Kembali ke Beranda</span>
        </a>
        <button type="button" id="theme-toggle" class="btn-icon theme-toggle" aria-label="Ganti tema" style="width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
    </div>

    <!-- Register Card -->
    <div class="auth-card">
        <div class="auth-brand-badge">SB</div>
        <h1 class="auth-heading">Daftar Member Baru</h1>
        <p class="auth-subheading">Nikmati diskon otomatis dan kemudahan pesan di Little Salt Bread</p>

        <form id="register-form" novalidate>
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="name" style="font-weight: 600; font-size: 0.88rem;">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Contoh: Budi Santoso" required autocomplete="name">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="email" style="font-weight: 600; font-size: 0.88rem;">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="nama@email.com" required autocomplete="email">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="phone" style="font-weight: 600; font-size: 0.88rem;">Nomor WhatsApp / HP</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="08123456789" required autocomplete="tel">
                <small style="color: var(--text-muted); font-size: 0.78rem;">Untuk notifikasi status pesanan Anda.</small>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="password" style="font-weight: 600; font-size: 0.88rem;">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required autocomplete="new-password">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="password_confirm" style="font-weight: 600; font-size: 0.88rem;">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password di atas" required autocomplete="new-password">
            </div>

            <button type="submit" id="btn-register" class="btn btn-primary btn-block" style="padding: 13px; font-size: 0.98rem; font-weight: 700; border-radius: 12px;">
                Daftar Sekarang
            </button>
        </form>

        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border); text-align: center; font-size: 0.88rem; color: var(--text-secondary);">
            Sudah memiliki akun? <a href="<?= $baseUrl ?>/views/auth/login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Masuk di Sini</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    const btnRegister = document.getElementById('btn-register');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (typeof Validator !== 'undefined') {
            Validator.clearAll(form);
        }

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const password_confirm = document.getElementById('password_confirm').value;

        let hasError = false;
        if (!name) {
            Validator.showError(document.getElementById('name'), 'Nama lengkap wajib diisi.');
            hasError = true;
        }

        if (!email) {
            Validator.showError(document.getElementById('email'), 'Email wajib diisi.');
            hasError = true;
        } else if (!Validator.isEmail(email)) {
            Validator.showError(document.getElementById('email'), 'Format email tidak valid.');
            hasError = true;
        }

        if (!phone) {
            Validator.showError(document.getElementById('phone'), 'Nomor WhatsApp wajib diisi.');
            hasError = true;
        } else if (!Validator.isPhone(phone)) {
            Validator.showError(document.getElementById('phone'), 'Nomor HP tidak valid (gunakan format 08xx atau +62xx).');
            hasError = true;
        }

        if (!password) {
            Validator.showError(document.getElementById('password'), 'Password wajib diisi.');
            hasError = true;
        } else if (!Validator.minLength(password, 6)) {
            Validator.showError(document.getElementById('password'), 'Password minimal 6 karakter.');
            hasError = true;
        }

        if (password !== password_confirm) {
            Validator.showError(document.getElementById('password_confirm'), 'Konfirmasi password tidak cocok.');
            hasError = true;
        }

        if (hasError) return;

        Loading.start(btnRegister, 'Mendaftarkan...');

        try {
            const res = await postJSON('<?= $baseUrl ?>/api/auth/register.php', {
                name, email, phone, password, password_confirm
            });

            if (res.success) {
                Toast.success(res.message || 'Pendaftaran berhasil! Silakan periksa email verifikasi Anda.');
                form.reset();
                setTimeout(() => {
                    window.location.href = '<?= $baseUrl ?>/views/auth/login.php';
                }, 1500);
            } else {
                Toast.error(res.message || 'Gagal mendaftar.');
                Loading.stop(btnRegister);
            }
        } catch (err) {
            Toast.error(err.message || 'Terjadi kesalahan sistem.');
            Loading.stop(btnRegister);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
