# KONEKSI DATABASE & CRUDMVC

NAMA : Patra Ananda          
NIM : 10241061           
Mata Kuliah : Pemrograman Lanjut A

---

## I. Tujuan Praktikum

- Memahami konsep koneksi database menggunakan mysqli di PHP.
- Mampu membuat koneksi database secara aman menggunakan pattern singleton.
- Mengimplementasikan operasi CRUD (Create, Read, Update, Delete) dengan prepared statement untuk keamanan aplikasi.
- Menggunakan konsep MVC untuk memisahkan logika bisnis (Model), tampilan (View), dan kontrol alur aplikasi (Controller).
- Membuat aplikasi web dengan fitur CRUD yang mencakup input validasi, list data dengan pagination, pencarian data, serta operasi update dan delete.
- Mampu menggunakan flash message untuk memberikan feedback kepada pengguna mengenai status aksi (berhasil/gagal).
- Membuat form input dan edit yang interaktif dan mengelola data pasien sebagai contoh kasus.
---

## II. Dasar Teori (Ringkas)

"Praktikum ini bertujuan untuk memahami cara membuat koneksi database menggunakan mysqli di PHP serta mengimplementasikan operasi CRUD dengan konsep MVC. Melalui praktikum ini, peserta dapat menguasai teknik validasi input, penggunaan prepared statement untuk keamanan, serta penerapan pagination dan pencarian data untuk membangun aplikasi web yang terstruktur dan interaktif."

---

## III. Langkah-Langkah Praktikum

Catatan: Kita menggunakan folder `Week5` `C:\xampp\htdocs\PL\Prak PL\Week8\motor_modif_shop`.

### **A. Buat Struktur Proyek.**

Buat struktur direktori berikut:

```
motor_modif_shop/
│
├── config/
│   └── database.php
│
├── controllers/
│   ├── BaseController.php
│   ├── SuppliersController.php
│   ├── CategoriesController.php
│   ├── ProductsController.php
│   ├── CustomersController.php
│   └── TransactionsController.php
│
├── models/
│   ├── Supplier.php
│   ├── Category.php
│   ├── Product.php
│   ├── Customer.php
│   └── Transaction.php
│
├── views/
│   ├── layouts/
│   │   ├── header.php
│   │   └── footer.php
│   │
│   ├── suppliers/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   │
│   ├── categories/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   │
│   ├── products/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   │
│   ├── customers/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   │
│   ├── transactions/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── detail.php
│   │   └── print.php
│   │
│   └── dashboard.php
│
├── public/
│   ├── css/
│   │   └── style.css
│   └── uploads/
│       └── products/
│
├── helpers/
│   └── functions.php
│
└── index.php
```

---


### **B. Buat menghubungka database (`config/database.php`).**

```php
<?php

class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'motor_modif_shop';
    private $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Koneksi database gagal: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
```
-----

### **C. Buat Controller (`contorllers/BaseController.php`).**

```php
<?php

class BaseController {
    
    protected function view($view, $data = []) {
        extract($data);
        
        $flash = getFlash();
        
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        include $basePath . 'views/layouts/header.php';
        include $basePath . 'views/' . $view . '.php';
        include $basePath . 'views/layouts/footer.php';
    }
    
    protected function redirect($url) {
        redirect($url);
    }
    
    protected function setFlash($type, $message) {
        setFlash($type, $message);
    }
}
?>
```

---

### **D. Buat Controller (`Controllers/SuppliersController.php`).**

```php
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
```

---
### **E. Buat Controllers (`Controllers/CategoriesController`).**

```php
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
```

----
### **F. Buat Controllers. (`Controllers/ProductsController.php `)**

```php
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
```
----


## **G. Buat Controllers. (`Controllers/CustomersController.php`)**
```php
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
```
## **H. Buat Controllers. (`Controllers/TransactionsController.php`)**
```php
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
```

## **I. Buat Models. (`Models/Supplier.php`)**

```php
<?php

class Supplier {
    private $conn;
    private $table = 'suppliers';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function all($search = '', $page = 1, $limit = 10) {
        if ($search === '' && $page === 1 && $limit === 10) {
            // Simple query for dropdown
            $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR contact_person LIKE ? OR phone LIKE ?
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
                WHERE name LIKE ? OR contact_person LIKE ? OR phone LIKE ?";
        
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
        
        $sql = "INSERT INTO {$this->table} (name, contact_person, phone, email, address, city) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssss', 
            $data['name'],
            $data['contact_person'],
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
                name = ?, contact_person = ?, phone = ?, 
                email = ?, address = ?, city = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssssi', 
            $data['name'],
            $data['contact_person'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['city'],
            $id
        );
        
        return ['success' => $stmt->execute()];
    }
    
    public function delete($id) {
        $check = "SELECT COUNT(*) as total FROM products WHERE supplier_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return ['success' => false, 'message' => 'Supplier tidak bisa dihapus karena masih digunakan'];
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        return ['success' => $stmt->execute()];
    }
    
    private function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nama supplier harus diisi';
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
```

----

## **J. Buat Models. (`Models/Category.php`)**
```php
<?php
class Category {
    private $conn;
    private $table = 'categories';
    
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
                WHERE name LIKE ? OR description LIKE ?
                ORDER BY name ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $search, $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function count($search = '') {
        $search = "%$search%";
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE name LIKE ? OR description LIKE ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $search, $search);
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
        
        $sql = "INSERT INTO {$this->table} (name, description) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $data['name'], $data['description']);
        
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
        
        $sql = "UPDATE {$this->table} SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $data['name'], $data['description'], $id);
        
        return ['success' => $stmt->execute()];
    }
    
    public function delete($id) {
        $check = "SELECT COUNT(*) as total FROM products WHERE category_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return ['success' => false, 'message' => 'Kategori tidak bisa dihapus karena masih digunakan'];
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        return ['success' => $stmt->execute()];
    }
    
    private function validate($data, $id = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nama kategori harus diisi';
        }
        
        return $errors;
    }
}
?>
```
---

## **K. Buat Models (`Models/Product.php`)**
```php
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
```
---
## **L. Buat Models. (`Models/Customer.php`)**
```php
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
```
---
## **M. Buat Models. (`Models/Transaction.php`)**

