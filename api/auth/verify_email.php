<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die('Metode tidak diizinkan');
}

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Token tidak valid');
}

try {
    $result = AuthController::verifyEmail($token);
    
    if ($result['success']) {
        $_SESSION['flash_message'] = 'Email berhasil diverifikasi. Silakan login.';
        $_SESSION['flash_type'] = 'success';
        header('Location: /login.php');
        exit;
    } else {
        die($result['message'] ?? 'Gagal memverifikasi email');
    }
} catch (Exception $e) {
    die('Terjadi kesalahan sistem: ' . $e->getMessage());
}
