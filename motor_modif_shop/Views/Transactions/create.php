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