<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Customer.php';

class CustomersController extends BaseController {
    private $customerModel;
    
    public function __construct($db) {
        $this->customerModel = new Customer($db);
    }
    
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $customers = $this->customerModel->all($search, $page, $limit);
        $total = $this->customerModel->count($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('customers/index', [
            'customers' => $customers,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function create() {
        $this->view('customers/create');
    }
    
    public function store() {
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'email' => clean($_POST['email'] ?? ''),
            'address' => clean($_POST['address'] ?? ''),
            'city' => clean($_POST['city'] ?? '')
        ];
        
        $result = $this->customerModel->create($data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Pelanggan berhasil ditambahkan');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=customers&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=customers&a=create');
        }
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->setFlash('danger', 'Pelanggan tidak ditemukan');
            $this->redirect('index.php?c=customers&a=index');
            return;
        }
        
        $this->view('customers/edit', ['customer' => $customer]);
    }
    
    public function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'email' => clean($_POST['email'] ?? ''),
            'address' => clean($_POST['address'] ?? ''),
            'city' => clean($_POST['city'] ?? '')
        ];
        
        $result = $this->customerModel->update($id, $data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Pelanggan berhasil diupdate');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=customers&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=customers&a=edit&id=' . $id);
        }
    }
    
    public function delete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->customerModel->delete($id);
        
        if ($result['success']) {
            $this->setFlash('success', 'Pelanggan berhasil dihapus');
        } else {
            $message = $result['message'] ?? 'Pelanggan gagal dihapus';
            $this->setFlash('danger', $message);
        }
        
        $this->redirect('index.php?c=customers&a=index');
    }
}
?>