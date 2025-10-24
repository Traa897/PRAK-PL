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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>