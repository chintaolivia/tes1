<?php
/**
 * API Endpoint: Update Status Pesanan & Stok oleh Staf / Admin
 */

if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDbConnection();

$action = $_POST['action'] ?? '';

// 1. Update Status Pesanan
if ($action === 'change_status') {
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');

    $allowed = ['menunggu_konfirmasi', 'dipanggang', 'sedang_dikemas', 'dikemas', 'siap_diambil', 'rak_mandiri', 'selesai', 'dibatalkan'];
    if (!in_array($newStatus, $allowed) || $orderId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Status atau ID pesanan tidak valid.']);
        exit;
    }

    $extraSql = "";
    $params = [$newStatus];

    // Jika status berubah ke siap_diambil, catat ready_at sekarang
    if ($newStatus === 'siap_diambil') {
        $extraSql = ", ready_at = NOW()";
    } elseif ($newStatus === 'selesai') {
        $extraSql = ", picked_up_at = NOW()";
    }

    $params[] = $orderId;
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() {$extraSql} WHERE id = ?");
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
    exit;
}

// 2. Verifikasi Pembayaran
if ($action === 'verify_payment') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'dipanggang', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$orderId]);

    echo json_encode(['success' => true, 'message' => 'Pembayaran diverifikasi & pesanan masuk ke oven panggangan.']);
    exit;
}

// 3. Update Stok Cepat
if ($action === 'adjust_stock') {
    $menuId = (int)($_POST['menu_id'] ?? 0);
    $delta  = (int)($_POST['delta'] ?? 0);

    if ($menuId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Menu ID tidak valid.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE menu_items SET stock = GREATEST(0, stock + ?) WHERE id = ?");
    $stmt->execute([$delta, $menuId]);

    $newStock = $pdo->prepare("SELECT stock FROM menu_items WHERE id = ?");
    $newStock->execute([$menuId]);
    $st = $newStock->fetchColumn();

    echo json_encode(['success' => true, 'new_stock' => $st, 'message' => 'Stok berhasil diperbarui.']);
    exit;
}

// 4. Set Stok Spesifik
if ($action === 'set_stock') {
    $menuId   = (int)($_POST['menu_id'] ?? 0);
    $newStock = max(0, (int)($_POST['stock'] ?? 0));

    $stmt = $pdo->prepare("UPDATE menu_items SET stock = ? WHERE id = ?");
    $stmt->execute([$newStock, $menuId]);

    echo json_encode(['success' => true, 'new_stock' => $newStock, 'message' => 'Stok berhasil diatur.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