```php
<?php

class Transaction {
    private $conn;
    private $table = 'transaksi';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function all($search = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $search = "%$search%";
        
        $sql = "SELECT t.*, c.name as customer_name, c.phone as customer_phone
                FROM {$this->table} t
                LEFT JOIN customers c ON t.customer_id = c.id
                WHERE t.transaction_code LIKE ? OR c.name LIKE ?
                ORDER BY t.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $search, $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function count($search = '') {
        $search = "%$search%";
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} t
                LEFT JOIN customers c ON t.customer_id = c.id
                WHERE t.transaction_code LIKE ? OR c.name LIKE ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    public function find($id) {
        $sql = "SELECT t.*, c.name as customer_name, c.email, c.phone, c.address
                FROM {$this->table} t
                LEFT JOIN customers c ON t.customer_id = c.id
                WHERE t.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function getDetails($transactionId) {
        $sql = "SELECT td.*, p.name as product_name, p.code as product_code
                FROM transaction_details td
                LEFT JOIN products p ON td.product_id = p.id
                WHERE td.transaction_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function create($data, $items) {
        $this->conn->begin_transaction();
        
        try {
            $transactionCode = generateTransactionCode();
            
            $sql = "INSERT INTO {$this->table} 
                    (customer_id, transaction_code, transaction_date, total_amount, payment_method, status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('issdsss', 
                $data['customer_id'],
                $transactionCode,
                $data['transaction_date'],
                $data['total_amount'],
                $data['payment_method'],
                $data['status'],
                $data['notes']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal menyimpan transaksi');
            }
            
            $transactionId = $this->conn->insert_id;
            
            $sqlDetail = "INSERT INTO transaction_details 
                          (transaction_id, product_id, quantity, price, subtotal) 
                          VALUES (?, ?, ?, ?, ?)";
            
            $stmtDetail = $this->conn->prepare($sqlDetail);
            
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                
                $stmtDetail->bind_param('iiidd', 
                    $transactionId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                );
                
                if (!$stmtDetail->execute()) {
                    throw new Exception('Gagal menyimpan detail transaksi');
                }
                
                $sqlUpdateStock = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmtStock = $this->conn->prepare($sqlUpdateStock);
                $stmtStock->bind_param('ii', $item['quantity'], $item['product_id']);
                
                if (!$stmtStock->execute()) {
                    throw new Exception('Gagal update stok produk');
                }
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'id' => $transactionId, 'code' => $transactionCode];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $status, $id);
        
        return ['success' => $stmt->execute()];
    }
    
    public function delete($id) {
        $transaction = $this->find($id);
        
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaksi tidak ditemukan'];
        }
        
        if ($transaction['status'] != 'pending') {
            return ['success' => false, 'message' => 'Hanya transaksi pending yang bisa dihapus'];
        }
        
        $this->conn->begin_transaction();
        
        try {
            $details = $this->getDetails($id);
            
            foreach ($details as $detail) {
                $sqlRestore = "UPDATE products SET stock = stock + ? WHERE id = ?";
                $stmtRestore = $this->conn->prepare($sqlRestore);
                $stmtRestore->bind_param('ii', $detail['quantity'], $detail['product_id']);
                $stmtRestore->execute();
            }
            
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $this->conn->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
```
---
# Menampilkan Data Untuk User 

---

## **N. Buat Layouts (`Views/Layouts/header.php`)**
```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patra Jaya Variasi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #212529;
            padding: 20px 0;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }
        .sidebar i {
            width: 25px;
        }
        .content-wrapper {
            padding: 20px;
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card {
            border-left: 4px solid;
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                Patra Jaya Variasi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> Admin
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 px-0 sidebar">
                <a href="index.php" class="<?= (!isset($_GET['c']) || $_GET['c'] == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <hr class="text-muted">
                <a href="index.php?c=suppliers&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'suppliers') ? 'active' : '' ?>">
                    <i class="fas fa-truck"></i> Suppliers
                </a>
                <a href="index.php?c=categories&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'categories') ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Kategori
                </a>
                <a href="index.php?c=products&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'products') ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Produk
                </a>
                <a href="index.php?c=customers&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'customers') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Pelanggan
                </a>
                <a href="index.php?c=transactions&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'transactions') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Transaksi
                </a>
            </div>

            <div class="col-md-10 content-wrapper">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
```

## **O. Buat Layout (`Views/Layout/Footer.php`)**
```php
    </div>
            </div>
        </div>

        <footer class="footer mt-5 py-4 bg-dark text-white">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-motorcycle"></i> Patra Jaya Variasi
                        </h5>
                        <p class="text-light small">
                            Tempatnya Modif Motor NO.1 Balikpapan
                        </p>
                        <div class="mt-3 social-links">
                            <a href="#" class="text-white me-3" title="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-white me-3" title="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
                            <a href="#" class="text-white" title="WhatsApp"><i class="fab fa-whatsapp fa-lg"></i></a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-address-book"></i> Kontak Kami
                        </h5>
                        <ul class="list-unstyled text-light small">
                            <li class="mb-2"><i class="fas fa-map-marker-alt text-danger"></i> Jl. Soekarno Hatta Km. 21 Rt. 41</li>
                            <li class="mb-2"><i class="fas fa-phone text-success"></i> <a href="tel:+6281351319657" class="text-light text-decoration-none">081351319657</a></li>
                            <li class="mb-2"><i class="fas fa-envelope text-primary"></i> <a href="mailto:10241061@student.itk.ac.id" class="text-light text-decoration-none">10241061@student.itk.ac.id</a></li>
                            <li class="mb-2"><i class="fas fa-clock text-warning"></i> Senin - Sabtu: 08:00 - 17:00</li>
                        </ul>
                    </div>
                </div>

                <hr class="my-4 bg-secondary">

                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-2">
                        <p class="mb-0 small text-muted">
                            &copy; <?= date('Y') ?> Patra Jaya Variasi, Balikpapan Utara
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0 small ">
                            Powered by <i class=></i> 
                            <strong>Patra Ananda 1061</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            window.addEventListener('scroll', function() {
                const scrollBtn = document.getElementById('scrollTopBtn');
                if (scrollBtn) {
                    if (window.pageYOffset > 300) {
                        scrollBtn.style.display = 'block';
                    } else {
                        scrollBtn.style.display = 'none';
                    }
                }
            });
            function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        </script>
        <button onclick="scrollToTop()" id="scrollTopBtn" 
                style="display: none; position: fixed; bottom: 20px; right: 20px; 
                    z-index: 99; border: none; outline: none; 
                    background-color: #0d6efd; color: white; 
                    cursor: pointer; padding: 15px; border-radius: 50%; 
                    font-size: 18px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <i class="fas fa-arrow-up"></i>
        </button>
        <style>
            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            .content-wrapper {
                flex: 1;
            }
            .footer {
                background-color: #212529;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
                margin-top: auto;
            }
            .footer h5 {
                color: white;
                font-weight: bold;
                border-bottom: 2px solid rgba(255, 255, 255, 0.5);
                display: inline-block;
                padding-bottom: 5px;
            }
            .footer a {
                transition: all 0.3s ease;
            }
            .footer a.nav-link:hover {
                color: white !important;
                transform: translateX(5px);
            }
            .footer .social-links a:hover {
                transform: scale(1.2);
                color: #d3d3d3 !important;
            }
            #scrollTopBtn:hover {
                background-color: #0b5ed7;
                transform: scale(1.1);
            }
        </style>
    </body>
    </html>
```

