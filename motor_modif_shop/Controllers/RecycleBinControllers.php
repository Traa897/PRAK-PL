<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Product.php';

class RecycleBinController extends BaseController {
    private $productModel;
    
    public function __construct($db) {
        $this->productModel = new Product($db);
    }
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $products = $this->productModel->getTrashed($search, $page, $limit);
        $total = $this->productModel->countTrashed($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('recyclebin/index', [
            'products' => $products,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
        public function restore() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->productModel->restore($id);
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('danger', $result['message']);
        }
        
        $this->redirect('index.php?c=recyclebin&a=index');
    }
    
    // Restore ALL products from recycle bin
    public function restoreAll() {
        $result = $this->productModel->restoreAll();
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('danger', $result['message']);
        }
        
        $this->redirect('index.php?c=recyclebin&a=index');
    }
    
    // Permanent delete product
    public function forceDelete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->productModel->forceDelete($id);
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('danger', $result['message']);
        }
        
        $this->redirect('index.php?c=recyclebin&a=index');
    }
    
    // Empty entire recycle bin
    public function empty() {
        $result = $this->productModel->emptyTrash();
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('danger', $result['message']);
        }
        
        $this->redirect('index.php?c=recyclebin&a=index');
    }
}
?>
