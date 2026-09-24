<?php
require_once __DIR__ . '/../config/database.php';

class OrderController {
    public static function calculateDiscount($memberId) {
        $db = getDb();
        if (!$memberId) return 0;
        
        $stmt = $db->prepare("SELECT tier FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();
        
        if (!$member) return 0;
        
        $tier = $member['tier'] ?? 'regular';
        
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tier_discounts'");
        $stmt->execute();
        $setting = $stmt->fetch();
        
        $discounts = ['regular' => 0, 'member' => 5, 'vip' => 10];
        if ($setting && $setting['setting_value']) {
            $parsed = json_decode($setting['setting_value'], true);
            if (is_array($parsed)) {
                $discounts = array_merge($discounts, $parsed);
            }
        }
        
        return $discounts[$tier] ?? 0;
    }
    
    public static function create($data) {
        $db = getDb();
        try {
            $db->beginTransaction();
            
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Keranjang belanja kosong.");
            }
            
            $subtotal = 0;
            $totalBakeTime = 0;
            
            foreach ($items as &$item) {
                $stmt = $db->prepare("SELECT name, price, stock, is_available, bake_time_mins FROM products WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    throw new Exception("Produk tidak ditemukan.");
                }
                
                if (isset($product['is_available']) && (int)$product['is_available'] === 0) {
                    throw new Exception("Menu '{$product['name']}' sedang tidak tersedia (habis).");
                }
                
                if ($product['stock'] < $item['quantity']) {
                    throw new Exception("Ketersediaan menu '{$product['name']}' tidak mencukupi untuk jumlah pesanan.");
                }
                
                $newStock = $product['stock'] - $item['quantity'];
                $newAvailable = $newStock > 0 ? 1 : 0;
                $stmt = $db->prepare("UPDATE products SET stock = ?, is_available = ? WHERE id = ?");
                $stmt->execute([$newStock, $newAvailable, $item['product_id']]);
                
                $item['item_name'] = $product['name'];
                $item['price'] = $product['price'];
                $item['subtotal'] = $product['price'] * $item['quantity'];
                $subtotal += $item['subtotal'];
                $totalBakeTime = max($totalBakeTime, $product['bake_time_mins']);
            }
            unset($item);
            
            $memberId = $data['member_id'] ?? null;
            $discountPercent = self::calculateDiscount($memberId);
            $discountAmount = ($subtotal * $discountPercent) / 100;
            
            // Tax and Service logic
            $taxRate = 11;
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tax_rate'");
            $stmt->execute();
            if ($row = $stmt->fetch()) $taxRate = (float)$row['setting_value'];
            
            $taxAmount = (($subtotal - $discountAmount) * $taxRate) / 100;
            
            $serviceCharge = 0;
            $orderType = $data['order_type'] ?? 'takeaway';
            if ($orderType === 'dine_in') {
                $serviceRate = 5;
                $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'service_charge'");
                $stmt->execute();
                if ($row = $stmt->fetch()) $serviceRate = (float)$row['setting_value'];
                
                $serviceCharge = (($subtotal - $discountAmount) * $serviceRate) / 100;
            }
            
            $total = $subtotal - $discountAmount + $taxAmount + $serviceCharge;
            
            $queueNumber = generateQueueNumber();
            $orderCode = generateOrderCode();
            
            $stmt = $db->prepare("SELECT COUNT(*) as queue_count FROM orders WHERE order_status IN ('pending', 'processing')");
            $stmt->execute();
            $queueCount = $stmt->fetch()['queue_count'] ?? 0;
            $estimatedMinutes = $totalBakeTime + ($queueCount * 2);
            
            $stmt = $db->prepare("INSERT INTO orders (
                order_code, queue_number, member_id, customer_name, customer_phone, customer_email, 
                order_type, table_id, payment_method, subtotal, discount_amount, tax_amount, service_charge, total_amount, 
                notes, estimated_minutes, order_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $orderCode, $queueNumber, $memberId, $data['customer_name'] ?? null, $data['customer_phone'] ?? null, $data['customer_email'] ?? null,
                $orderType, $data['table_id'] ?? null, $data['payment_method'] ?? 'cash',
                $subtotal, $discountAmount, $taxAmount, $serviceCharge, $total,
                $data['notes'] ?? null, $estimatedMinutes, 'pending'
            ]);
            
            $orderId = (int) $db->lastInsertId();
            
            foreach ($items as $it) {
                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, item_name, quantity, price, subtotal, options_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $it['product_id'], $it['item_name'], $it['quantity'], $it['price'], $it['subtotal'], json_encode($it['options'] ?? [])]);
            }
            
            if (!empty($data['table_id']) && $orderType === 'dine_in') {
                $stmt = $db->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
                $stmt->execute([$data['table_id']]);
            }
            
            $db->commit();
            
            $returnData = [
                "id" => $orderId,
                "order_id" => $orderId,
                "order_code" => $orderCode,
                "queue_number" => $queueNumber,
                "total_amount" => $total
            ];
            
            return [
                "success" => true,
                "message" => "Pesanan berhasil dibuat.",
                "data" => $returnData,
                "order_code" => $orderCode,
                "order_id" => $orderId,
                "queue_number" => $queueNumber
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    public static function getByCode($orderCode) {
        try {
            $db = getDb();
            $orderCode = trim($orderCode);
            $stmt = $db->prepare("SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.order_code = ? OR o.queue_number = ? ORDER BY (o.order_code = ?) DESC, o.id DESC LIMIT 1");
            $stmt->execute([$orderCode, $orderCode, $orderCode]);
            $order = $stmt->fetch();
            
            if (!$order) {
                return ["success" => false, "message" => "Pesanan tidak ditemukan."];
            }
            
            $stmt = $db->prepare("SELECT oi.*, p.name as product_name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
            
            return ["success" => true, "data" => $order];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    public static function getById($id) {
        try {
            $db = getDb();
            $stmt = $db->prepare("SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                return ["success" => false, "message" => "Pesanan tidak ditemukan."];
            }
            
            $stmt = $db->prepare("SELECT oi.*, p.name as product_name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
            
            return ["success" => true, "data" => $order];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    public static function getHistory($memberId, $page = 1, $perPage = 10) {
        $db = getDb();
        $offset = ($page - 1) * $perPage;
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE member_id = ?");
        $stmt->execute([$memberId]);
        $total = $stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT * FROM orders WHERE member_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $memberId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        return [
            "success" => true, 
            "data" => $orders,
            "meta" => [
                "total" => $total,
                "page" => $page,
                "per_page" => $perPage,
                "total_pages" => ceil($total / $perPage)
            ]
        ];
    }
    
    public static function getLiveQueue() {
        $db = getDb();
        $stmt = $db->prepare("SELECT o.id, o.order_code, o.queue_number, o.order_status, o.payment_status, o.payment_method, o.customer_name, o.customer_phone, o.customer_email, o.order_type, o.subtotal, o.total_amount, o.notes, o.estimated_minutes, o.ready_at, o.shelf_slot, o.created_at, t.table_number 
                              FROM orders o 
                              LEFT JOIN tables t ON o.table_id = t.id
                              WHERE o.order_status IN ('pending', 'confirmed', 'processing', 'ready', 'shelf') 
                              ORDER BY (DATE(o.created_at) = CURDATE()) DESC, o.id DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        $grouped = [
            'pending' => [],
            'processing' => [],
            'ready' => [],
            'shelf' => []
        ];
        
        foreach ($orders as &$order) {
            $stmtItems = $db->prepare("SELECT oi.id, oi.item_name, oi.quantity, oi.price, oi.subtotal, oi.options_json FROM order_items oi WHERE oi.order_id = ?");
            $stmtItems->execute([$order['id']]);
            $order['items'] = $stmtItems->fetchAll();
            
            $statusGroup = ($order['order_status'] === 'confirmed') ? 'pending' : $order['order_status'];
            if (isset($grouped[$statusGroup])) {
                $grouped[$statusGroup][] = $order;
            }
        }
        unset($order);
        
        return [
            "success" => true, 
            "data" => [
                "queue" => $grouped,
                "orders" => $orders,
                "counts" => [
                    "pending" => count($grouped['pending']),
                    "processing" => count($grouped['processing']),
                    "ready" => count($grouped['ready']),
                    "shelf" => count($grouped['shelf']),
                    "total" => count($orders)
                ]
            ]
        ];
    }
    
    public static function updateStatus($id, $newStatus, $extraData = []) {
        $db = getDb();
        $validStatuses = ['pending', 'confirmed', 'processing', 'ready', 'shelf', 'completed', 'cancelled'];
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception("Status tidak valid: " . $newStatus);
        }
        
        $stmt = $db->prepare("SELECT order_status, payment_status, table_id FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception("Pesanan tidak ditemukan.");
        }
        
        $query = "UPDATE orders SET order_status = ?, updated_at = NOW()";
        $params = [$newStatus];

        if (!empty($extraData['payment_status'])) {
            $query .= ", payment_status = ?";
            $params[] = $extraData['payment_status'];
        } elseif ($newStatus === 'confirmed' && $order['payment_status'] !== 'paid') {
            $query .= ", payment_status = 'paid'";
        }
        
        if ($newStatus === 'ready') {
            $query .= ", ready_at = NOW()";
        } else if ($newStatus === 'shelf') {
            $shelfSlot = $extraData['shelf_slot'] ?? 'Rak Mandiri A-01';
            $query .= ", shelf_slot = ?";
            $params[] = $shelfSlot;
        } else if ($newStatus === 'completed') {
            $query .= ", picked_up_at = NOW()";
            if ($order['table_id']) {
                $stmtTable = $db->prepare("UPDATE tables SET status = 'available' WHERE id = ?");
                $stmtTable->execute([$order['table_id']]);
            }
        } else if ($newStatus === 'cancelled') {
            if ($order['table_id']) {
                $stmtTable = $db->prepare("UPDATE tables SET status = 'available' WHERE id = ?");
                $stmtTable->execute([$order['table_id']]);
            }
        }
        
        $query .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return ["success" => true, "message" => "Status pesanan berhasil diperbarui."];
    }
    
    public static function getStats() {
        $db = getDb();
        $today = date('Y-m-d');
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = ? AND order_status != 'cancelled'");
        $stmt->execute([$today]);
        $ordersCount = $stmt->fetch()['count'];
        
        $stmt = $db->prepare("SELECT SUM(total_amount) as revenue FROM orders WHERE DATE(created_at) = ? AND order_status IN ('completed')");
        $stmt->execute([$today]);
        $revenue = $stmt->fetch()['revenue'] ?? 0;
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'processing', 'ready')");
        $stmt->execute();
        $activeQueue = $stmt->fetch()['count'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM members");
        $stmt->execute();
        $totalMembers = $stmt->fetch()['count'];
        
        $stmt = $db->prepare("SELECT p.name, SUM(oi.quantity) as sold 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              JOIN orders o ON oi.order_id = o.id 
                              WHERE DATE(o.created_at) = ? AND o.order_status = 'completed' 
                              GROUP BY p.id 
                              ORDER BY sold DESC LIMIT 5");
        $stmt->execute([$today]);
        $topProducts = $stmt->fetchAll();
        
        return [
            "success" => true,
            "data" => [
                "today_orders" => $ordersCount,
                "today_revenue" => $revenue,
                "active_queue" => $activeQueue,
                "total_members" => $totalMembers,
                "top_products" => $topProducts
            ]
        ];
    }
}
