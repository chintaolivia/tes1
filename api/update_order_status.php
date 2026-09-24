<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/OrderController.php';

initSession();
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $order_id = $input['order_id'] ?? null;
    $new_status = $input['new_status'] ?? '';
    
    if (empty($order_id) || empty($new_status)) {
        jsonResponse(false, null, 'Order ID dan status baru wajib diisi', 400);
    }
    
    $valid_statuses = ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'];
    $valid_statuses = ['pending', 'confirmed', 'processing', 'ready', 'shelf', 'completed', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        jsonResponse(false, null, 'Status pesanan tidak valid', 400);
        jsonResponse(false, null, 'Status pesanan tidak valid: ' . $new_status, 400);
    }
    
    $result = OrderController::updateStatus($order_id, $new_status);
    $result = OrderController::updateStatus($order_id, $new_status, $input);
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, $result['message'] ?? 'Status pesanan berhasil diupdate', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mengupdate status pesanan', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
