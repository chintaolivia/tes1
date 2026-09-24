<?php
/**
 * Automated System Verification Test Suite
 * Little Salt Bread Blok M — POS System
 */

$isCli = (php_sapi_name() === 'cli');

echo "================================================================\n";
echo "   LITTLE SALT BREAD BLOK M — AUTOMATED SYSTEM TEST SUITE\n";
echo "================================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function runTest(string $title, callable $testFn) {
    global $testsPassed, $testsFailed;
    try {
        $result = $testFn();
        if ($result !== false) {
            echo " [PASS] " . $title . "\n";
            if (is_string($result) && $result !== '') {
                echo "        > " . $result . "\n";
            }
            $testsPassed++;
        } else {
            echo " [FAIL] " . $title . "\n";
            $testsFailed++;
        }
    } catch (Throwable $e) {
        echo " [FAIL] " . $title . " — Exception: " . $e->getMessage() . "\n";
        $testsFailed++;
    }
}

// ─── Test 1: Config & Session ────────────────────────────────────
runTest("Inisialisasi Session & CSRF Token", function() {
    require_once __DIR__ . '/config/session.php';
    initSession();
    $token = generateCsrfToken();
    if (empty($token) || strlen($token) !== 64) {
        throw new Exception("CSRF token tidak valid: " . $token);
    }
    return "CSRF Token generated successfully: " . substr($token, 0, 10) . "...";
});

// ─── Test 2: Database Connection & Auto Bootstrap ───────────────
runTest("Koneksi Database MySQL & Helper Functions", function() {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDb();
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    $queue = generateQueueNumber();
    $code = generateOrderCode();
    return "Database aktif: {$dbName}, Queue sample: {$queue}, Order code: {$code}";
});

// ─── Test 3: iPaymu Config & Signature Helper ────────────────────
runTest("Konfigurasi iPaymu Sandbox & Signature Generator", function() {
    require_once __DIR__ . '/config/ipaymu.php';
    $config = getIPaymuConfig();
    if ($config['va'] !== '0000005150560383') {
        throw new Exception("VA iPaymu tidak sesuai: " . $config['va']);
    }
    $sigHeaders = generateIPaymuSignature(['test' => 123]);
    if (empty($sigHeaders)) throw new Exception("Signature generation failed");
    return "VA: {$config['va']}, Env: {$config['env']}";
});

// ─── Test 4: Resend Email Config & Templates ────────────────────
runTest("Konfigurasi Resend API & Sender", function() {
    require_once __DIR__ . '/config/resend.php';
    if (RESEND_API_KEY !== 're_Zicwfxjn_Bobdt6fMTjEuVdcNazLSZCQX') {
        throw new Exception("Resend API Key tidak sesuai");
    }
    if (RESEND_FROM_NAME !== 'KopiKenangan' || RESEND_FROM_EMAIL !== 'noreply@shisuka.online') {
        throw new Exception("Sender info tidak sesuai");
    }
    $tpl = emailTemplate('Test', '<p>Hello</p>');
    if (strpos($tpl, 'SALT BREAD') === false) throw new Exception("Template invalid");
    return "Sender: " . RESEND_FROM_NAME . " <" . RESEND_FROM_EMAIL . ">";
});

// ─── Test 5: Menu & Categories Controllers ───────────────────────
runTest("Query MenuController (Categories & 11 Seed Products)", function() {
    require_once __DIR__ . '/controllers/MenuController.php';
    $catsRes = MenuController::getCategories();
    $prodsRes = MenuController::getAll();
    $cats = $catsRes['data'] ?? [];
    $prods = $prodsRes['data'] ?? [];
    if (count($cats) < 2) throw new Exception("Kategori kurang dari 2");
    if (count($prods) < 11) throw new Exception("Produk kurang dari 11 items, ditemukan: " . count($prods));
    return "Kategori: " . count($cats) . ", Total Produk: " . count($prods);
});

// ─── Test 6: Table Controller ────────────────────────────────────
runTest("Query TableController (Denah Meja)", function() {
    require_once __DIR__ . '/controllers/TableController.php';
    $tables = TableController::getAll();
    if (!$tables['success'] || count($tables['data']) < 6) {
        throw new Exception("Data meja tidak mencukupi");
    }
    return "Total meja terdaftar: " . count($tables['data']);
});

// ─── Test 7: PWA Assets & CSS Integrity ──────────────────────────
runTest("Verifikasi File PWA & Stylesheets", function() {
    $requiredFiles = [
        'manifest.json',
        'sw.js',
        'assets/css/style.css',
        'assets/css/landing.css',
        'assets/css/customer.css',
        'assets/css/admin.css',
        'assets/js/theme.js',
        'assets/js/main.js',
        'monitor.php',
        '.htaccess'
    ];
    foreach ($requiredFiles as $rf) {
        if (!file_exists(__DIR__ . '/' . $rf)) {
            throw new Exception("File hilang: " . $rf);
        }
    }
    return "Semua 10 file inti PWA & Frontend valid berada di tempatnya";
});

// ─── Test 8: End-to-End ACID Checkout Transaction ───────────────
runTest("Transaksi Checkout ACID & Deduksi Stok Otomatis", function() {
    require_once __DIR__ . '/controllers/OrderController.php';
    
    // Pick first product
    $pdo = getDb();
    $prod = $pdo->query("SELECT * FROM products WHERE stock > 5 LIMIT 1")->fetch();
    if (!$prod) throw new Exception("Tidak ada produk untuk checkout test");
    
    $initialStock = (int) $prod['stock'];
    
    $orderPayload = [
        'customer_name' => 'Budi Santoso (Test Automated)',
        'customer_phone' => '081299887766',
        'customer_email' => 'budi.test@gmail.com',
        'order_type' => 'takeaway',
        'payment_method' => 'qris',
        'notes' => 'Tolong panaskan lebih renyah',
        'items' => [
            [
                'product_id' => $prod['id'],
                'quantity' => 2,
                'options' => ['notes' => 'extra crispy']
            ]
        ]
    ];
    
    $res = OrderController::create($orderPayload);
    if (!$res['success']) {
        throw new Exception("Gagal membuat order: " . ($res['message'] ?? ''));
    }
    
    $orderData = $res['data'];
    $orderCode = $orderData['order_code'];
    $queueNum = $orderData['queue_number'];
    
    // Check stock was decremented by 2
    $checkStmt = $pdo->prepare("SELECT stock FROM products WHERE id = :id");
    $checkStmt->execute([':id' => $prod['id']]);
    $newStock = (int) $checkStmt->fetchColumn();
    
    if ($newStock !== ($initialStock - 2)) {
        throw new Exception("Deduksi stok gagal! Awal: {$initialStock}, Sekarang: {$newStock}");
    }
    
    // Test status transition to ready
    $updateRes = OrderController::updateStatus($orderData['id'], 'ready');
    if (!$updateRes['success']) {
        throw new Exception("Gagal update status pesanan");
    }
    
    return "Order berhasil dibuat: {$queueNum} ({$orderCode}), Stok produk #{$prod['id']} terdeduksi ({$initialStock} -> {$newStock})";
});

echo "\n----------------------------------------------------------------\n";
echo "HASIL PENGUJIAN: {$testsPassed} LULUS, {$testsFailed} GAGAL\n";
echo "================================================================\n";

exit($testsFailed === 0 ? 0 : 1);
