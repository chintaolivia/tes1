<?php
require_once __DIR__ . '/../config/database.php';
$db = getDb();
$db->query("UPDATE orders SET payment_status = 'unpaid', order_status = 'pending' WHERE id = 19");
echo "Order 19 reset to unpaid/pending\n";

