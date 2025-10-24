<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Supplier.php';

class SuppliersController extends BaseController {
    private $supplierModel;
    
    public function __construct($db) {
        $this->supplierModel = new Supplier($db);
    }
    
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $suppliers = $this->supplierModel->all($search, $page, $limit);
        $total = $this->supplierModel->count($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('suppliers/index', [
            'suppliers' => $suppliers,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function create() {
        $this->view('suppliers/create');
    }
    
    public function store() {
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'contact_person' => clean($_POST['contact_person'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'email' => clean($_POST['email'] ?? ''),
            'address' => clean($_POST['address'] ?? ''),
            'city' => clean($_POST['city'] ?? '')
        ];
        
        $result = $this->supplierModel->create($data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Supplier berhasil ditambahkan');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=suppliers&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=suppliers&a=create');
        }
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $supplier = $this->supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('danger', 'Supplier tidak ditemukan');
            $this->redirect('index.php?c=suppliers&a=index');
            return;
        }
        
        $this->view('suppliers/edit', ['supplier' => $supplier]);
    }
    
    public function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'contact_person' => clean($_POST['contact_person'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'email' => clean($_POST['email'] ?? ''),
            'address' => clean($_POST['address'] ?? ''),
            'city' => clean($_POST['city'] ?? '')
        ];
        
        $result = $this->supplierModel->update($id, $data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Supplier berhasil diupdate');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=suppliers&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=suppliers&a=edit&id=' . $id);
        }
    }
    
    public function delete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->supplierModel->delete($id);
        
        if ($result['success']) {
            $this->setFlash('success', 'Supplier berhasil dihapus');
        } else {
            $message = $result['message'] ?? 'Supplier gagal dihapus';
            $this->setFlash('danger', $message);
        }
        
        $this->redirect('index.php?c=suppliers&a=index');
    }
}
?>