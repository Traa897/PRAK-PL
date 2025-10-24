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