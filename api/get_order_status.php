<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/OrderController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $order_code = trim($_GET['order_code'] ?? '');
    
    if (empty($order_code)) {
        jsonResponse(false, null, 'Kode pesanan wajib diisi', 400);
    }
    
    $result = OrderController::getByCode($order_code);
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, 'Berhasil mendapatkan status pesanan', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Pesanan tidak ditemukan', 404);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
