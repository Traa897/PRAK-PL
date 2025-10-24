<?php

require_once BASE_PATH . 'controllers/BaseController.php';
require_once BASE_PATH . 'models/Category.php';

class CategoriesController extends BaseController {
    private $categoryModel;
    
    public function __construct($db) {
        $this->categoryModel = new Category($db);
    }
    
    public function index() {
        $search = isset($_GET['search']) ? clean($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $categories = $this->categoryModel->all($search, $page, $limit);
        $total = $this->categoryModel->count($search);
        $totalPages = ceil($total / $limit);
        
        $this->view('categories/index', [
            'categories' => $categories,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    public function create() {
        $this->view('categories/create');
    }
    
    public function store() {
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'description' => clean($_POST['description'] ?? '')
        ];
        
        $result = $this->categoryModel->create($data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Kategori berhasil ditambahkan');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=categories&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=categories&a=create');
        }
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $this->setFlash('danger', 'Kategori tidak ditemukan');
            $this->redirect('index.php?c=categories&a=index');
            return;
        }
        
        $this->view('categories/edit', ['category' => $category]);
    }
    
    public function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        $data = [
            'name' => clean($_POST['name'] ?? ''),
            'description' => clean($_POST['description'] ?? '')
        ];
        
        $result = $this->categoryModel->update($id, $data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Kategori berhasil diupdate');
            clearOld();
            clearErrors();
            $this->redirect('index.php?c=categories&a=index');
        } else {
            if (isset($result['errors'])) {
                setErrors($result['errors']);
            }
            setOld($data);
            $this->redirect('index.php?c=categories&a=edit&id=' . $id);
        }
    }
    
    public function delete() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = $this->categoryModel->delete($id);
        
        if ($result['success']) {
            $this->setFlash('success', 'Kategori berhasil dihapus');
        } else {
            $message = $result['message'] ?? 'Kategori gagal dihapus';
            $this->setFlash('danger', $message);
        }
        
        $this->redirect('index.php?c=categories&a=index');
    }
}
?>