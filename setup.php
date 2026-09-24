<?php
/**
 * Database Setup & Initialization Helper
 * Little Salt Bread Blok M — POS System
 */

$isCli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/config/database.php';

$messages = [];
$status = 'success';

try {
    // 1. Connect without database name first
    $dsnNoDb = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdo = new PDO($dsnNoDb, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $messages[] = "✅ Terhubung ke MySQL Server (" . DB_HOST . ":" . DB_PORT . ")";

    // 2. Create Database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "✅ Database `" . DB_NAME . "` siap.";

    // 3. Connect to database
    $pdo->exec("USE `" . DB_NAME . "`");

    // 4. Import schema SQL
    $schemaFile = __DIR__ . '/database/database.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        $messages[] = "✅ Skema database & seed menu berhasil diimpor dari database.sql.";
    } else {
        throw new Exception("File database/database.sql tidak ditemukan.");
    }

    // 5. Verify counts
    $counts = [
        'Produk'   => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'Kategori' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'Meja'     => $pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn(),
        'Admin'    => $pdo->query("SELECT COUNT(*) FROM members WHERE role='admin'")->fetchColumn(),
    ];

    foreach ($counts as $k => $c) {
        $messages[] = "ℹ️ Total {$k}: {$c} data terdaftar.";
    }

} catch (Exception $e) {
    $status = 'error';
    $messages[] = "❌ Error: " . $e->getMessage();
}

if ($isCli) {
    echo "\n=== SETUP DATABASE LITTLE SALT BREAD ===\n";
    foreach ($messages as $m) echo strip_tags($m) . "\n";
    echo "========================================\n";
    exit($status === 'success' ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Database — Little Salt Bread</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #fffbeb; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .setup-card { background: #ffffff; border-radius: 16px; padding: 32px; max-width: 540px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; }
        .msg-line { padding: 8px 12px; border-radius: 8px; font-size: 0.95rem; margin-bottom: 8px; background: #f9fafb; font-family: monospace; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 50px; height: 50px; background: #d97706; color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem; margin: 0 auto 12px;">SB</div>
            <h2 style="font-family: 'Patrick Hand', cursive; font-size: 2.2rem; color: #d97706; margin: 0;">Setup Database</h2>
            <p style="color: #6b7280; font-size: 0.9rem; margin: 4px 0 0;">Little Salt Bread Blok M • POS System</p>
        </div>

        <div style="margin-bottom: 24px;">
            <?php foreach ($messages as $msg): ?>
                <div class="msg-line"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        </div>

        <?php if ($status === 'success'): ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="/" class="btn btn-primary btn-block" style="text-align: center; text-decoration: none; padding: 12px; font-weight: 700;">
                    Buka Halaman Utama (Landing)
                </a>
                <a href="/views/customer/index.php" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none; padding: 12px;">
                    Buka Katalog Menu Pelanggan
                </a>
                <a href="/views/admin/dashboard.php" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none; padding: 12px;">
                    Buka Dashboard Kasir / Admin
                </a>
                <a href="/monitor.php" target="_blank" class="btn btn-secondary btn-block" style="text-align: center; text-decoration: none; padding: 12px;">
                    Buka Monitor Outlet TV
                </a>
            </div>
        <?php else: ?>
            <button onclick="window.location.reload()" class="btn btn-primary btn-block">Coba Lagi</button>
        <?php endif; ?>
    </div>
</body>
</html>

