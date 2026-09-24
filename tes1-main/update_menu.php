<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$menuItems = [
    [1,  'Salt Bread Kaya', 'roti', 'Salt bread lembut berisi pasta kaya (selai kacang/telur khas Asia) yang gurih manis.', 28000, 20, 'assets/images/salt_bread_kaya.jpg', 12],
    [2,  'Salt Bread Plain', 'roti', 'Salt bread original klasik, renyah di luar lembut di dalam dengan taburan garam laut.', 14000, 25, 'assets/images/salt_bread_plain.jpg', 10],
    [3,  'Salt Bread Cheese', 'roti', 'Salt bread dipenuhi taburan keju parmesan dan cheddar panggang yang harum dan gurih.', 22000, 20, 'assets/images/salt_bread_cheese.jpg', 12],
    [4,  'Salt Bread Kinako Chocolate', 'roti', 'Perpaduan tepung kedelai panggang (kinako) dengan cokelat lembut di dalam salt bread.', 21000, 15, 'assets/images/salt_bread_kinako_chocolate.jpg', 12],
    [5,  'Salt Bread Truffle Egg', 'roti', 'Salt bread premium berisi scrambled egg lembut beraroma truffle hitam yang mewah.', 29000, 10, 'assets/images/salt_bread_truffle_egg.jpg', 15],
    [6,  'Salt Bread Garlic', 'roti', 'Salt bread dengan olesan mentega bawang putih panggang yang harum dan gurih.', 21000, 18, 'assets/images/salt_bread_garlic.jpg', 12],
    [7,  'Sugar Salt Bread', 'roti', 'Salt bread ditaburi gula pasir crispy yang manis kontras dengan gurih roti.', 21000, 20, 'assets/images/sugar_salt_bread.jpg', 12],
    [8,  'Hand Drip Coffee', 'minuman', 'Kopi single origin diseduh manual pour-over, menghasilkan cita rasa bersih dan aromatik.', 48000, 30, 'assets/images/hand_drip_coffee.jpg', 5],
    [9,  'Sweet Japanese Iced Coffee', 'minuman', 'Japanese iced coffee dengan metode flash brew, manis seimbang. Bisa tambah cream +7k.', 49000, 25, 'assets/images/sweet_japanese_iced_coffee.jpg', 5],
    [10, 'Honey Milk Tea', 'minuman', 'Teh susu dengan sentuhan madu alami, lembut manis dan menyegarkan.', 38000, 25, 'assets/images/honey_milk_tea.jpg', 5],
    [11, 'Deep Roast Oolong Milk Tea', 'minuman', 'Oolong dark roast dipadukan susu creamy, menghasilkan rasa kaya dan dalam yang unik.', 38000, 20, 'assets/images/deep_roast_oolong_milk_tea.jpg', 5],
];

$stmtUpdate = $pdo->prepare("UPDATE menu_items SET name = ?, category = ?, description = ?, price = ?, stock = ?, image_url = ?, bake_time_mins = ? WHERE id = ?");
$stmtInsert = $pdo->prepare("INSERT INTO menu_items (id, name, category, description, price, stock, image_url, bake_time_mins) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE id = ?");

foreach ($menuItems as $item) {
    $stmtCheck->execute([$item[0]]);
    if ($stmtCheck->fetchColumn() > 0) {
        $stmtUpdate->execute([$item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[0]]);
    } else {
        $stmtInsert->execute($item);
    }
}
$pdo->exec("DELETE FROM menu_items WHERE id > 11");

$rows = $pdo->query("SELECT id, name, price, stock FROM menu_items ORDER BY id")->fetchAll();
echo "<pre>Menu Updated! " . count($rows) . " items:\n\n";
foreach ($rows as $r) {
    echo sprintf("[%2d] %-35s Rp%s  stok=%d\n", $r['id'], $r['name'], number_format($r['price'],0,',','.'), $r['stock']);
}
echo "</pre><br><a href='index.php'>Buka Menu</a>";
