<?php


class Customer {
    private $conn;
    private $table = 'customers';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function all($search = '', $page = 1, $limit = 10) {
        if ($search === '' && $page === 1 && $limit === 10) {
            $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?
                ORDER BY name ASC
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
                WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
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
        
        $sql = "INSERT INTO {$this->table} (name, phone, email, address, city) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssss', 
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['city']
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
                name = ?, phone = ?, email = ?, address = ?, city = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssi', 
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['city'],
            $id
        );
                    
        return ['success' => $stmt->execute()];
    }
    
    public function delete($id) {
        $check = "SELECT COUNT(*) as total FROM transaksi WHERE customer_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return ['success' => false, 'message' => 'Pelanggan tidak bisa dihapus karena memiliki transaksi'];
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        return ['success' => $stmt->execute()];
    }
    
    
    private function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nama pelanggan harus diisi';
        }
        
        if (empty($data['phone'])) {
            $errors['phone'] = 'Nomor telepon harus diisi';
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        }
        
        return $errors;
    }
}
?>