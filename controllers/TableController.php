<?php
require_once __DIR__ . '/../config/database.php';

class TableController {
    public static function getAll() {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM tables WHERE is_active = 1 ORDER BY zone, table_number");
        $stmt->execute();
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
    
    public static function getAvailable() {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM tables WHERE is_active = 1 AND status = 'available' ORDER BY zone, table_number");
        $stmt->execute();
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
    
    public static function getById($id) {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM tables WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $table = $stmt->fetch();
        
        if (!$table) {
            throw new Exception("Meja tidak ditemukan.");
        }
        
        return ["success" => true, "data" => $table];
    }
    
    public static function updateStatus($id, $status) {
        $db = getDb();
        $validStatuses = ['available', 'occupied', 'reserved', 'maintenance'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status meja tidak valid.");
        }
        
        $stmt = $db->prepare("UPDATE tables SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        return ["success" => true, "message" => "Status meja berhasil diupdate."];
    }
    
    public static function create($data) {
        $db = getDb();
        $stmt = $db->prepare("INSERT INTO tables (table_number, capacity, zone, status, pos_x, pos_y) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['table_number'],
            $data['capacity'] ?? 2,
            $data['zone'] ?? 'main',
            $data['status'] ?? 'available',
            $data['pos_x'] ?? 0,
            $data['pos_y'] ?? 0
        ]);
        
        return ["success" => true, "message" => "Meja berhasil ditambahkan.", "id" => $db->lastInsertId()];
    }
    
    public static function update($id, $data) {
        $db = getDb();
        $stmt = $db->prepare("UPDATE tables SET table_number = ?, capacity = ?, zone = ?, status = ?, pos_x = ?, pos_y = ? WHERE id = ?");
        $stmt->execute([
            $data['table_number'],
            $data['capacity'] ?? 2,
            $data['zone'] ?? 'main',
            $data['status'] ?? 'available',
            $data['pos_x'] ?? 0,
            $data['pos_y'] ?? 0,
            $id
        ]);
        
        return ["success" => true, "message" => "Meja berhasil diupdate."];
    }
    
    public static function delete($id) {
        $db = getDb();
        $stmt = $db->prepare("UPDATE tables SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        return ["success" => true, "message" => "Meja berhasil dihapus."];
    }
    
    public static function getLayout() {
        $db = getDb();
        $stmt = $db->prepare("SELECT id, table_number, capacity, zone, status, pos_x, pos_y FROM tables WHERE is_active = 1 ORDER BY zone, table_number");
        $stmt->execute();
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
}
