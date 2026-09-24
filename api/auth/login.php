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
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(false, null, 'Email dan password wajib diisi', 400);
    }
    
    $data = [
        'email' => $email,
        'password' => $password
    ];
    
    $result = AuthController::login($data);
    
    if ($result['success'] && !empty($result['data'])) {
        loginUser($result['data']);
        jsonResponse(true, $result['data'], $result['message'] ?? 'Login berhasil', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Login gagal', 401);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
