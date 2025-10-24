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