<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/MenuController.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Metode tidak diizinkan', 405);
}

try {
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    $featured_only = isset($_GET['featured_only']) && filter_var($_GET['featured_only'], FILTER_VALIDATE_BOOLEAN);
    
    if ($featured_only) {
        $result = MenuController::getFeatured();
    } else {
        $result = MenuController::getAll($category, $search);
    }
    
    $categoriesResult = MenuController::getCategories();
    
    if ($result['success']) {
        $data = [
            'menu' => $result['data'],
            'categories' => $categoriesResult['success'] ? $categoriesResult['data'] : []
        ];
        jsonResponse(true, $data, 'Berhasil mengambil data menu', 200);
    } else {
        jsonResponse(false, null, $result['message'] ?? 'Gagal mengambil data menu', 400);
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
