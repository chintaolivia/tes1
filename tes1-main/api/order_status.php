<?php
/**
 * API JSON Endpoint: Status Pesanan Real-Time
 * Menghitung waktu tunggu, status 5-tahap, dan durasi menit sejak status siap_diambil
 */

if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDbConnection();

$orderCode = trim($_GET['order_code'] ?? '');
$queueNum  = trim($_GET['queue_number'] ?? '');

if (empty($orderCode) && empty($queueNum)) {
    echo json_encode(['success' => false, 'message' => 'Parameter order_code atau queue_number wajib diisi.']);
    exit;
}

if (!empty($orderCode)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
    $stmt->execute([$orderCode]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE queue_number = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$queueNum]);
}

$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
    exit;
}

// Cek dan hitung durasi sejak ready_at jika status siap_diambil atau rak_mandiri
$readyElapsedSeconds = 0;
$isOver20Minutes = false;

if (!empty($order['ready_at'])) {
    $readyTime = strtotime($order['ready_at']);
    $now = time();
    $readyElapsedSeconds = max(0, $now - $readyTime);

    // Jika status masih siap_diambil tapi sudah lewat 20 menit (1200 detik), auto-update ke rak_mandiri!
    if ($order['order_status'] === 'siap_diambil' && $readyElapsedSeconds >= 1200) {
        $updateStmt = $pdo->prepare("UPDATE orders SET order_status = 'rak_mandiri', updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$order['id']]);
        $order['order_status'] = 'rak_mandiri';
    }

    if ($readyElapsedSeconds >= 1200) {
        $isOver20Minutes = true;
    }
}

// Hitung antrian yang masih di depan pesanan ini jika masih dipanggang/dikemas
$queueAhead = 0;
if (in_array($order['order_status'], ['menunggu_konfirmasi', 'dipanggang'])) {
    $stmtAhead = $pdo->prepare("
        SELECT COUNT(*) FROM orders 
        WHERE id < ? AND order_status IN ('dipanggang', 'sedang_dikemas', 'dikemas')
    ");
    $stmtAhead->execute([$order['id']]);
    $queueAhead = (int)$stmtAhead->fetchColumn();
}

$badge = getStatusBadge($order['order_status']);

// Ambil item pesanan
$itemsStmt = $pdo->prepare("SELECT item_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

echo json_encode([
    'success' => true,
    'order' => [
        'id'                 => (int)$order['id'],
        'queue_number'       => $order['queue_number'],
        'order_code'         => $order['order_code'],
        'customer_name'      => $order['customer_name'],
        'order_type'         => $order['order_type'],
        'order_status'       => $order['order_status'],
        'status_label'       => $badge['label'],
        'status_icon'        => $badge['icon'],
        'payment_status'     => $order['payment_status'],
        'total_amount'       => (int)$order['total_amount'],
        'formatted_total'    => rupiah($order['total_amount']),
        'estimated_minutes'  => (int)$order['estimated_minutes'],
        'queue_ahead'        => $queueAhead,
        'ready_at'           => $order['ready_at'],
        'ready_elapsed_sec'  => $readyElapsedSeconds,
        'is_over_20_mins'    => $isOver20Minutes,
        'shelf_slot'         => $order['shelf_slot'] ?: 'Rak Mandiri A-01',
        'created_at'         => $order['created_at'],
        'items'              => $items
    ]
]);
