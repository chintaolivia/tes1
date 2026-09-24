<?php
/**
 * Login Page
 * Little Salt Bread Blok M — POS System
 * Standalone clean authentication view (no navbar, no footer)
 */

$hideNavbar = true;
$hideFooter = true;
$pageTitle = 'Masuk ke Akun Anda';
$pageDescription = 'Masuk ke akun Little Salt Bread untuk menikmati promo member dan kemudahan pemesanan.';
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

// If already logged in, redirect
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
    max-width: 420px;
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
    padding: 32px 26px;
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
    margin: 0 0 22px;
    font-size: 0.88rem;
    text-align: center;
    line-height: 1.45;
}
.auth-demo-box {
    margin-top: 18px;
    background: rgba(217, 119, 6, 0.08);
    border: 1px dashed rgba(217, 119, 6, 0.4);
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 0.82rem;
    color: var(--text-secondary);
    text-align: left;
}
@media (max-width: 480px) {
    .auth-page-wrapper {
        padding: 16px 12px;
    }
    .auth-card {
        padding: 24px 18px;
        border-radius: 16px;
    }
    .auth-heading {
        font-size: 1.7rem;
    }
}
</style>

<div class="auth-page-wrapper">
    <!-- Top Action Bar: Back to Home + Clean Theme Toggle -->
    <div class="auth-top-actions">
        <a href="<?= $baseUrl ?>/" class="auth-back-link" title="Kembali ke Beranda">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Kembali ke Beranda</span>
        </a>
        <button type="button" id="theme-toggle" class="btn-icon theme-toggle" aria-label="Ganti tema" style="width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
    </div>

    <!-- Login Card -->
    <div class="auth-card">
        <!-- Brand Icon & Titles -->
        <div class="auth-brand-badge">SB</div>
        <h1 class="auth-heading">Selamat Datang</h1>
        <p class="auth-subheading">Masuk ke akun Anda untuk kemudahan pemesanan Salt Bread</p>

        <!-- Form -->
        <form id="login-form" novalidate>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" for="email" style="font-weight: 600; font-size: 0.88rem;">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="nama@email.com" required autocomplete="email">
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 0;">Password</label>
                    <a href="<?= $baseUrl ?>/views/auth/forgot_password.php" style="font-size: 0.82rem; color: var(--primary); text-decoration: none; font-weight: 600;">Lupa password?</a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required autocomplete="current-password" style="padding-right: 42px;">
                    <button type="button" id="toggle-pwd" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; padding: 4px;" aria-label="Lihat password">
                        <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="btn-login" class="btn btn-primary btn-block" style="padding: 13px; font-size: 0.98rem; font-weight: 700; border-radius: 12px;">
                Masuk Sekarang
            </button>
        </form>

        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border); text-align: center; font-size: 0.88rem; color: var(--text-secondary);">
            Belum punya akun? <a href="<?= $baseUrl ?>/views/auth/register.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Daftar Akun Baru</a>
        </div>

        <!-- Demo Helper Box for Admin / Kasir -->
        <div class="auth-demo-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-weight: 700; color: var(--primary-dark);">Akun Demo Kasir / Admin:</span>
                <button type="button" id="btn-autofill-admin" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                    Isi Otomatis
                </button>
            </div>
            <div>Email: <strong style="color: var(--text-primary);">admin@saltbread.id</strong></div>
            <div>Password: <strong style="color: var(--text-primary);">admin123</strong></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const btnLogin = document.getElementById('btn-login');
    const emailInput = document.getElementById('email');
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('toggle-pwd');
    const btnAutofill = document.getElementById('btn-autofill-admin');

    // Autofill Admin Demo
    if (btnAutofill) {
        btnAutofill.addEventListener('click', () => {
            emailInput.value = 'admin@saltbread.id';
            pwdInput.value = 'admin123';
            if (typeof Toast !== 'undefined') {
                Toast.info('Akun admin telah diisikan ke formulir');
            }
        });
    }

    // Toggle Password Visibility
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            toggleBtn.innerHTML = isPassword 
                ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
                : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        });
    }

    // Form Submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (typeof Validator !== 'undefined') {
            Validator.clearAll(form);
        }

        const email = emailInput.value.trim();
        const password = pwdInput.value;

        let hasError = false;
        if (!email) {
            if (typeof Validator !== 'undefined') {
                Validator.showError(emailInput, 'Email wajib diisi.');
            }
            hasError = true;
        } else if (typeof Validator !== 'undefined' && !Validator.isEmail(email)) {
            Validator.showError(emailInput, 'Format email tidak valid.');
            hasError = true;
        }

        if (!password) {
            if (typeof Validator !== 'undefined') {
                Validator.showError(pwdInput, 'Password wajib diisi.');
            }
            hasError = true;
        }

        if (hasError) return;

        if (typeof Loading !== 'undefined') {
            Loading.start(btnLogin, 'Sedang masuk...');
        }

        try {
            const res = await postJSON('<?= $baseUrl ?>/api/auth/login.php', { email, password });
            if (res.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success('Login berhasil! Mengalihkan...');
                }
                setTimeout(() => {
                    if (res.data && res.data.role === 'admin') {
                        window.location.href = '<?= $baseUrl ?>/views/admin/dashboard.php';
                    } else {
                        window.location.href = '<?= $baseUrl ?>/views/customer/index.php';
                    }
                }, 600);
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(res.message || 'Email atau password salah.');
                }
                if (typeof Loading !== 'undefined') {
                    Loading.stop(btnLogin);
                }
            }
        } catch (err) {
            if (typeof Toast !== 'undefined') {
                Toast.error(err.message || 'Terjadi kesalahan sistem.');
            }
            if (typeof Loading !== 'undefined') {
                Loading.stop(btnLogin);
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
