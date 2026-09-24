<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDb();
$stmt = $pdo->query("SELECT id, name, email, role FROM members WHERE role = 'admin' LIMIT 5");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);

