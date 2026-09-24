<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/OrderController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $items = $input['items'] ?? [];
    $customer_name = trim($input['customer_name'] ?? '');
    $payment_method = trim($input['payment_method'] ?? '');
    
    $isPos = !empty($input['is_pos']) || ($input['source'] ?? '') === 'pos';
    if (isAdmin() && !$isPos) {
        jsonResponse(false, null, 'Akun admin tidak dapat membuat pesanan online customer. Silakan gunakan Live POS Kasir.', 403);
    }
    
    if (empty($items) || !is_array($items)) {
        jsonResponse(false, null, 'Daftar pesanan (items) tidak boleh kosong', 400);
    }
    
    if (empty($customer_name)) {
        jsonResponse(false, null, 'Nama pelanggan wajib diisi', 400);
    }
    
    if (empty($payment_method)) {
        jsonResponse(false, null, 'Metode pembayaran wajib diisi', 400);
    }
    
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $input['member_id'] = $user['id'];
    }
    
    $result = OrderController::create($input);
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, $result['message'] ?? 'Pesanan berhasil dibuat', 201);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal membuat pesanan', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