## **P. Buat Views Suppliers (`Views/Suppliers/index.php`)**
```php
<!-- views/suppliers/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Supplier</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=suppliers&a=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Supplier
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="suppliers">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari berdasarkan nama, contact person, atau telepon..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Total: <?= $total ?> supplier</p>
            
            <?php if (empty($suppliers)): ?>
                <div class="alert alert-info">Tidak ada data supplier</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Supplier</th>
                                <th>Contact Person</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Kota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($suppliers as $supplier): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($supplier['name']) ?></strong></td>
                                <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                                <td><?= htmlspecialchars($supplier['phone']) ?></td>
                                <td><?= htmlspecialchars($supplier['email']) ?></td>
                                <td><?= htmlspecialchars($supplier['city']) ?></td>
                                <td>
                                    <a href="index.php?c=suppliers&a=edit&id=<?= $supplier['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="index.php?c=suppliers&a=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                        <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=suppliers&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
```
---
## **Q. Buat Views Suppliers (`Views/Suppliers/Create.php`)**
```php
    <!-- views/suppliers/create.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Tambah Supplier Baru</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=suppliers&a=index">Supplier</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=suppliers&a=store">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name') ?>" placeholder="PT. Supplier Motor" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" 
                               value="<?= old('contact_person') ?>" placeholder="Nama kontak">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control <?= getError('phone') ? 'is-invalid' : '' ?>" 
                               value="<?= old('phone') ?>" placeholder="08123456789" required>
                        <?php if (getError('phone')): ?>
                            <div class="invalid-feedback"><?= getError('phone') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?= getError('email') ? 'is-invalid' : '' ?>" 
                               value="<?= old('email') ?>" placeholder="email@supplier.com">
                        <?php if (getError('email')): ?>
                            <div class="invalid-feedback"><?= getError('email') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" 
                               value="<?= old('city') ?>" placeholder="Jakarta">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3" 
                                  placeholder="Alamat lengkap supplier..."><?= old('address') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=suppliers&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---
## **R. Buat Views Suppliers (`Views/Suppliers/Edit.php`)**
```php
<!-- views/suppliers/edit.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Edit Supplier</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=suppliers&a=index">Supplier</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=suppliers&a=update">
                <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name', $supplier['name']) ?>" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" 
                               value="<?= old('contact_person', $supplier['contact_person']) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control <?= getError('phone') ? 'is-invalid' : '' ?>" 
                               value="<?= old('phone', $supplier['phone']) ?>" required>
                        <?php if (getError('phone')): ?>
                            <div class="invalid-feedback"><?= getError('phone') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?= getError('email') ? 'is-invalid' : '' ?>" 
                               value="<?= old('email', $supplier['email']) ?>">
                        <?php if (getError('email')): ?>
                            <div class="invalid-feedback"><?= getError('email') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" 
                               value="<?= old('city', $supplier['city']) ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3"><?= old('address', $supplier['address']) ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=suppliers&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

## **S. Buat Views Categories (`Views/Categories/Index.php`)**
```php
<!-- views/categories/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Kategori</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=categories&a=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="categories">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari berdasarkan nama kategori atau deskripsi..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Total: <?= $total ?> kategori</p>
            
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">Tidak ada data kategori</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th width="15%">Tanggal Dibuat</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($categories as $category): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($category['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($category['description']) ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?php 
                                        if(isset($category['created_at'])):
                                            echo date('d/m/Y', strtotime($category['created_at']));
                                        else:
                                            echo '-';
                                        endif;
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="index.php?c=categories&a=edit&id=<?= $category['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="index.php?c=categories&a=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini? Kategori yang masih digunakan pada produk tidak bisa dihapus.')">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=categories&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
```
---
## **T. Buat View Categories (`Views/Categories/Create.php`)**
```php
<!-- views/categories/create.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Tambah Kategori Baru</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=categories&a=index">Kategori</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=categories&a=store">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name') ?>" placeholder="Contoh: Knalpot, Ban, Velg" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4" 
                                  placeholder="Deskripsi kategori..."><?= old('description') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=categories&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---

## **U. Buat Views Categories (`Views/Categories/edit.php`)**
```php
<!-- views/categories/edit.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Edit Kategori</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=categories&a=index">Kategori</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=categories&a=update">
                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name', $category['name']) ?>" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"><?= old('description', $category['description']) ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=categories&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Kategori
                    </button>
                </div>
            </form>
