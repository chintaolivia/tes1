<?php
/**
 * Public Live Queue API for TV Monitor & Live Displays
 * Little Salt Bread Blok M — POS System
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDb();
    
    // Auto-divert: If order has been ready for > 20 mins (1200 seconds), move to shelf
    $shelfTimeout = (int) getSetting('shelf_timeout_minutes', '20');
    $divertStmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = 'shelf', updated_at = NOW()
        WHERE order_status = 'ready' 
          AND ready_at IS NOT NULL 
          AND TIMESTAMPDIFF(MINUTE, ready_at, NOW()) >= :timeout
    ");
    $divertStmt->execute([':timeout' => $shelfTimeout]);

    // Fetch active queue orders from today
    $stmt = $pdo->query("
        SELECT id, queue_number, order_code, customer_name, order_type, 
               order_status, payment_status, shelf_slot, ready_at, estimated_minutes, created_at
        FROM orders
        WHERE order_status IN ('confirmed', 'processing', 'ready', 'shelf')
          AND DATE(created_at) = CURDATE()
        ORDER BY id ASC
    ");
    $orders = $stmt->fetchAll();

    $baking = [];
    $packing = [];
    $ready = [];
    $shelf = [];

    foreach ($orders as $o) {
        $st = $o['order_status'];
        if ($st === 'confirmed' || $st === 'processing') {
            $baking[] = $o;
        } elseif ($st === 'ready') {
            $ready[] = $o;
        } elseif ($st === 'shelf') {
            $shelf[] = $o;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'baking'  => $baking,
            'packing' => $packing,
            'ready'   => $ready,
            'shelf'   => $shelf,
            'total_active' => count($orders),
            'server_time' => date('H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching live queue: ' . $e->getMessage()
    ]);
}

