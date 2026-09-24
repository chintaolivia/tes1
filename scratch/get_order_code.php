<?php
require_once __DIR__ . '/../config/database.php';
$db = getDb();
$s = $db->prepare('SELECT id, order_code, payment_method, payment_status, total_amount, order_type FROM orders WHERE order_code = ?');
$s->execute(['SB2409266DA7']);
print_r($s->fetch());
