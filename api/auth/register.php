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
    
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $password_confirm = $input['password_confirm'] ?? '';
    
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($password_confirm)) {
        jsonResponse(false, null, 'Semua field wajib diisi', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Format email tidak valid', 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(false, null, 'Password minimal 6 karakter', 400);
    }
    
    if ($password !== $password_confirm) {
        jsonResponse(false, null, 'Password dan konfirmasi password tidak cocok', 400);
    }
    
    $data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password' => $password
    ];
    
    $result = AuthController::register($data);
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, $result['message'] ?? 'Pendaftaran berhasil', 201);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Pendaftaran gagal', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
