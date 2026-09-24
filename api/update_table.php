<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/TableController.php';

initSession();
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $action = $input['action'] ?? '';
    
    if (empty($action)) {
        jsonResponse(false, null, 'Action tidak diberikan', 400);
    }
    
    $result = ['success' => false, 'message' => 'Action tidak valid'];
    
    if ($action === 'create') {
        $result = TableController::create($input);
    } elseif ($action === 'update') {
        $id = $input['id'] ?? null;
        if (!$id) jsonResponse(false, null, 'ID wajib diisi', 400);
        $result = TableController::update($id, $input);
    } elseif ($action === 'delete') {
        $id = $input['id'] ?? null;
        if (!$id) jsonResponse(false, null, 'ID wajib diisi', 400);
        $result = TableController::delete($id);
    } elseif ($action === 'update_status') {
        $id = $input['id'] ?? null;
        $status = $input['status'] ?? null;
        if (!$id || !$status) jsonResponse(false, null, 'ID dan status wajib diisi', 400);
        $result = TableController::updateStatus($id, $status);
    }
    
    if ($result['success']) {
        jsonResponse(true, $result['data'] ?? null, $result['message'] ?? 'Berhasil memproses meja', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal memproses meja', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
