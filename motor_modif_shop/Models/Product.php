<?php

class Product {
    private $conn;
    private $table = 'products';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all products (exclude deleted)
    public function all($search = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.deleted_at IS NULL
                AND (p.name LIKE ? OR p.code LIKE ? OR p.motor_type LIKE ?)
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssii', $search, $search, $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get trashed products (soft deleted)
    public function getTrashed($search = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.deleted_at IS NOT NULL
                AND (p.name LIKE ? OR p.code LIKE ? OR p.motor_type LIKE ?)
                ORDER BY p.deleted_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssii', $search, $search, $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function count($search = '') {
        $search = "%$search%";
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE deleted_at IS NULL
                AND (name LIKE ? OR code LIKE ? OR motor_type LIKE ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    public function countTrashed($search = '') {
        $search = "%$search%";
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE deleted_at IS NOT NULL
                AND (name LIKE ? OR code LIKE ? OR motor_type LIKE ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    public function find($id) {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.id = ? AND p.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function findTrashed($id) {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.id = ? AND p.deleted_at IS NOT NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $sql = "INSERT INTO {$this->table} 
                (category_id, supplier_id, code, name, brand, description, price, stock, motor_type, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iissssdiss', 
            $data['category_id'],
            $data['supplier_id'],
            $data['code'],
            $data['name'],
            $data['brand'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['motor_type'],
            $data['image']
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Gagal menyimpan data'];
    }
    
    public function update($id, $data) {
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $sql = "UPDATE {$this->table} SET 
                category_id = ?, supplier_id = ?, code = ?, name = ?, 
                brand = ?, description = ?, price = ?, stock = ?, 
                motor_type = ?, image = ?
                WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iissssdissi', 
            $data['category_id'],
            $data['supplier_id'],
            $data['code'],
            $data['name'],
            $data['brand'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['motor_type'],
            $data['image'],
            $id
        );
        
        return ['success' => $stmt->execute()];
    }
    
    // SOFT DELETE - Move to recycle bin
    public function delete($id) {
        // SOFT DELETE tidak perlu cek transaksi karena data tidak benar-benar dihapus
        // Data hanya di-mark sebagai deleted dengan timestamp
        
        // Cek apakah produk ada dan belum dihapus
        $checkProduct = "SELECT id FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($checkProduct);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan atau sudah dihapus'];
        }
        
        // Soft delete - set deleted_at timestamp
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produk berhasil dipindahkan ke Recycle Bin'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus produk'];
    }
    
    // RESTORE from recycle bin
    public function restore($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produk berhasil dikembalikan'];
        }
        
        return ['success' => false, 'message' => 'Gagal mengembalikan produk'];
    }
    
    // RESTORE ALL from recycle bin
    public function restoreAll() {
        // Hitung berapa produk yang akan di-restore
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NOT NULL";
        $result = $this->conn->query($countSql);
        $row = $result->fetch_assoc();
        $total = $row['total'];
        
        if ($total == 0) {
            return ['success' => false, 'message' => 'Tidak ada produk di Recycle Bin'];
        }
        
        // Restore semua produk yang ada di recycle bin
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE deleted_at IS NOT NULL";
        
        if ($this->conn->query($sql)) {
            $restoredCount = $this->conn->affected_rows;
            return ['success' => true, 'message' => "Berhasil mengembalikan $restoredCount produk dari Recycle Bin"];
        }
        
        return ['success' => false, 'message' => 'Gagal mengembalikan produk'];
    }
    
    // PERMANENT DELETE from recycle bin
    public function forceDelete($id) {
        $product = $this->findTrashed($id);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan di Recycle Bin'];
        }
        
        // Check if product is used in transactions (untuk permanent delete)
        $check = "SELECT COUNT(*) as total FROM transaction_details WHERE product_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return ['success' => false, 'message' => 'Produk tidak bisa dihapus permanen karena sudah ada di transaksi historis. Biarkan di Recycle Bin atau gunakan fitur Restore.'];
        }
        
        // Delete image file if exists
        if ($product['image']) {
            $imagePath = 'motor_modif_shop/public/uploads/products/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND deleted_at IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produk berhasil dihapus permanen'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus produk'];
    }
    
    // EMPTY recycle bin - delete all trashed products
    public function emptyTrash() {
        // Get all trashed products
        $sql = "SELECT p.id, p.image FROM {$this->table} p
                LEFT JOIN transaction_details td ON p.id = td.product_id
                WHERE p.deleted_at IS NOT NULL
                GROUP BY p.id
                HAVING COUNT(td.id) = 0";
        
        $result = $this->conn->query($sql);
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        if (empty($products)) {
            return ['success' => false, 'message' => 'Tidak ada produk yang bisa dihapus permanen (semua produk di Recycle Bin masih terhubung dengan transaksi)'];
        }
        
        // Delete image files
        foreach ($products as $product) {
            if ($product['image']) {
                $imagePath = 'motor_modif_shop/public/uploads/products/' . $product['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        
        // Delete products that are not used in transactions
        $sql = "DELETE p FROM {$this->table} p
                LEFT JOIN transaction_details td ON p.id = td.product_id
                WHERE p.deleted_at IS NOT NULL
                GROUP BY p.id
                HAVING COUNT(td.id) = 0";
        
        if ($this->conn->query($sql)) {
            $deletedCount = $this->conn->affected_rows;
            if ($deletedCount > 0) {
                return ['success' => true, 'message' => "Berhasil menghapus $deletedCount produk dari Recycle Bin"];
            } else {
                return ['success' => false, 'message' => 'Semua produk di Recycle Bin masih terhubung dengan transaksi dan tidak bisa dihapus permanen'];
            }
        }
        
        return ['success' => false, 'message' => 'Gagal mengosongkan Recycle Bin'];
    }
    
    private function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['code'])) {
            $errors['code'] = 'Kode produk harus diisi';
        } else {
            $sql = "SELECT id FROM {$this->table} WHERE code = ? AND deleted_at IS NULL";
            if ($id) {
                $sql .= " AND id != ?";
            }
            $stmt = $this->conn->prepare($sql);
            if ($id) {
                $stmt->bind_param('si', $data['code'], $id);
            } else {
                $stmt->bind_param('s', $data['code']);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors['code'] = 'Kode produk sudah digunakan';
            }
        }
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nama produk harus diisi';
        }
        
        if (empty($data['category_id'])) {
            $errors['category_id'] = 'Kategori harus dipilih';
        }
        
        if (empty($data['supplier_id'])) {
            $errors['supplier_id'] = 'Supplier harus dipilih';
        }
        
        if (empty($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'Harga harus diisi dan lebih dari 0';
        }
        
        if (!isset($data['stock']) || $data['stock'] < 0) {
            $errors['stock'] = 'Stok harus diisi dan tidak boleh negatif';
        }
        
        return $errors;
    }
    
    public function updateStock($id, $quantity) {
        $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $quantity, $id);
        return $stmt->execute();
    }
}
?>
