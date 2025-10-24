<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Transaction.php';
require_once BASE_PATH . 'models/Customer.php';
require_once BASE_PATH . 'models/Product.php';

class TransactionsController extends BaseController {
    private $transactionModel;
    private $customerModel;
    private $productModel;
    
    public function __construct($db) {
        $this->transactionModel = new Transaction($db);
        $this->customerModel = new Customer($db);
        $this->productModel = new Product($db);
    }
    
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $transactions = $this->transactionModel->all($search, $page, $limit);
        $total = $this->transactionModel->count($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('transactions/index', [
            'transactions' => $transactions,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function create() {
        $customers = $this->customerModel->all();
        $products = $this->productModel->all();
        
        $this->view('transactions/create', [
            'customers' => $customers,
            'products' => $products
        ]);
    }
    
    public function store() {
        if (empty($_POST['items'])) {
            $this->setFlash('danger', 'Tidak ada produk yang dipilih');
            $this->redirect('index.php?c=transactions&a=create');
            return;
        }
        
        $items = json_decode($_POST['items'], true);
        
        if (empty($items)) {
            $this->setFlash('danger', 'Data produk tidak valid');
            $this->redirect('index.php?c=transactions&a=create');
            return;
        }
        
        $data = [
            'customer_id' => clean($_POST['customer_id'] ?? ''),
            'transaction_date' => clean($_POST['transaction_date'] ?? date('Y-m-d')),
            'total_amount' => clean($_POST['total_amount'] ?? 0),
            'payment_method' => clean($_POST['payment_method'] ?? 'cash'),
            'status' => 'completed',
            'notes' => clean($_POST['notes'] ?? '')
        ];
        
        $result = $this->transactionModel->create($data, $items);
        
        if ($result['success']) {
            $this->setFlash('success', 'Transaksi berhasil! Kode: ' . $result['code']);
            $this->redirect('index.php?c=transactions&a=detail&id=' . $result['id']);
        } else {
            $this->setFlash('danger', $result['message']);
            $this->redirect('index.php?c=transactions&a=create');
        }
    }
    
    public function detail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $transaction = $this->transactionModel->find($id);
        
        if (!$transaction) {
            $this->setFlash('danger', 'Transaksi tidak ditemukan');
            $this->redirect('index.php?c=transactions&a=index');
            return;
        }
        
        $details = $this->transactionModel->getDetails($id);
        
        $this->view('transactions/detail', [
            'transaction' => $transaction,
            'details' => $details
        ]);
    }
    
    public function delete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->transactionModel->delete($id);
        
        if ($result['success']) {
            $this->setFlash('success', 'Transaksi berhasil dihapus');
        } else {
            $message = $result['message'] ?? 'Transaksi gagal dihapus';
            $this->setFlash('danger', $message);
        }
        
        $this->redirect('index.php?c=transactions&a=index');
    }
    
    public function getProduct() {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $product = $this->productModel->find($id);
        
        if ($product) {
            echo json_encode([
                'success' => true,
                'data' => $product
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }
        exit;
    }
    
    public function print() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $transaction = $this->transactionModel->find($id);
        
        if (!$transaction) {
            $this->setFlash('danger', 'Transaksi tidak ditemukan');
            $this->redirect('index.php?c=transactions&a=index');
            return;
        }
        
        $details = $this->transactionModel->getDetails($id);
        
        include BASE_PATH . 'views/transactions/print.php';
        exit;
    }
}
?>