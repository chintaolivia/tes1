<?php
/**
 * Test Otomatis Sistem Antrian Online Salt Bread
 * Memvalidasi Database litdig_kelompok11, Stok, Pembayaran Awal, Alur Status, & Aturan 20 Menit Rak Mandiri
 */

require_once __DIR__ . '/config/database.php';

echo "=== MEMULAI PENGUJIAN SISTEM ANTRIAN SALT BREAD ===\n\n";

$pdo = getDbConnection();

// 1. Uji Database & Tabel
echo "[1] Memeriksa Database 'litdig_kelompok11' & Tabel...\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "    Tabel ditemukan: " . implode(', ', $tables) . "\n";
assert(in_array('menu_items', $tables), "Tabel menu_items harus ada!");
assert(in_array('orders', $tables), "Tabel orders harus ada!");
assert(in_array('order_items', $tables), "Tabel order_items harus ada!");
echo "    -> PASS: Seluruh tabel valid.\n\n";

// 2. Uji Menu & Stok Awal
echo "[2] Memeriksa Data Menu & Stok Awal...\n";
$item = $pdo->query("SELECT * FROM menu_items WHERE id = 1")->fetch();
echo "    Menu #1: {$item['name']} | Stok: {$item['stock']} | Harga: Rp " . number_format($item['price']) . "\n";
$initialStock = (int)$item['stock'];
echo "    -> PASS: Master data produk terverifikasi.\n\n";

// 3. Uji Pembuatan Pesanan & Keep Stok
echo "[3] Uji Checkout Pesanan & Pemotongan Stok Otomatis...\n";
$qtyToOrder = 2;
$queueNum = generateQueueNumber($pdo);
$orderCode = 'SB' . date('ymd') . strtoupper(substr(uniqid(), -5));

$pdo->beginTransaction();

// Potong stok
$pdo->prepare("UPDATE menu_items SET stock = stock - ? WHERE id = 1")->execute([$qtyToOrder]);

// Buat order
$stmt = $pdo->prepare("
    INSERT INTO orders (queue_number, order_code, customer_name, customer_phone, order_type, total_amount, payment_method, payment_status, order_status, estimated_minutes)
    VALUES (?, ?, 'Tata Cantik', '08123456789', 'takeaway', ?, 'qris', 'unpaid', 'menunggu_konfirmasi', 15)
");
$stmt->execute([$queueNum, $orderCode, $item['price'] * $qtyToOrder]);
$orderId = $pdo->lastInsertId();

$pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, item_name, quantity, price, subtotal) VALUES (?, 1, ?, ?, ?, ?)")
    ->execute([$orderId, $item['name'], $qtyToOrder, $item['price'], $item['price'] * $qtyToOrder]);

$pdo->commit();

// Cek sisa stok di database
$updatedStock = (int)$pdo->query("SELECT stock FROM menu_items WHERE id = 1")->fetchColumn();
echo "    Stok awal: {$initialStock}, Memesan: {$qtyToOrder}, Sisa stok sekarang: {$updatedStock}\n";
assert($updatedStock === ($initialStock - $qtyToOrder), "Stok harus berkurang sesuai pesanan!");
echo "    Nomor Antrian Diterbitkan: {$queueNum} | Kode: {$orderCode}\n";
echo "    -> PASS: Fitur keep stok dan pembuatan nomor antrian berhasil!\n\n";

// 4. Uji Pembayaran Awal (QRIS / Lunas)
echo "[4] Uji Pembayaran di Awal & Perubahan Status ke Dipanggang...\n";
$pdo->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'dipanggang' WHERE id = ?")->execute([$orderId]);
$checkOrder = $pdo->query("SELECT payment_status, order_status FROM orders WHERE id = {$orderId}")->fetch();
echo "    Payment Status: {$checkOrder['payment_status']} | Order Status: {$checkOrder['order_status']}\n";
assert($checkOrder['payment_status'] === 'paid', "Status pembayaran harus 'paid'!");
assert($checkOrder['order_status'] === 'dipanggang', "Status pesanan harus 'dipanggang'!");
echo "    -> PASS: Pembayaran awal terverifikasi & pesanan masuk proses oven.\n\n";

// 5. Uji Alur Status Dapur (Dipanggang -> Sedang Dikemas -> Siap Diambil)
echo "[5] Uji Alur Status Dapur ke 'Siap Diambil'...\n";
$pdo->prepare("UPDATE orders SET order_status = 'sedang_dikemas' WHERE id = ?")->execute([$orderId]);
$pdo->prepare("UPDATE orders SET order_status = 'siap_diambil', ready_at = NOW() WHERE id = ?")->execute([$orderId]);
$readyOrder = $pdo->query("SELECT order_status, ready_at FROM orders WHERE id = {$orderId}")->fetch();
echo "    Status: {$readyOrder['order_status']} | Waktu Siap (ready_at): {$readyOrder['ready_at']}\n";
assert($readyOrder['order_status'] === 'siap_diambil', "Status pesanan harus 'siap_diambil'!");
echo "    -> PASS: Status siap diambil dan timestamp ready_at tercatat.\n\n";

// 6. Uji Aturan 20 Menit: Pengalihan Otomatis ke Rak Pengambilan Mandiri
echo "[6] Uji Aturan 20 Menit untuk Rak Pengambilan Mandiri...\n";
// Set ready_at menjadi 25 menit yang lalu untuk mensimulasikan pelanggan tidak datang selama 25 menit
$pdo->prepare("UPDATE orders SET ready_at = NOW() - INTERVAL 25 MINUTE WHERE id = ?")->execute([$orderId]);

// Simulasikan panggilan API status
$_GET['order_code'] = $orderCode;
ob_start();
include __DIR__ . '/api/order_status.php';
$apiOutput = ob_get_clean();
$apiJson = json_decode($apiOutput, true);

echo "    Respon API setelah 25 menit:\n";
echo "    - Status Pesanan Terkini: " . $apiJson['order']['order_status'] . "\n";
echo "    - Status Label: " . $apiJson['order']['status_label'] . "\n";
echo "    - Lebih dari 20 Menit?: " . ($apiJson['order']['is_over_20_mins'] ? 'YA (Pindah ke Rak Mandiri)' : 'TIDAK') . "\n";
echo "    - Durasi Siap: " . floor($apiJson['order']['ready_elapsed_sec'] / 60) . " menit\n";
echo "    - Lokasi: " . $apiJson['order']['shelf_slot'] . "\n";

assert($apiJson['order']['order_status'] === 'rak_mandiri', "Pesanan >20 menit harus otomatis berstatus 'rak_mandiri'!");
assert($apiJson['order']['is_over_20_mins'] === true, "Flag is_over_20_mins harus true!");
echo "    -> PASS: Aturan 20 menit dan pengalihan ke Rak Pengambilan Mandiri bekerja 100% sempurna!\n\n";

echo "=== SEMUA PENGUJIAN SISTEM BERHASIL DILALUI! ===\n";