```
---

## **V. Buat Views Products (`Views/Product/index.php`)**
```php
<!-- views/products/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Sparepart Motor</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=products&a=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="products">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari berdasarkan nama, kode, atau tipe motor..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Total: <?= $total ?> produk</p>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info">Tidak ada data produk</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Brand</th>
                                <th>Kategori</th>
                                <th>Supplier</th>
                                <th>Tipe Motor</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($products as $product): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($product['code']) ?></strong></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['brand']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($product['category_name']) ?></span></td>
                                <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                                <td><small><?= htmlspecialchars($product['motor_type']) ?></small></td>
                                <td><strong><?= formatRupiah($product['price']) ?></strong></td>
                                <td>
                                    <?php if ($product['stock'] > 10): ?>
                                        <span class="badge bg-success"><?= $product['stock'] ?></span>
                                    <?php elseif ($product['stock'] > 0): ?>
                                        <span class="badge bg-warning"><?= $product['stock'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?c=products&a=edit&id=<?= $product['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="index.php?c=products&a=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=products&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
```
---

## **W. Buat Views Products (`Views/Products/create.php`)**
```php
<!-- views/products/create.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Tambah Produk Baru</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=products&a=index">Produk</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=products&a=store" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control <?= getError('code') ? 'is-invalid' : '' ?>" 
                               value="<?= old('code') ?>" placeholder="Contoh: SPR001" required>
                        <?php if (getError('code')): ?>
                            <div class="invalid-feedback"><?= getError('code') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name') ?>" placeholder="Contoh: Knalpot Racing" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select <?= getError('category_id') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (getError('category_id')): ?>
                            <div class="invalid-feedback"><?= getError('category_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select <?= getError('supplier_id') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>" <?= old('supplier_id') == $sup['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (getError('supplier_id')): ?>
                            <div class="invalid-feedback"><?= getError('supplier_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brand/Merk</label>
                        <input type="text" name="brand" class="form-control" 
                               value="<?= old('brand') ?>" placeholder="Contoh: Yamaha, Honda">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Motor</label>
                        <input type="text" name="motor_type" class="form-control" 
                               value="<?= old('motor_type') ?>" placeholder="Contoh: Vario 150, Beat">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control <?= getError('price') ? 'is-invalid' : '' ?>" 
                               value="<?= old('price') ?>" min="0" placeholder="0" required>
                        <?php if (getError('price')): ?>
                            <div class="invalid-feedback"><?= getError('price') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control <?= getError('stock') ? 'is-invalid' : '' ?>" 
                               value="<?= old('stock') ?>" min="0" placeholder="0" required>
                        <?php if (getError('stock')): ?>
                            <div class="invalid-feedback"><?= getError('stock') ?></div>
                        <?php endif; ?>
                    </div>


                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4" 
                                  placeholder="Deskripsi produk..."><?= old('description') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=products&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---
## **X. Buat Views Products (`Views/Products/Edit.php`)**
```php
<!-- views/products/edit.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Edit Produk</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=products&a=index">Produk</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=products&a=update" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control <?= getError('code') ? 'is-invalid' : '' ?>" 
                               value="<?= old('code', $product['code']) ?>" required>
                        <?php if (getError('code')): ?>
                            <div class="invalid-feedback"><?= getError('code') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name', $product['name']) ?>" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select <?= getError('category_id') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= old('category_id', $product['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (getError('category_id')): ?>
                            <div class="invalid-feedback"><?= getError('category_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select <?= getError('supplier_id') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>" 
                                    <?= old('supplier_id', $product['supplier_id']) == $sup['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (getError('supplier_id')): ?>
                            <div class="invalid-feedback"><?= getError('supplier_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brand/Merk</label>
                        <input type="text" name="brand" class="form-control" 
                               value="<?= old('brand', $product['brand']) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Motor</label>
                        <input type="text" name="motor_type" class="form-control" 
                               value="<?= old('motor_type', $product['motor_type']) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control <?= getError('price') ? 'is-invalid' : '' ?>" 
                               value="<?= old('price', $product['price']) ?>" min="0" required>
                        <?php if (getError('price')): ?>
                            <div class="invalid-feedback"><?= getError('price') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control <?= getError('stock') ? 'is-invalid' : '' ?>" 
                               value="<?= old('stock', $product['stock']) ?>" min="0" required>
                        <?php if (getError('stock')): ?>
                            <div class="invalid-feedback"><?= getError('stock') ?></div>
                        <?php endif; ?>
                    </div>


                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"><?= old('description', $product['description']) ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=products&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---

## **Y. Buat Views Customers (`Views/Customers/index.php`)**
```php
<!-- views/customers/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Pelanggan</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=customers&a=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="customers">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari berdasarkan nama, telepon, atau email..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Total: <?= $total ?> pelanggan</p>
            
            <?php if (empty($customers)): ?>
                <div class="alert alert-info">Tidak ada data pelanggan</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th>Kota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($customers as $customer): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($customer['name']) ?></strong></td>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                <td><?= htmlspecialchars($customer['address']) ?></td>
                                <td><?= htmlspecialchars($customer['city']) ?></td>
                                <td>
                                    <a href="index.php?c=customers&a=edit&id=<?= $customer['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="index.php?c=customers&a=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                        <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=customers&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
```
---
## **Z. Buat Views Customers (`Views/Customers/Create.php`)**
```php
<!-- views/customers/create.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Tambah Pelanggan Baru</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=customers&a=index">Pelanggan</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=customers&a=store">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name') ?>" placeholder="Nama pelanggan" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control <?= getError('phone') ? 'is-invalid' : '' ?>" 
                               value="<?= old('phone') ?>" placeholder="08123456789" required>
                        <?php if (getError('phone')): ?>
                            <div class="invalid-feedback"><?= getError('phone') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?= getError('email') ? 'is-invalid' : '' ?>" 
                               value="<?= old('email') ?>" placeholder="email@example.com">
                        <?php if (getError('email')): ?>
                            <div class="invalid-feedback"><?= getError('email') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" 
                               value="<?= old('city') ?>" placeholder="Jakarta">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3" 
                                  placeholder="Alamat lengkap pelanggan..."><?= old('address') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=customers&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---
## **A1. Buat Views Customers (`Views/Customers/Edit.php`)**
```php
<!-- views/customers/edit.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Edit Pelanggan</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=customers&a=index">Pelanggan</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="index.php?c=customers&a=update">
                <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= getError('name') ? 'is-invalid' : '' ?>" 
                               value="<?= old('name', $customer['name']) ?>" required>
                        <?php if (getError('name')): ?>
                            <div class="invalid-feedback"><?= getError('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control <?= getError('phone') ? 'is-invalid' : '' ?>" 
                               value="<?= old('phone', $customer['phone']) ?>" required>
                        <?php if (getError('phone')): ?>
                            <div class="invalid-feedback"><?= getError('phone') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?= getError('email') ? 'is-invalid' : '' ?>" 
                               value="<?= old('email', $customer['email']) ?>">
                        <?php if (getError('email')): ?>
                            <div class="invalid-feedback"><?= getError('email') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" 
                               value="<?= old('city', $customer['city']) ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3"><?= old('address', $customer['address']) ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?c=customers&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```
---

## **A2. Buat Views Transactions (`Views/Transactions/index.php`)**
```php
<!-- views/transactions/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Transaksi</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=transactions&a=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Transaksi Baru
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="transactions">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari berdasarkan kode transaksi atau nama pelanggan..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Total: <?= $total ?> transaksi</p>
            
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">Belum ada transaksi</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Telepon</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($transactions as $trans): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($trans['transaction_code']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($trans['transaction_date'])) ?></td>
                                <td><?= htmlspecialchars($trans['customer_name']) ?></td>
                                <td><?= htmlspecialchars($trans['customer_phone']) ?></td>
                                <td><strong><?= formatRupiah($trans['total_amount']) ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= strtoupper($trans['payment_method']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $badgeColor = [
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $badgeColor[$trans['status']] ?>">
                                        <?= strtoupper($trans['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?c=transactions&a=detail&id=<?= $trans['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($trans['status'] == 'pending'): ?>
                                    <form method="POST" action="index.php?c=transactions&a=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <input type="hidden" name="id" value="<?= $trans['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=transactions&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
```
---

## **A3. Buat Views Transactions (`Views/Transactions/Create.php`)**
```php
<!-- views/transactions/create.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Transaksi Baru</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?c=transactions&a=index">Transaksi</a></li>
                    <li class="breadcrumb-item active">Baru</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="POST" action="index.php?c=transactions&a=store" id="transactionForm">
        <div class="row">
            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Pilih Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <select id="productSelect" class="form-select">
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach($products as $prod): ?>
                                        <option value="<?= $prod['id'] ?>" 
                                                data-name="<?= htmlspecialchars($prod['name']) ?>"
                                                data-code="<?= htmlspecialchars($prod['code']) ?>"
                                                data-price="<?= $prod['price'] ?>"
                                                data-stock="<?= $prod['stock'] ?>">
                                            <?= htmlspecialchars($prod['code']) ?> - <?= htmlspecialchars($prod['name']) ?> 
                                            (Stok: <?= $prod['stock'] ?>) - <?= formatRupiah($prod['price']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" id="productQty" class="form-control" placeholder="Qty" min="1" value="1">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="addProductBtn" class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="cartTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th width="15%">Harga</th>
                                        <th width="10%">Qty</th>
                                        <th width="15%">Subtotal</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center text-muted">Belum ada produk</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Data Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                <?php foreach($customers as $cust): ?>
                                    <option value="<?= $cust['id'] ?>">
                                        <?= htmlspecialchars($cust['name']) ?> - <?= htmlspecialchars($cust['phone']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" class="form-control" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" 
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <h5>Total:</h5>
                            <h4 class="text-success" id="totalDisplay">Rp 0</h4>
                        </div>

                        <input type="hidden" name="total_amount" id="totalAmount" value="0">
                        <input type="hidden" name="items" id="itemsData" value="">

                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                            <i class="fas fa-check-circle"></i> Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let cart = [];

document.getElementById('addProductBtn').addEventListener('click', function() {
    const select = document.getElementById('productSelect');
    const qty = parseInt(document.getElementById('productQty').value) || 0;
    
    if (!select.value) {
        alert('Pilih produk terlebih dahulu!');
        return;
    }
    
    if (qty <= 0) {
        alert('Quantity harus lebih dari 0!');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const productId = parseInt(select.value);
    const productName = option.dataset.name;
    const productCode = option.dataset.code;
    const price = parseFloat(option.dataset.price);
    const stock = parseInt(option.dataset.stock);
    
    if (qty > stock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${stock}`);
        return;
    }
    
    const existingIndex = cart.findIndex(item => item.product_id === productId);
    
    if (existingIndex > -1) {
        cart[existingIndex].quantity += qty;
        cart[existingIndex].subtotal = cart[existingIndex].quantity * cart[existingIndex].price;
    } else {
        cart.push({
            product_id: productId,
            code: productCode,
            name: productName,
            price: price,
            quantity: qty,
            subtotal: price * qty
        });
    }
    
    renderCart();
    select.value = '';
    document.getElementById('productQty').value = 1;
});

