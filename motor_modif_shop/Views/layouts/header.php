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
                <a href="index.php?c=recyclebin&a=index" class="<?= (isset($_GET['c']) && $_GET['c'] == 'recyclebin') ? 'active' : '' ?>">
                    <i class="fas fa-trash-restore"></i> Recycle Bin
                </a>
            </div>


            <div class="col-md-10 content-wrapper">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
