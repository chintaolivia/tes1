<?php
/**
 * Setup & Inisialisasi Database litdig_kelompok11
 * Jalankan file ini via browser atau CLI
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
initDatabaseTables($pdo);

// Cek apakah tabel berhasil dibuat
$tablesStmt = $pdo->query("SHOW TABLES FROM " . DB_NAME);
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

// Hitung menu
$menuCount = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Database litdig_kelompok11 - Salt Bread</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fffbeb;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 520px;
            width: 100%;
            padding: 32px;
            text-align: center;
            border: 1px solid #fde68a;
        }
        .icon { font-size: 54px; margin-bottom: 12px; }
        h1 { color: #b45309; margin: 0 0 10px; font-size: 24px; }
        p { color: #64748b; font-size: 15px; line-height: 1.6; }
        .table-list {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            text-align: left;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        .table-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #cbd5e1;
            font-size: 14px;
        }
        .table-item:last-child { border-bottom: none; }
        .badge-success {
            background: #dcfce7;
            color: #15803d;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn {
            flex: 1;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-primary { background: #d97706; color: white; }
        .btn-primary:hover { background: #b45309; }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🥐✨</div>
        <h1>Database litdig_kelompok11 Siap!</h1>
        <p>Inisialisasi tabel dan master data varian Salt Bread berhasil dijalankan.</p>

        <div class="table-list">
            <div class="table-item">
                <span>Database</span>
                <span class="badge-success"><?= htmlspecialchars(DB_NAME) ?></span>
            </div>
            <div class="table-item">
                <span>Tabel Terdeteksi (<?= count($tables) ?>)</span>
                <span><?= implode(', ', $tables) ?></span>
            </div>
            <div class="table-item">
                <span>Total Varian Menu Awal</span>
                <span class="badge-success"><?= $menuCount ?> Varian</span>
            </div>
        </div>

        <div class="btn-group">
            <a href="index.php" class="btn btn-primary">Buka Halaman Pemesanan &rarr;</a>
            <a href="admin/index.php" class="btn btn-secondary">Dashboard Admin & Dapur</a>
        </div>
    </div>
</body>
</html>

