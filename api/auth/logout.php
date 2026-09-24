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
    $result = AuthController::logout();
    logoutUser();
    jsonResponse(true, null, 'Logout berhasil', 200);
} catch (Exception $e) {
    jsonResponse(false, null, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
}
