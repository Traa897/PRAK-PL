<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Product.php';
require_once BASE_PATH . 'models/Category.php';
require_once BASE_PATH . 'models/Supplier.php';

class ProductsController extends BaseController {
    private $productModel;
    private $categoryModel;
    private $supplierModel;
    
    public function __construct($db) {
        $this->productModel = new Product($db);
        $this->categoryModel = new Category($db);
        $this->supplierModel = new Supplier($db);
    }
    
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $products = $this->productModel->all($search, $page, $limit);
        $total = $this->productModel->count($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('products/index', [
            'products' => $products,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function create() {
        $categories = $this->categoryModel->all();
        $suppliers = $this->supplierModel->all();
        
        $this->view('products/create', [
            'categories' => $categories,
            'suppliers' => $suppliers
        ]);
    }
    
    public function store() {
        $data = [
            'category_id' => clean($_POST['category_id'] ?? ''),
            'supplier_id' => clean($_POST['supplier_id'] ?? ''),
            'code' => strtoupper(clean($_POST['code'] ?? '')),
            'name' => clean($_POST['name'] ?? ''),
            'brand' => clean($_POST['brand'] ?? ''),
            'description' => clean($_POST['description'] ?? ''),
            'price' => clean($_POST['price'] ?? 0),
            'stock' => clean($_POST['stock'] ?? 0),
            'motor_type' => clean($_POST['motor_type'] ?? ''),
            'image' => ''
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload = uploadImage($_FILES['image']);
            if ($upload['success']) {
                $data['image'] = $upload['filename'];
            } else {
                $this->setFlash('danger', $upload['message']);
                setOld($data);
                $this->redirect('index.php?c=products&a=create');
                return;
            }
        }
        
        $result = $this->productModel->create($data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Produk berhasil ditambahkan');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=products&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=products&a=create');
        }
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->setFlash('danger', 'Produk tidak ditemukan');
            $this->redirect('index.php?c=products&a=index');
            return;
        }
        
        $categories = $this->categoryModel->all();
        $suppliers = $this->supplierModel->all();
        
        $this->view('products/edit', [
            'product' => $product,
            'categories' => $categories,
            'suppliers' => $suppliers
        ]);
    }
    
    public function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->setFlash('danger', 'Produk tidak ditemukan');
            $this->redirect('index.php?c=products&a=index');
            return;
        }
        
        $data = [
            'category_id' => clean($_POST['category_id'] ?? ''),
            'supplier_id' => clean($_POST['supplier_id'] ?? ''),
            'code' => strtoupper(clean($_POST['code'] ?? '')),
            'name' => clean($_POST['name'] ?? ''),
            'brand' => clean($_POST['brand'] ?? ''),
            'description' => clean($_POST['description'] ?? ''),
            'price' => clean($_POST['price'] ?? 0),
            'stock' => clean($_POST['stock'] ?? 0),
            'motor_type' => clean($_POST['motor_type'] ?? ''),
            'image' => $product['image']
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload = uploadImage($_FILES['image']);
            if ($upload['success']) {
                // Delete old image
                $oldImagePath = 'motor_modif_shop/public/uploads/products/' . $product['image'];
                if ($product['image'] && file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $data['image'] = $upload['filename'];
            }
        }
        
        $result = $this->productModel->update($id, $data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Produk berhasil diupdate');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=products&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=products&a=edit&id=' . $id);
        }
    }
    
    public function delete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->productModel->delete($id);
        
        if ($result['success']) {
            $this->setFlash('success', 'Produk berhasil dihapus');
        } else {
            $message = $result['message'] ?? 'Produk gagal dihapus';
            $this->setFlash('danger', $message);
        }
        
        $this->redirect('index.php?c=products&a=index');
    }
}
?>