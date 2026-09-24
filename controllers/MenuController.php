<?php
require_once __DIR__ . '/../config/database.php';

class MenuController {
    public static function getAll($categorySlug = null, $search = null) {
        $db = getDb();
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1";
        $params = [];
        
        if ($categorySlug) {
            $query .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }
        
        if ($search) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $query .= " ORDER BY c.sort_order, p.name";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
    
    public static function getFeatured() {
        $db = getDb();
        $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.is_active = 1 AND p.is_featured = 1 
                              ORDER BY p.name");
        $stmt->execute();
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
    
    public static function getById($id) {
        $db = getDb();
        $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.id = ? AND p.is_active = 1");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            throw new Exception("Produk tidak ditemukan.");
        }
        
        return ["success" => true, "data" => $product];
    }
    
    public static function getCategories() {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");
        $stmt->execute();
        return ["success" => true, "data" => $stmt->fetchAll()];
    }
    
    public static function create($data) {
        $db = getDb();
        $isAvailable = isset($data['is_available']) ? (int)$data['is_available'] : 1;
        $stock = isset($data['stock']) ? (int)$data['stock'] : ($isAvailable ? 50 : 0);

        $slug = !empty($data['slug']) ? $data['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        if (empty($slug)) {
            $slug = 'menu-' . time();
        }
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $stmtCheck->execute([$slug]);
        if ($stmtCheck->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        $stmt = $db->prepare("INSERT INTO products (name, slug, category_id, description, price, stock, image_url, bake_time_mins, is_featured, is_available, options_json) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'], 
            $slug,
            $data['category_id'], 
            $data['description'] ?? '', 
            $data['price'], 
            $stock, 
            $data['image_url'] ?? '', 
            $data['bake_time_mins'] ?? 0, 
            $data['is_featured'] ?? 0,
            $isAvailable,
            $data['options_json'] ?? '[]'
        ]);
        
        return ["success" => true, "message" => "Produk berhasil ditambahkan.", "id" => $db->lastInsertId()];
    }
    
    public static function update($id, $data) {
        $db = getDb();
        $isAvailable = isset($data['is_available']) ? (int)$data['is_available'] : 1;
        $stock = isset($data['stock']) ? (int)$data['stock'] : ($isAvailable ? 50 : 0);

        $slug = !empty($data['slug']) ? $data['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        if (empty($slug)) {
            $slug = 'menu-' . $id;
        }
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
        $stmtCheck->execute([$slug, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        $stmt = $db->prepare("UPDATE products SET name = ?, slug = ?, category_id = ?, description = ?, price = ?, stock = ?, image_url = ?, bake_time_mins = ?, is_featured = ?, is_available = ?, options_json = ? WHERE id = ?");
        $stmt->execute([
            $data['name'], 
            $slug,
            $data['category_id'], 
            $data['description'] ?? '', 
            $data['price'], 
            $stock, 
            $data['image_url'] ?? '', 
            $data['bake_time_mins'] ?? 0, 
            $data['is_featured'] ?? 0,
            $isAvailable,
            $data['options_json'] ?? '[]',
            $id
        ]);
        
        return ["success" => true, "message" => "Produk berhasil diupdate."];
    }
    
    public static function delete($id) {
        $db = getDb();
        $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        return ["success" => true, "message" => "Produk berhasil dihapus."];
    }
    
    public static function toggleAvailability($id, $targetStatus = null) {
        $db = getDb();
        $stmt = $db->prepare("SELECT id, name, is_available, stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            throw new Exception("Produk tidak ditemukan.");
        }
        
        $newStatus = ($targetStatus !== null) ? (int)$targetStatus : ($product['is_available'] ? 0 : 1);
        
        if ($newStatus === 1) {
            $newStock = $product['stock'] <= 0 ? 50 : $product['stock'];
            $upd = $db->prepare("UPDATE products SET is_available = 1, stock = ? WHERE id = ?");
            $upd->execute([$newStock, $id]);
        } else {
            $upd = $db->prepare("UPDATE products SET is_available = 0, stock = 0 WHERE id = ?");
            $upd->execute([$id]);
            $newStock = 0;
        }
        
        $statusText = $newStatus === 1 ? 'Tersedia' : 'Tidak Tersedia';
        return [
            "success" => true,
            "message" => "Status menu '{$product['name']}' diubah menjadi {$statusText}.",
            "is_available" => $newStatus,
            "stock" => $newStock
        ];
    }
    
    public static function adjustStock($id, $delta) {
        $db = getDb();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT stock, is_available FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Produk tidak ditemukan.");
            }
            
            $newStock = max(0, $product['stock'] + $delta);
            $newAvailable = $newStock > 0 ? 1 : 0;
            
            $stmt = $db->prepare("UPDATE products SET stock = ?, is_available = ? WHERE id = ?");
            $stmt->execute([$newStock, $newAvailable, $id]);
            
            $db->commit();
            return [
                "success" => true, 
                "message" => "Stok berhasil diupdate.", 
                "new_stock" => $newStock,
                "is_available" => $newAvailable
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    public static function handleImageUpload($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Parameter file tidak valid.');
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('Tidak ada file yang dikirim.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Ukuran file melebihi batas.');
            default:
                throw new Exception('Kesalahan tidak diketahui.');
        }
        
        if ($file['size'] > 2097152) { // 2MB
            throw new Exception('Ukuran file maksimal 2MB.');
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $ext = array_search(
            $finfo->file($file['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ),
            true
        );
        
        if (false === $ext) {
            throw new Exception('Format file tidak valid. Gunakan JPG, PNG, atau WEBP.');
        }
        
        $filename = sprintf('%s.%s', sha1_file($file['tmp_name']) . '_' . time(), $ext);
        $uploadPath = __DIR__ . '/../assets/img/products/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            throw new Exception('Gagal memindahkan file yang diunggah.');
        }
        
        return ["success" => true, "image_url" => 'assets/img/products/' . $filename];
    }
}
