<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDb();

try {
    $today = date('Y-m-d');
    
    // Test stmtQueue
    $stmtQueue = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE (order_status IN ('confirmed', 'processing', 'ready', 'shelf') OR (order_status = 'pending' AND payment_status = 'paid'))");
    $stmtQueue->execute();
    $activeQueueCount = (int)($stmtQueue->fetch()['count'] ?? 0);
    echo "Queue count test: PASS ($activeQueueCount)\n";

    // Test stmtToday
    $stmtToday = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = ? AND order_status != 'cancelled'");
    $stmtToday->execute([$today]);
    $todayStat = $stmtToday->fetch();
    echo "Today stat test: PASS\n";

    // Test stmtLiveOrders
    $stmtLiveOrders = $pdo->prepare("
        SELECT o.id, o.order_code, o.queue_number, o.customer_name, o.order_type, o.order_status, o.payment_status, o.total_amount, o.created_at
        FROM orders o
        WHERE (o.order_status IN ('confirmed', 'processing', 'ready', 'shelf') OR (o.order_status = 'pending' AND o.payment_status = 'paid'))
        ORDER BY (DATE(o.created_at) = CURDATE()) DESC, o.id DESC
        LIMIT 20
    ");
    $stmtLiveOrders->execute();
    $liveOrders = $stmtLiveOrders->fetchAll();
    echo "Live orders test: PASS (" . count($liveOrders) . " rows)\n";

} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
echo "ALL DASHBOARD SQL QUERIES EXECUTE SUCCESSFULLY!\n";

