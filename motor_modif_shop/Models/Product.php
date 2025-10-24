<?php

class Product {
    private $conn;
    private $table = 'products';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function all($search = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.name LIKE ? OR p.code LIKE ? OR p.motor_type LIKE ?
                ORDER BY p.created_at DESC
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
                WHERE name LIKE ? OR code LIKE ? OR motor_type LIKE ?";
        
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
                WHERE p.id = ?";
        
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
                WHERE id = ?";
        
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
    
    public function delete($id) {
        $check = "SELECT COUNT(*) as total FROM transaction_details WHERE product_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return ['success' => false, 'message' => 'Produk tidak bisa dihapus karena sudah ada di transaksi'];
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        return ['success' => $stmt->execute()];
    }
    
    private function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['code'])) {
            $errors['code'] = 'Kode produk harus diisi';
        } else {
            $sql = "SELECT id FROM {$this->table} WHERE code = ?";
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
        $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $quantity, $id);
        return $stmt->execute();
    }
}
?>