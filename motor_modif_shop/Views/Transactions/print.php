<?php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

require_once 'config/database.php';
require_once 'models/Transaction.php';
require_once 'helpers/functions.php';

$database = new Database();
$db = $database->getConnection();
$transactionModel = new Transaction($db);

$transaction = $transactionModel->find($id);
$details = $transactionModel->getDetails($id);

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - <?= htmlspecialchars($transaction['transaction_code']) ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .header .logo {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-box {
            width: 48%;
        }
        
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
        }
        
        .info-box table {
            width: 100%;
            font-size: 11px;
        }
        
        .info-box table td {
            padding: 4px 0;
        }
        
        .info-box table td:first-child {
            width: 120px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table thead {
            background-color: #333;
            color: white;
        }
        
        .items-table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .items-table tfoot {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table tfoot td {
            padding: 12px 10px;
            border-top: 2px solid #333;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-amount {
            font-size: 18px;
            color: #198754;
        }
        
        .notes {
            margin: 20px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .notes strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            text-align: center;
        }
        
        .signature-box {
            width: 45%;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #198754;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .badge-info {
            background-color: #0dcaf0;
            color: black;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-container {
                border: none;
                max-width: 100%;
            }
            
            .no-print {
                display: none;
            }
            
            @page {
                margin: 1cm;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        CETAK INVOICE
    </button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">🏍️</div>
            <h1>MOTOR MODIF SHOP</h1>
            <p>Toko Sparepart Motor Modifikasi Terpercaya</p>
            <p>Jl. Raya Motor No. 123, Jakarta Pusat 10110</p>
            <p>Telp: (021) 1234-5678 | Email: info@motormodifshop.com</p>
            <p>Website: www.motormodifshop.com</p>
        </div>

        <div class="invoice-title">
            INVOICE PENJUALAN
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>INFORMASI TRANSAKSI</h3>
                <table>
                    <tr>
                        <td>No. Invoice</td>
                        <td>: <strong><?= htmlspecialchars($transaction['transaction_code']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <?= formatTanggal($transaction['transaction_date']) ?></td>
                    </tr>
                    <tr>
                        <td>Waktu Cetak</td>
                        <td>: <?= date('d/m/Y H:i:s') ?></td>
                    </tr>
                    <tr>
                        <td>Metode Bayar</td>
                        <td>: <span class="badge badge-info"><?= strtoupper($transaction['payment_method']) ?></span></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: 
                            <?php 
                            $statusClass = $transaction['status'] == 'completed' ? 'badge-success' : 'badge-warning';
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= strtoupper($transaction['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <h3>DATA PELANGGAN</h3>
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <strong><?= htmlspecialchars($transaction['customer_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: <?= htmlspecialchars($transaction['phone']) ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>: <?= htmlspecialchars($transaction['email']) ?></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: <?= htmlspecialchars($transaction['address']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="15%">KODE</th>
                    <th>NAMA PRODUK</th>
                    <th width="15%" class="text-right">HARGA</th>
                    <th width="8%" class="text-center">QTY</th>
                    <th width="17%" class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $grandTotal = 0;
                foreach($details as $item): 
                    $grandTotal += $item['subtotal'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($item['product_code']) ?></strong></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-right"><?= formatRupiah($item['price']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right"><strong><?= formatRupiah($item['subtotal']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">TOTAL PEMBAYARAN:</td>
                    <td class="text-right total-amount"><?= formatRupiah($transaction['total_amount']) ?></td>
                </tr>
            </tfoot>
        </table>

        <?php if ($transaction['notes']): ?>
        <div class="notes">
            <strong>Catatan:</strong>
            <?= htmlspecialchars($transaction['notes']) ?>
        </div>
        <?php endif; ?>

        <div style="margin: 15px 0; font-style: italic; font-size: 11px;">
            <strong>Terbilang:</strong> 
            <span style="text-transform: capitalize;">
                # <?= ucwords(terbilang($transaction['total_amount'])) ?> Rupiah #
            </span>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p><strong>Penerima</strong></p>
                <div class="signature-line">
                    ( <?= htmlspecialchars($transaction['customer_name']) ?> )
                </div>
            </div>
            <div class="signature-box">
                <p><strong>Hormat Kami</strong></p>
                <div class="signature-line">
                    ( Admin )
                </div>
            </div>
        </div>

        <div class="footer">
            <p style="font-weight: bold; margin-bottom: 10px;">Terima kasih atas kepercayaan Anda!</p>
            <p style="font-size: 10px; color: #666;">
                Invoice ini dicetak otomatis oleh sistem. Barang yang sudah dibeli tidak dapat dikembalikan.
            </p>
            <p style="font-size: 10px; color: #666; margin-top: 5px;">
                Untuk informasi lebih lanjut, hubungi customer service kami.
            </p>
        </div>
    </div>

    <script>
        window.onafterprint = function() {
           
        }
    </script>
</body>
</html>

<?php
function terbilang($angka) {
    $angka = abs($angka);
    $bilangan = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    
    if ($angka < 12) {
        return $bilangan[$angka];
    } else if ($angka < 20) {
        return $bilangan[$angka - 10] . ' belas';
    } else if ($angka < 100) {
        return $bilangan[$angka / 10] . ' puluh ' . $bilangan[$angka % 10];
    } else if ($angka < 200) {
        return 'seratus ' . terbilang($angka - 100);
    } else if ($angka < 1000) {
        return $bilangan[$angka / 100] . ' ratus ' . terbilang($angka % 100);
    } else if ($angka < 2000) {
        return 'seribu ' . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        return terbilang($angka / 1000) . ' ribu ' . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        return terbilang($angka / 1000000) . ' juta ' . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        return terbilang($angka / 1000000000) . ' milyar ' . terbilang($angka % 1000000000);
    } else {
        return terbilang($angka / 1000000000000) . ' trilyun ' . terbilang($angka % 1000000000000);
    }
}
?>