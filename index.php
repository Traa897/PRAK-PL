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