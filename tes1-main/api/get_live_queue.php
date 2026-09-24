<?php
/**
 * API JSON Endpoint: Daftar Antrian Aktif untuk Monitor TV Toko
 */

if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDbConnection();

// Cek dan update otomatis order yang siap_diambil > 20 menit (1200 detik) ke rak_mandiri
$stmtCheckTimeout = $pdo->query("
    SELECT id, ready_at FROM orders 
    WHERE order_status = 'siap_diambil' AND ready_at IS NOT NULL
");
while ($row = $stmtCheckTimeout->fetch()) {
    $readyTime = strtotime($row['ready_at']);
    if ((time() - $readyTime) >= 1200) {
        $up = $pdo->prepare("UPDATE orders SET order_status = 'rak_mandiri', updated_at = NOW() WHERE id = ?");
        $up->execute([$row['id']]);
    }
}

// Ambil semua antrian hari ini yang belum selesai
$stmt = $pdo->query("
    SELECT id, queue_number, customer_name, order_type, order_status, ready_at, shelf_slot, updated_at
    FROM orders
    WHERE order_status IN ('dipanggang', 'sedang_dikemas', 'dikemas', 'siap_diambil', 'rak_mandiri')
    ORDER BY id ASC
");
$orders = $stmt->fetchAll();

$bakingList = [];
$packingList = [];
$readyList = [];
$shelfList = [];

foreach ($orders as $ord) {
    $st = $ord['order_status'];
    $itemData = [
        'id'           => (int)$ord['id'],
        'queue_number' => $ord['queue_number'],
        'customer_name'=> $ord['customer_name'],
        'shelf_slot'   => $ord['shelf_slot'] ?: 'Rak Mandiri A-01',
        'ready_at'     => $ord['ready_at']
    ];

    if ($st === 'dipanggang') {
        $bakingList[] = $itemData;
    } elseif ($st === 'sedang_dikemas' || $st === 'dikemas') {
        $packingList[] = $itemData;
    } elseif ($st === 'siap_diambil') {
        $readyList[] = $itemData;
    } elseif ($st === 'rak_mandiri') {
        $shelfList[] = $itemData;
    }
}

echo json_encode([
    'success' => true,
    'timestamp' => time(),
    'baking' => $bakingList,
    'packing' => $packingList,
    'ready' => $readyList,
    'shelf' => $shelfList
]);
