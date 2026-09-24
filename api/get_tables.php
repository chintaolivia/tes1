<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/TableController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $available_only = isset($_GET['available_only']) && filter_var($_GET['available_only'], FILTER_VALIDATE_BOOLEAN);
    
    if ($available_only) {
        $result = TableController::getAvailable();
    } else {
        $result = TableController::getAll();
    }
    
    if ($result['success']) {
        jsonResponse(true, $result['data'], 'Berhasil mengambil data meja', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mengambil data meja', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
