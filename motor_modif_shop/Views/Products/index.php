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