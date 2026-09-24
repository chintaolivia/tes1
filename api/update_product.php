<?php
/**
 * API Endpoint: Update Product / Menu Management
 * Little Salt Bread Blok M — POS System
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/MenuController.php';

initSession();
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Metode permintaan tidak diizinkan.', 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'toggle_availability':
            $id = (int)($input['product_id'] ?? $input['id'] ?? 0);
            if (!$id) {
                jsonResponse(false, null, 'ID produk wajib diisi.', 400);
            }
            $targetStatus = isset($input['is_available']) ? (int)$input['is_available'] : null;
            $res = MenuController::toggleAvailability($id, $targetStatus);
            jsonResponse(true, $res, $res['message'], 200);
            break;

        case 'create_product':
            if (empty($input['name']) || empty($input['category_id']) || !isset($input['price'])) {
                jsonResponse(false, null, 'Nama, kategori, dan harga wajib diisi.', 400);
            }
            $res = MenuController::create($input);
            jsonResponse(true, $res, $res['message'] ?? 'Menu berhasil ditambahkan.', 200);
            break;

        case 'update_product':
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                jsonResponse(false, null, 'ID produk tidak valid.', 400);
            }
            $res = MenuController::update($id, $input);
            jsonResponse(true, $res, $res['message'] ?? 'Menu berhasil diperbarui.', 200);
            break;

        case 'delete_product':
            $id = (int)($input['product_id'] ?? $input['id'] ?? 0);
            if (!$id) {
                jsonResponse(false, null, 'ID produk tidak valid.', 400);
            }
            $res = MenuController::delete($id);
            jsonResponse(true, $res, $res['message'] ?? 'Menu berhasil dinonaktifkan.', 200);
            break;

        case 'adjust_stock':
            $id = (int)($input['product_id'] ?? $input['id'] ?? 0);
            $delta = (int)($input['delta'] ?? 0);
            if (!$id) {
                jsonResponse(false, null, 'ID produk tidak valid.', 400);
            }
            $res = MenuController::adjustStock($id, $delta);
            jsonResponse(true, $res, $res['message'] ?? 'Stok berhasil diperbarui.', 200);
            break;

        default:
            jsonResponse(false, null, 'Aksi tidak dikenali.', 400);
            break;
    }
} catch (Exception $e) {
    jsonResponse(false, null, $e->getMessage(), 400);
}

