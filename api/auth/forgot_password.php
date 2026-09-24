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
    
    if (empty($email)) {
        jsonResponse(false, null, 'Email wajib diisi', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Format email tidak valid', 400);
    }
    
    $result = AuthController::forgotPassword(['email' => $email]);
    
    if ($result['success']) {
        jsonResponse(true, null, $result['message'] ?? 'Instruksi reset password telah dikirim ke email Anda', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mengirim instruksi reset password', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
