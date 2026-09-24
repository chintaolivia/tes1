<?php
/**
 * Database Configuration & Connection
 * Little Salt Bread Blok M — POS System
 * 
 * PDO MySQL connection singleton with auto-database creation.
 */

// ─── Database Credentials ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'litdig_kelompok11');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ─── PDO Singleton ───────────────────────────────────────────────
function getDb(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // First try connecting to the specific database
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            // If database doesn't exist, create it and import schema
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $pdo = initializeDatabase();
            } else {
                error_log("Database connection failed: " . $e->getMessage());
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Koneksi database gagal. Pastikan MySQL sudah berjalan.'
                ]));
            }
        }
    }
    
    return $pdo;
}

/**
 * Auto-create database and import schema from database.sql
 */
function initializeDatabase(): PDO {
    // Connect without database name
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Import schema
    $schemaFile = dirname(__DIR__) . '/database/database.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        // Remove the CREATE DATABASE and USE statements since we already did that
        $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
        $sql = preg_replace('/USE.*?;/s', '', $sql);
        $pdo->exec($sql);
    }
    
    return $pdo;
}

// ─── Helper: Generate Daily Queue Number ─────────────────────────
function generateQueueNumber(): string {
    $pdo = getDb();
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM orders 
        WHERE DATE(created_at) = :today
    ");
    $stmt->execute([':today' => $today]);
    $count = (int) $stmt->fetch()['count'];
    
    return 'SB-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

// ─── Helper: Generate Unique Order Code ──────────────────────────
function generateOrderCode(): string {
    $prefix = 'SB';
    $date = date('dmy');
    $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    
    $code = $prefix . $date . $random;
    
    // Ensure uniqueness
    $pdo = getDb();
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE order_code = :code");
    $stmt->execute([':code' => $code]);
    
    if ((int) $stmt->fetch()['count'] > 0) {
        return generateOrderCode(); // Recursively generate if collision
    }
    
    return $code;
}

// ─── Helper: Get Setting Value ───────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    try {
        $pdo = getDb();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();
        
        $value = $result ? $result['setting_value'] : $default;
        $cache[$key] = $value;
        return $value;
    } catch (PDOException $e) {
        return $default;
    }
}

// ─── Helper: Format Rupiah ───────────────────────────────────────
function formatRupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// ─── Helper: Get Base URL ────────────────────────────────────────
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');
    
    // If we're in a subdirectory like /api or /views, go up to root
    $rootPath = '';
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    
    if ($documentRoot && strpos($projectRoot, $documentRoot) === 0) {
        $rootPath = substr($projectRoot, strlen($documentRoot));
    }
    
    return $protocol . '://' . $host . $rootPath;
}

// ─── Helper: Sanitize Input ─────────────────────────────────────
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ─── Helper: JSON Response ───────────────────────────────────────
function jsonResponse(bool $success, $data = null, string $message = '', int $httpCode = 200): void {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