function renderCart() {
    const tbody = document.getElementById('cartBody');
    const emptyRow = document.getElementById('emptyRow');
    
    if (cart.length === 0) {
        emptyRow.style.display = '';
        document.getElementById('submitBtn').disabled = true;
        updateTotal();
        return;
    }
    
    emptyRow.style.display = 'none';
    
    let html = '';
    cart.forEach((item, index) => {
        html += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.code}</strong></td>
                <td>${item.name}</td>
                <td>${formatRupiah(item.price)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${item.quantity}" min="1" 
                           onchange="updateQty(${index}, this.value)">
                </td>
                <td><strong>${formatRupiah(item.subtotal)}</strong></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('submitBtn').disabled = false;
    updateTotal();
}

function updateQty(index, newQty) {
    newQty = parseInt(newQty);
    if (newQty <= 0) {
        removeItem(index);
        return;
    }
    
    cart[index].quantity = newQty;
    cart[index].subtotal = cart[index].price * newQty;
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateTotal() {
    const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
    document.getElementById('totalDisplay').textContent = formatRupiah(total);
    document.getElementById('totalAmount').value = total;
    document.getElementById('itemsData').value = JSON.stringify(cart);
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

document.getElementById('transactionForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Tambahkan produk terlebih dahulu!');
    }
});
</script>
```
---

## **A3. Buat Views Transactions (`Views/Transactions/detail.php`)**
```php
<!-- views/transactions/detail.php -->

<div class="container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Detail Transaksi</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=transactions&a=print&id=<?= $transaction['id'] ?>"class="btn btn-success" target="_blank">
                <i class="fas fa-print"></i> Cetak Invoice
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h3>Patra Jaya Variasi</h3>
                <p class="mb-0">Toko Sparepart Motor</p>
                <p class="text-muted">Jl. Soekarno Hatta 21| Telp: 081351319657</p>
                <hr>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Informasi Transaksi:</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150">Kode Transaksi</td>
                            <td>: <strong><?= htmlspecialchars($transaction['transaction_code']) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>: <?= formatTanggal($transaction['transaction_date']) ?></td>
                        </tr>
                        <tr>
                            <td>Pembayaran</td>
                            <td>: <span class="badge bg-info"><?= strtoupper($transaction['payment_method']) ?></span></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>: 
                                <?php 
                                $badgeColor = [
                                    'pending' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $badgeColor[$transaction['status']] ?>">
                                    <?= strtoupper($transaction['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Data Pelanggan:</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="100">Nama</td>
                            <td>: <?= htmlspecialchars($transaction['customer_name']) ?></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>: <?= htmlspecialchars($transaction['email']) ?></td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td>: <?= htmlspecialchars($transaction['phone']) ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>: <?= htmlspecialchars($transaction['address']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <h6>Detail Produk:</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode</th>
                            <th>Nama Produk</th>
                            <th width="15%">Harga</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach($details as $item): 
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($item['product_code']) ?></strong></td>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="text-end"><?= formatRupiah($item['price']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end"><strong><?= formatRupiah($item['subtotal']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                            <td class="text-end">
                                <h5 class="mb-0 text-success">
                                    <strong><?= formatRupiah($transaction['total_amount']) ?></strong>
                                </h5>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($transaction['notes']): ?>
            <div class="mt-3">
                <h6>Catatan:</h6>
                <p class="text-muted"><?= htmlspecialchars($transaction['notes']) ?></p>
            </div>
            <?php endif; ?>

            <hr>
            <p class="text-center text-muted mb-0">
                <small>Terima kasih atas pembelian Anda!</small>
            </p>
        </div>
    </div>
</div>

<style media="print">
    .btn, nav, .sidebar, footer { display: none !important; }
    .content-wrapper { padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
</style>
```

---
## **A4. Buat Views Transactions (`Views/Transactions/Print.php`)**
```php
<?php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

require_once 'config/database.php';
require_once 'models/Transaction.php';
require_once 'helpers/functions.php';

$database = new Database();
$db = $database->getConnection();
$transactionModel = new Transaction($db);

$transaction = $transactionModel->find($id);
$details = $transactionModel->getDetails($id);

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - <?= htmlspecialchars($transaction['transaction_code']) ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .header .logo {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-box {
            width: 48%;
        }
        
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
        }
        
        .info-box table {
            width: 100%;
            font-size: 11px;
        }
        
        .info-box table td {
            padding: 4px 0;
        }
        
        .info-box table td:first-child {
            width: 120px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table thead {
            background-color: #333;
            color: white;
        }
        
        .items-table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .items-table tfoot {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table tfoot td {
            padding: 12px 10px;
            border-top: 2px solid #333;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-amount {
            font-size: 18px;
            color: #198754;
        }
        
        .notes {
            margin: 20px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .notes strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            text-align: center;
        }
        
        .signature-box {
            width: 45%;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #198754;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .badge-info {
            background-color: #0dcaf0;
            color: black;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-container {
                border: none;
                max-width: 100%;
            }
            
            .no-print {
                display: none;
            }
            
            @page {
                margin: 1cm;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        CETAK INVOICE
    </button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">🏍️</div>
            <h1>MOTOR MODIF SHOP</h1>
            <p>Toko Sparepart Motor Modifikasi Terpercaya</p>
            <p>Jl. Raya Motor No. 123, Jakarta Pusat 10110</p>
            <p>Telp: (021) 1234-5678 | Email: info@motormodifshop.com</p>
            <p>Website: www.motormodifshop.com</p>
        </div>

        <div class="invoice-title">
            INVOICE PENJUALAN
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>INFORMASI TRANSAKSI</h3>
                <table>
                    <tr>
                        <td>No. Invoice</td>
                        <td>: <strong><?= htmlspecialchars($transaction['transaction_code']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <?= formatTanggal($transaction['transaction_date']) ?></td>
                    </tr>
                    <tr>
                        <td>Waktu Cetak</td>
                        <td>: <?= date('d/m/Y H:i:s') ?></td>
                    </tr>
                    <tr>
                        <td>Metode Bayar</td>
                        <td>: <span class="badge badge-info"><?= strtoupper($transaction['payment_method']) ?></span></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: 
                            <?php 
                            $statusClass = $transaction['status'] == 'completed' ? 'badge-success' : 'badge-warning';
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= strtoupper($transaction['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <h3>DATA PELANGGAN</h3>
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <strong><?= htmlspecialchars($transaction['customer_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: <?= htmlspecialchars($transaction['phone']) ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>: <?= htmlspecialchars($transaction['email']) ?></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: <?= htmlspecialchars($transaction['address']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="15%">KODE</th>
                    <th>NAMA PRODUK</th>
                    <th width="15%" class="text-right">HARGA</th>
                    <th width="8%" class="text-center">QTY</th>
                    <th width="17%" class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $grandTotal = 0;
                foreach($details as $item): 
                    $grandTotal += $item['subtotal'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($item['product_code']) ?></strong></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-right"><?= formatRupiah($item['price']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right"><strong><?= formatRupiah($item['subtotal']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">TOTAL PEMBAYARAN:</td>
                    <td class="text-right total-amount"><?= formatRupiah($transaction['total_amount']) ?></td>
                </tr>
            </tfoot>
        </table>

        <?php if ($transaction['notes']): ?>
        <div class="notes">
            <strong>Catatan:</strong>
            <?= htmlspecialchars($transaction['notes']) ?>
        </div>
        <?php endif; ?>

        <div style="margin: 15px 0; font-style: italic; font-size: 11px;">
            <strong>Terbilang:</strong> 
            <span style="text-transform: capitalize;">
                # <?= ucwords(terbilang($transaction['total_amount'])) ?> Rupiah #
            </span>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p><strong>Penerima</strong></p>
                <div class="signature-line">
                    ( <?= htmlspecialchars($transaction['customer_name']) ?> )
                </div>
            </div>
            <div class="signature-box">
                <p><strong>Hormat Kami</strong></p>
                <div class="signature-line">
                    ( Admin )
                </div>
            </div>
        </div>

        <div class="footer">
            <p style="font-weight: bold; margin-bottom: 10px;">Terima kasih atas kepercayaan Anda!</p>
            <p style="font-size: 10px; color: #666;">
                Invoice ini dicetak otomatis oleh sistem. Barang yang sudah dibeli tidak dapat dikembalikan.
            </p>
            <p style="font-size: 10px; color: #666; margin-top: 5px;">
                Untuk informasi lebih lanjut, hubungi customer service kami.
            </p>
        </div>
    </div>

    <script>
        window.onafterprint = function() {
           
        }
    </script>
</body>
</html>

<?php
function terbilang($angka) {
    $angka = abs($angka);
    $bilangan = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    
    if ($angka < 12) {
        return $bilangan[$angka];
    } else if ($angka < 20) {
        return $bilangan[$angka - 10] . ' belas';
    } else if ($angka < 100) {
        return $bilangan[$angka / 10] . ' puluh ' . $bilangan[$angka % 10];
    } else if ($angka < 200) {
        return 'seratus ' . terbilang($angka - 100);
    } else if ($angka < 1000) {
        return $bilangan[$angka / 100] . ' ratus ' . terbilang($angka % 100);
    } else if ($angka < 2000) {
        return 'seribu ' . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        return terbilang($angka / 1000) . ' ribu ' . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        return terbilang($angka / 1000000) . ' juta ' . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        return terbilang($angka / 1000000000) . ' milyar ' . terbilang($angka % 1000000000);
    } else {
        return terbilang($angka / 1000000000000) . ' trilyun ' . terbilang($angka % 1000000000000);
    }
}
?>
```

## **A5. Buat Dashboard di Views (`Views/Dashboard.php`)**
```php
<!-- views/dashboard.php -->

<div class="container-fluid">
    <h2 class="mb-4">Dashboard </h2>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Produk</h6>
                            <h3 class="mb-0"><?= $totalProducts ?></h3>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Pelanggan</h6>
                            <h3 class="mb-0"><?= $totalCustomers ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 3rem;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Transaksi</h6>
                            <h3 class="mb-0"><?= $totalTransactions ?></h3>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Pendapatan</h6>
                            <h3 class="mb-0" style="font-size: 1.3rem;"><?= formatRupiah($totalRevenue) ?></h3>
                        </div>
                        <div class="text-danger" style="font-size: 3rem;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="index.php?c=products&a=create" class="btn btn-primary w-100 p-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Tambah Produk Baru
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?c=transactions&a=create" class="btn btn-success w-100 p-3">
                                <i class="fas fa-shopping-cart fa-2x d-block mb-2"></i>
                                Buat Transaksi
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?c=customers&a=create" class="btn btn-info w-100 p-3">
                                <i class="fas fa-user-plus fa-2x d-block mb-2"></i>
                                Tambah Pelanggan
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?c=products&a=index" class="btn btn-warning w-100 p-3">
                                <i class="fas fa-list fa-2x d-block mb-2"></i>
                                Lihat Semua Produk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4><i class="fas fa-info-circle"></i> Selamat Datang!</h4>
                    <p>Tempatnya Modif Motor Terlengkap</p>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Fitur Sistem:</h5>
                            <ul>
                                <li>Manajemen Supplier</li>
                                <li>Manajemen Kategori Produk</li>
                                <li>Manajemen Produk Sparepart</li>
                                <li>Manajemen Data Pelanggan</li>
                                <li>Transaksi Penjualan</li>
                                <li>Laporan Penjualan</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Menu Navigasi:</h5>
                            <ul>
                                <li><strong>Suppliers:</strong> Data pemasok sparepart</li>
                                <li><strong>Kategori:</strong> Kategori jenis sparepart</li>
                                <li><strong>Produk:</strong> Data sparepart motor</li>
                                <li><strong>Pelanggan:</strong> Data customer</li>
                                <li><strong>Transaksi:</strong> Pembelian sparepart</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```
---

## **A6. Buat Public CSS (`Views/Public/css/Style.php`)**
```php

/* ========================================
   GENERAL STYLES
======================================== */
:root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #0dcaf0;
    --dark-color: #212529;
    --light-color: #f8f9fa;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #333;
}

/* ========================================
   NAVBAR
======================================== */
.navbar {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar-brand {
    font-weight: bold;
    font-size: 1.3rem;
    transition: color 0.3s;
}

.navbar-brand:hover {
    color: #0d6efd !important;
}

/* ========================================
   SIDEBAR
======================================== */
.sidebar {
    min-height: calc(100vh - 56px);
    background-color: #212529;
    padding: 20px 0;
    position: sticky;
    top: 56px;
}

.sidebar a {
    color: #adb5bd;
    text-decoration: none;
    padding: 12px 20px;
    display: block;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar a:hover {
    background-color: #343a40;
    color: #fff;
    border-left-color: #0d6efd;
    padding-left: 25px;
}

.sidebar a.active {
    background-color: #495057;
    color: #fff;
    border-left-color: #0d6efd;
    font-weight: 600;
}

.sidebar a i {
    width: 25px;
    margin-right: 10px;
}

.sidebar hr {
    margin: 15px 0;
    border-color: #495057;
}

/* ========================================
   CONTENT WRAPPER
======================================== */
.content-wrapper {
    padding: 20px;
    min-height: calc(100vh - 56px);
}

/* ========================================
   CARDS
======================================== */
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    border: none;
    border-radius: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.card-header {
    background-color: #fff;
    border-bottom: 2px solid #f0f0f0;
    font-weight: 600;
}

/* ========================================
   STAT CARDS (Dashboard)
======================================== */
.stat-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.stat-card.primary { 
    border-left-color: #0d6efd; 
}

.stat-card.success { 
    border-left-color: #198754; 
}

.stat-card.warning { 
    border-left-color: #ffc107; 
}

.stat-card.danger { 
    border-left-color: #dc3545; 
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-card h6 {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ========================================
   TABLES
======================================== */
.table {
    background-color: #fff;
}

.table thead {
    background-color: #212529;
    color: #fff;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    border: none;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.02);
}

/* ========================================
   BUTTONS
======================================== */
.btn {
    border-radius: 5px;
    padding: 8px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn i {
    margin-right: 5px;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* ========================================
   BADGES
======================================== */
.badge {
    padding: 6px 12px;
    font-weight: 500;
    border-radius: 4px;
}

/* ========================================
   ALERTS
======================================== */
.alert {
    border-radius: 8px;
    border: none;
    padding: 15px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-dismissible .btn-close {
    padding: 1rem 1.25rem;
}

/* ========================================
   FORMS
======================================== */
.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border-radius: 5px;
    border: 1px solid #ced4da;
    padding: 10px 15px;
    transition: all 0.3s;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    color: #dc3545;
    margin-top: 5px;
}

/* ========================================
   PAGINATION
======================================== */
.pagination {
    margin-top: 20px;
}

.page-link {
    color: #0d6efd;
    border-radius: 5px;
    margin: 0 3px;
    transition: all 0.3s;
}

.page-link:hover {
    background-color: #0d6efd;
    color: #fff;
    transform: translateY(-2px);
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* ========================================
   BREADCRUMB
======================================== */
.breadcrumb {
    background-color: transparent;
    padding: 10px 0;
    margin-bottom: 20px;
}

.breadcrumb-item a {
    color: #0d6efd;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: #6c757d;
}

/* ========================================
   SEARCH BOX
======================================== */
.search-box {
    position: relative;
}

.search-box input {
    padding-left: 40px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* ========================================
   IMAGE PRODUCT
======================================== */
.product-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* ========================================
   PRINT STYLES
======================================== */
@media print {
    .btn, 
    nav, 
    .navbar,
    .sidebar, 
    footer,
    .no-print {
        display: none !important;
    }
    
    .content-wrapper {
        padding: 0 !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white;
    }
}

/* ========================================
   RESPONSIVE
======================================== */
@media (max-width: 768px) {
    .sidebar {
        position: relative;
        min-height: auto;
    }
    
    .content-wrapper {
        padding: 15px;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .stat-card h3 {
        font-size: 1.5rem;
    }
}

/* ========================================
   LOADING SPINNER
======================================== */
.spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

/* ========================================
   UTILITIES
======================================== */
.text-muted {
    color: #6c757d !important;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
}

.rounded {
    border-radius: 8px !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

/* ========================================
   QUICK ACTIONS (Dashboard)
======================================== */
.quick-action-btn {
    text-align: center;
    padding: 25px;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    transform: scale(1.05);
}

.quick-action-btn i {
    display: block;
    margin-bottom: 10px;
    font-size: 2.5rem;
}

/* ========================================
   CART TABLE (Transactions)
======================================== */
#cartTable input[type="number"] {
    width: 80px;
}

#cartTable .btn-sm {
    padding: 4px 8px;
}

/* ========================================
   INVOICE PRINT
======================================== */
.invoice-header {
    text-align: center;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.invoice-header h3 {
    margin-bottom: 5px;
    color: #0d6efd;
}

/* ========================================
   ANIMATIONS
======================================== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease-in-out;
}

/* ========================================
   SCROLLBAR CUSTOM
======================================== */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
```
---

## **A7. public upload itu hanya foto produk saja
---

## **A8. Buat Views Helpers (`Views/Helpers/functions.php`)**
```php
<?php
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function setOld($data) {
    $_SESSION['old'] = $data;
}

function old($key, $default = '') {
    if (isset($_SESSION['old'][$key])) {
        return $_SESSION['old'][$key];
    }
    return $default;
}

function clearOld() {
    unset($_SESSION['old']);
}

function setErrors($errors) {
    $_SESSION['errors'] = $errors;
}

function getError($key) {
    if (isset($_SESSION['errors'][$key])) {
        return $_SESSION['errors'][$key];
    }
    return null;
}

function clearErrors() {
    unset($_SESSION['errors']);
}


function redirect($url) {
    header("Location: $url");
    exit();
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function uploadImage($file) {
    $targetDir = "motor_modif_shop/public/uploads/products/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File bukan gambar'];
    }
    
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 2MB)'];
    }
    
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'message' => 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

function generateTransactionCode() {
    return 'TRX' . date('Ymd') . rand(1000, 9999);
}
?>
```
---
## **A9. Buat Folder di luar motor_modif_motor (`Week8/index.php`)**
```php
<?php
session_start();
define('BASE_PATH', __DIR__ . '/motor_modif_shop/');

require_once BASE_PATH . 'helpers/functions.php';
require_once BASE_PATH . 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$controller = isset($_GET['c']) ? clean($_GET['c']) : 'dashboard';
$action = isset($_GET['a']) ? clean($_GET['a']) : 'index';

if ($controller == 'dashboard' || $controller == '') {
    require_once BASE_PATH . 'models/Product.php';
    require_once BASE_PATH . 'models/Customer.php';
    require_once BASE_PATH . 'models/Transaction.php';
    
    $productModel = new Product($db);
    $customerModel = new Customer($db);
    $transactionModel = new Transaction($db);
    
    $totalProducts = $productModel->count('');
    $totalCustomers = $customerModel->count('');
    $totalTransactions = $transactionModel->count('');
    
    $sql = "SELECT SUM(total_amount) as total FROM transaksi WHERE status = 'completed'";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $totalRevenue = $row['total'] ?? 0;
    
    include BASE_PATH . 'views/layouts/header.php';
    include BASE_PATH . 'views/dashboard.php';
    include BASE_PATH . 'views/layouts/footer.php';
    
} else {
    $controllerFile = BASE_PATH . 'controllers/' . ucfirst($controller) . 'Controller.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        $controllerClass = ucfirst($controller) . 'Controller';
        $controllerObject = new $controllerClass($db);
        
        if (method_exists($controllerObject, $action)) {
            $controllerObject->$action();
        } else {
            die("Action '$action' tidak ditemukan di controller '$controller'");
        }
    } else {
        die("Controller '$controller' tidak ditemukan");
    }
}

$database->close();
?>
```
---
## **OUTPUT**
Tampilan awal Dashboard Website CRUDMVC: Toko Patra Jaya Variasi
![1](image.png)

---
## 1.TAMPILAN SUPLIER

Tampilan Suplier Barang
![2](image-1.png)

DI tampilan suplier kita bisa menggunakan search untuk mencari supplier yang ingin kita cari
![2.1](image-6.png)

Bisa menambahkan Suplier Baru 
![2.3](image-7.png)

---

## 2.Kategori Barang
Tampilan Kategori Barang
![3](image-2.png)

search kategori barang yang ingin kita cari
![3.1](image-8.png)

Menambahkan Kategori barang yang diperluhkan
![3.2](image-9.png)

---
## 3. Data Produk Yang ada di toko

Data Produk yang ada di toko
![4](image-3.png)

bisa search data barang yang ingin dicari
![4.1](image-10.png)

Bisa tambah barang semisal barangnya restock
![4.2](image-11.png)

---
## 4. Data Pelanggan
Data Pelanggan Toko
![5](image-4.png)

bisa search nama pelanggan
![5.1](image-12.png)

bisa nambah pelanggan yang telah beli
![5.2](image-13.png)

---
##5. Transaksi Pelanggan

Data Transaksi Pelanggan
![6](image-5.png)


Bisa mencari data transaksi pelanggan beli apa saja
![6.1](image-14.png)

bisa liat total struk transaksi pelanggan 
![6.2](image-15.png)

bisa print struknnya pelanggan
![6.3](image-16.png)

bagian payment untuk pelanggan
![6.4](image-17.png)

---
## 5. Footer Website

![7](image-18.png)


makasih
  
---
