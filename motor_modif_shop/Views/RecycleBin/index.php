<!-- views/recyclebin/index.php -->

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>
                <i class="fas fa-trash-restore"></i> Recycle Bin - Produk Terhapus
            </h2>
            <p class="text-muted">Produk yang dihapus dapat dikembalikan atau dihapus permanen</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php?c=products&a=index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Produk
            </a>
            <?php if ($total > 0): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreAllModal">
                <i class="fas fa-undo-alt"></i> Kembalikan Semua
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
                <i class="fas fa-trash-alt"></i> Kosongkan Recycle Bin
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="recyclebin">
                <input type="hidden" name="a" value="index">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari produk yang dihapus..." 
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

    <!-- Content -->
    <div class="card">
        <div class="card-body">
            <?php if ($total > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian!</strong> Ada <strong><?= $total ?></strong> produk di Recycle Bin. 
                    Anda dapat mengembalikan atau menghapusnya secara permanen.
                </div>
            <?php endif; ?>

            <p class="text-muted">Total: <?= $total ?> produk terhapus</p>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>Recycle Bin Kosong</h5>
                    <p class="mb-0">Tidak ada produk yang dihapus</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode</th>
                                <th>Nama Produk</th>
                                <th width="12%">Kategori</th>
                                <th width="12%">Harga</th>
                                <th width="8%">Stok</th>
                                <th width="15%">Dihapus Pada</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = ($page - 1) * 10 + 1;
                            foreach($products as $product): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($product['code']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <?php if ($product['brand']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($product['brand']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($product['category_name']) ?></span>
                                </td>
                                <td><?= formatRupiah($product['price']) ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark"><?= $product['stock'] ?></span>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d/m/Y H:i', strtotime($product['deleted_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <!-- Restore Button -->
                                    <form method="POST" action="index.php?c=recyclebin&a=restore" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('Yakin ingin mengembalikan produk ini?')">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                title="Kembalikan" data-bs-toggle="tooltip">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Permanent Delete Button -->
                                    <form method="POST" action="index.php?c=recyclebin&a=forceDelete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('PERINGATAN! Produk akan dihapus permanen dan tidak bisa dikembalikan. Lanjutkan?')">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                title="Hapus Permanen" data-bs-toggle="tooltip">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?c=recyclebin&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>">
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

    <!-- Legend -->
    <div class="card mt-3">
        <div class="card-body">
            <h6><i class="fas fa-info-circle"></i> Keterangan:</h6>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1">
                        <button class="btn btn-sm btn-success" disabled>
                            <i class="fas fa-undo"></i>
                        </button>
                        <strong>Restore:</strong> Mengembalikan 1 produk
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1">
                        <button class="btn btn-sm btn-success" disabled>
                            <i class="fas fa-undo-alt"></i>
                        </button>
                        <strong>Restore All:</strong> Mengembalikan semua produk sekaligus
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1">
                        <button class="btn btn-sm btn-danger" disabled>
                            <i class="fas fa-times"></i>
                        </button>
                        <strong>Hapus Permanen:</strong> Tidak bisa dikembalikan
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore All Modal -->
<div class="modal fade" id="restoreAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo-alt"></i> Konfirmasi Kembalikan Semua
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-info-circle"></i> <strong>Informasi</strong>
                </div>
                <p>Anda akan mengembalikan <strong>SEMUA <?= $total ?> produk</strong> dari Recycle Bin ke daftar produk aktif.</p>
                <p class="mb-0">Semua produk akan tersedia kembali untuk transaksi. Lanjutkan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <form method="POST" action="index.php?c=recyclebin&a=restoreAll" style="display:inline;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo-alt"></i> Ya, Kembalikan Semua
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Empty Trash Modal -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Kosongkan Recycle Bin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>PERINGATAN!</strong>
                </div>
                <p>Anda akan menghapus <strong>SEMUA <?= $total ?> produk</strong> di Recycle Bin secara permanen.</p>
                <p class="mb-0">Data yang sudah dihapus <strong>TIDAK BISA dikembalikan</strong>. Apakah Anda yakin?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <form method="POST" action="index.php?c=recyclebin&a=empty" style="display:inline;">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Ya, Hapus Semua
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.1);
}

.badge {
    font-weight: 500;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}
</style>