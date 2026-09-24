<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDb();
$users = $pdo->query("SELECT id, name, email, role FROM members WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

