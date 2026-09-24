<?php
/**
 * Reset Password Page
 * Little Salt Bread Blok M — POS System
 * Standalone clean authentication view (no navbar, no footer)
 */

$hideNavbar = true;
$hideFooter = true;
$pageTitle = 'Atur Ulang Password';
$pageDescription = 'Masukkan password baru untuk akun Little Salt Bread Anda.';
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

$token = $_GET['token'] ?? '';

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
        <a href="<?= $baseUrl ?>/views/auth/login.php" class="auth-back-link" title="Kembali ke Login">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Kembali ke Login</span>
        </a>
        <button type="button" id="theme-toggle" class="btn-icon theme-toggle" aria-label="Ganti tema" style="width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
    </div>

    <!-- Reset Password Card -->
    <div class="auth-card">
        <div class="auth-brand-badge">SB</div>
        <h1 class="auth-heading">Password Baru</h1>
        <p class="auth-subheading">Silakan buat password baru yang aman untuk akun Anda.</p>

        <?php if (empty($token)): ?>
            <div style="background: rgba(220, 38, 38, 0.1); border-left: 4px solid var(--error); padding: 14px; border-radius: var(--radius-sm); color: var(--error); margin-bottom: 20px; font-size: 0.88rem;">
                Token reset tidak valid atau tidak ditemukan. Silakan minta tautan baru dari halaman lupa password.
            </div>
            <a href="<?= $baseUrl ?>/views/auth/forgot_password.php" class="btn btn-secondary btn-block">Minta Tautan Baru</a>
        <?php else: ?>
            <form id="reset-form" novalidate>
                <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="password" style="font-weight: 600; font-size: 0.88rem;">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="password_confirm" style="font-weight: 600; font-size: 0.88rem;">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password baru" required autocomplete="new-password">
                </div>

                <button type="submit" id="btn-reset" class="btn btn-primary btn-block" style="padding: 13px; font-size: 0.98rem; font-weight: 700; border-radius: 12px;">
                    Simpan Password Baru
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); text-align: center; font-size: 0.88rem; color: var(--text-secondary);">
            <a href="<?= $baseUrl ?>/views/auth/login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Kembali ke Halaman Login</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reset-form');
    if (!form) return;

    const btnReset = document.getElementById('btn-reset');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (typeof Validator !== 'undefined') {
            Validator.clearAll(form);
        }

        const token = document.getElementById('token').value;
        const password = document.getElementById('password').value;
        const password_confirm = document.getElementById('password_confirm').value;

        let hasError = false;
        if (!password || password.length < 6) {
            if (typeof Validator !== 'undefined') {
                Validator.showError(document.getElementById('password'), 'Password minimal 6 karakter.');
            }
            hasError = true;
        }

        if (password !== password_confirm) {
            if (typeof Validator !== 'undefined') {
                Validator.showError(document.getElementById('password_confirm'), 'Konfirmasi password tidak cocok.');
            }
            hasError = true;
        }

        if (hasError) return;

        if (typeof Loading !== 'undefined') {
            Loading.start(btnReset, 'Menyimpan...');
        }

        try {
            const res = await postJSON('<?= $baseUrl ?>/api/auth/reset_password.php', {
                token, password, password_confirm
            });

            if (res.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(res.message || 'Password berhasil diperbarui! Mengalihkan ke halaman login...');
                }
                setTimeout(() => {
                    window.location.href = '<?= $baseUrl ?>/views/auth/login.php';
                }, 1500);
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(res.message || 'Gagal mereset password.');
                }
                if (typeof Loading !== 'undefined') {
                    Loading.stop(btnReset);
                }
            }
        } catch (err) {
            if (typeof Toast !== 'undefined') {
                Toast.error(err.message || 'Terjadi kesalahan sistem.');
            }
            if (typeof Loading !== 'undefined') {
                Loading.stop(btnReset);
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
