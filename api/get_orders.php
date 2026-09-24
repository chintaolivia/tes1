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
    $type = $_GET['type'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    
    if (isAdmin()) {
        if ($type === 'stats') {
            $result = OrderController::getStats();
        } else {
            $result = OrderController::getLiveQueue();
        }
    } elseif (isLoggedIn()) {
        $user = getCurrentUser();
        $result = OrderController::getHistory($user['id'], $page, $perPage);
    } else {
        jsonResponse(false, null, 'Akses tidak diizinkan. Silakan login terlebih dahulu.', 401);
        exit;
    }
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, 'Berhasil mengambil data pesanan', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mengambil data pesanan', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
