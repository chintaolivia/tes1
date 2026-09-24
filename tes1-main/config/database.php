<?php
/**
 * Konfigurasi Database litdig_kelompok11 & Koneksi PDO
 * Aplikasi Antrian Online Salt Bread
 */

date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'litdig_kelompok11');

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Coba koneksi langsung ke database litdig_kelompok11
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET time_zone = '+07:00';");
        return $pdo;
    } catch (PDOException $e) {
        // Jika database belum ada, coba buat database dan inisialisasi tabel otomatis
        try {
            $rootPdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
            $rootPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Reconnect ke database baru
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Jalankan SQL init jika tabel belum ada
            initDatabaseTables($pdo);
            return $pdo;
        } catch (Exception $ex) {
            die("Koneksi Database Gagal: " . $ex->getMessage());
        }
    }
}

/**
 * Inisialisasi struktur tabel dan data awal jika tabel belum ada
 */
function initDatabaseTables($pdo) {
    $sqlFile = dirname(__DIR__) . '/database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
    }
}

/**
 * Helper Format Rupiah
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Generator Kode Antrian Hari Ini (contoh: SB-001, SB-002)
 */
function generateQueueNumber($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $row = $stmt->fetch();
    $nextSeq = ($row ? (int)$row['total'] : 0) + 1;
    return 'SB-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
}

/**
 * Helper Status Label & Warna
 */
function getStatusBadge($status) {
    switch ($status) {
        case 'menunggu_konfirmasi':
            return ['label' => 'Menunggu Konfirmasi', 'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => ''];
        case 'dipanggang':
            return ['label' => 'Roti Sedang Dipanggang', 'color' => '#d97706', 'bg' => '#ffedd5', 'icon' => ''];
        case 'sedang_dikemas':
        case 'dikemas':
            return ['label' => 'Sedang Dikemas', 'color' => '#2563eb', 'bg' => '#dbeafe', 'icon' => ''];
        case 'siap_diambil':
            return ['label' => 'Siap Diambil', 'color' => '#059669', 'bg' => '#d1fae5', 'icon' => ''];
        case 'rak_mandiri':
            return ['label' => 'Rak Pengambilan Mandiri', 'color' => '#dc2626', 'bg' => '#fee2e2', 'icon' => ''];
        case 'selesai':
            return ['label' => 'Selesai', 'color' => '#4b5563', 'bg' => '#f3f4f6', 'icon' => ''];
        case 'dibatalkan':
            return ['label' => 'Dibatalkan', 'color' => '#991b1b', 'bg' => '#fef2f2', 'icon' => ''];
        default:
            return ['label' => ucfirst(str_replace('_', ' ', $status)), 'color' => '#4b5563', 'bg' => '#f3f4f6', 'icon' => ''];
    }
}
