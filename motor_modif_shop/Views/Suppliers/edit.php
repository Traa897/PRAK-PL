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