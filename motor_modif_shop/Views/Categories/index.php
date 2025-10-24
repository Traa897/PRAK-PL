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