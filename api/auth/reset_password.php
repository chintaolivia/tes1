<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $token = trim($input['token'] ?? '');
    $password = $input['password'] ?? '';
    $password_confirm = $input['password_confirm'] ?? '';
    
    if (empty($token) || empty($password) || empty($password_confirm)) {
        jsonResponse(false, null, 'Semua field wajib diisi', 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(false, null, 'Password minimal 6 karakter', 400);
    }
    
    if ($password !== $password_confirm) {
        jsonResponse(false, null, 'Password dan konfirmasi password tidak cocok', 400);
    }
    
    $data = [
        'token' => $token,
        'password' => $password
    ];
    
    $result = AuthController::resetPassword($data);
    
    if ($result['success']) {
        jsonResponse(true, null, $result['message'] ?? 'Password berhasil direset', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mereset password', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
