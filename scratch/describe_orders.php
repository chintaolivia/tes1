<?php
require_once __DIR__ . '/../config/database.php';
$db = getDb();
$stmt = $db->query("DESCRIBE orders");
while ($r = $stmt->fetch()) {
    echo $r['Field'] . "\n";
}

