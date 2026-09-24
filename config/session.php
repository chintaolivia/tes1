<?php
/**
 * Session Management & Auth Guard
 * Little Salt Bread Blok M — POS System
 * 
 * Secure session handling, CSRF protection, and role-based access control.
 */

// ─── Session Configuration ───────────────────────────────────────
function initSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    if (headers_sent()) {
        if (session_status() !== PHP_SESSION_ACTIVE && php_sapi_name() === 'cli') {
            @session_start();
        }
        return;
    }
    
    // Secure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    
    // Use secure cookies in production (HTTPS)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    
    session_name('sb_session');
    session_start();
    
    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// ─── CSRF Token Management ───────────────────────────────────────
function generateCsrfToken(): string {
    initSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool {
    initSession();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function getCsrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

// ─── Authentication Helpers ──────────────────────────────────────

/**
 * Check if user is currently logged in
 */
function isLoggedIn(): bool {
    initSession();
    return !empty($_SESSION['user_id']);
}

/**
 * Check if current user is an admin
 */
function isAdmin(): bool {
    initSession();
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Get current logged-in user data
 */
function getCurrentUser(): ?array {
    initSession();
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer',
        'tier'  => $_SESSION['user_tier'] ?? 'regular',
    ];
}

/**
 * Set session data after successful login
 */
function loginUser(array $user): void {
    initSession();
    session_regenerate_id(true);
    
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_tier']  = $user['tier'] ?? 'regular';
    $_SESSION['_created']   = time();
}

/**
 * Destroy session on logout
 */
function logoutUser(): void {
    initSession();
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}

// ─── Access Guards (Redirect-based for pages) ────────────────────

/**
 * Require authentication — redirect to login if not authenticated
 */
function requireAuth(string $redirectTo = '/views/auth/login.php'): void {
    if (!isLoggedIn()) {
        initSession();
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Require admin role — redirect to home if not admin
 */
function requireAdmin(string $redirectTo = '/index.php'): void {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// ─── Access Guards (JSON-based for API endpoints) ────────────────

/**
 * Require auth for API — return JSON error if not authenticated
 */
function requireAuthApi(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'data'    => null,
            'message' => 'Silakan login terlebih dahulu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Require admin for API — return JSON error if not admin
 */
function requireAdminApi(): void {
    requireAuthApi();
    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'data'    => null,
            'message' => 'Akses ditolak. Hanya admin yang diizinkan.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ─── Flash Messages ──────────────────────────────────────────────

/**
 * Set a flash message to display on next page load
 */
function setFlash(string $type, string $message): void {
    initSession();
    $_SESSION['flash'] = [
        'type'    => $type,    // success, error, warning, info
        'message' => $message,
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    initSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as HTML
 */
function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) {
        return '';
    }
    
    $typeClass = match ($flash['type']) {
        'success' => 'toast-success',
        'error'   => 'toast-error',
        'warning' => 'toast-warning',
        default   => 'toast-info',
    };
    
    return sprintf(
        '<div class="flash-message %s" data-auto-dismiss="5000">%s</div>',
        $typeClass,
        htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8')
    );
}

