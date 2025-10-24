<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

requireLogin();

$saleId = $_GET['id'] ?? null;

if (!$saleId) {
    die('Sale ID tidak ditemukan');
}

$saleModel = new SaleTransaction();
$sale = $saleModel->getSaleWithDetails($saleId);

if (!$sale) {
    die('Transaksi tidak ditemukan');
}

$pageTitle = 'Struk Transaksi #' . $sale['sale_number'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
        .receipt {
            max-width: 80mm;
            margin: 20px auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-items {
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-footer {
            text-align: center;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt">
            <div class="receipt-header">
                <h4 class="mb-1"><?php echo APP_NAME; ?></h4>
                <p class="mb-0">Sistem Kasir & Inventori</p>
                <small>================================</small>
            </div>
            
            <table class="w-100 mb-2" style="font-size: 11px;">
                <tr>
                    <td>No. Transaksi</td>
                    <td class="text-end"><strong><?php echo $sale['sale_number']; ?></strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td class="text-end"><?php echo formatDate($sale['sale_date']); ?></td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td class="text-end"><?php echo $_SESSION['full_name']; ?></td>
                </tr>
                <?php if (isset($sale['customer'])): ?>
                <tr>
                    <td>Pelanggan</td>
                    <td class="text-end"><?php echo htmlspecialchars($sale['customer']['customer_name']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <small>================================</small>
            
            <div class="receipt-items">
                <table class="w-100" style="font-size: 11px;">
                    <?php foreach ($sale['items'] as $item): ?>
                    <tr>
                        <td colspan="2"><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['selling_price']); ?></td>
                        <td class="text-end"><?php echo formatRupiah($item['subtotal']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <table class="w-100" style="font-size: 11px;">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-end"><strong><?php echo formatRupiah($sale['subtotal']); ?></strong></td>
                </tr>
                <?php if ($sale['discount_amount'] > 0): ?>
                <tr>
                    <td>Diskon (<?php echo $sale['discount_percent']; ?>%)</td>
                    <td class="text-end">-<?php echo formatRupiah($sale['discount_amount']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><h5 class="mb-0">TOTAL</h5></td>
                    <td class="text-end"><h5 class="mb-0"><?php echo formatRupiah($sale['total_amount']); ?></h5></td>
                </tr>
                <tr>
                    <td>Bayar (<?php echo strtoupper($sale['payment_method']); ?>)</td>
                    <td class="text-end"><?php echo formatRupiah($sale['payment_amount']); ?></td>
                </tr>
                <tr>
                    <td>Kembalian</td>
                    <td class="text-end"><?php echo formatRupiah($sale['change_amount']); ?></td>
                </tr>
            </table>
            
            <small class="d-block text-center my-2">================================</small>
            
            <div class="receipt-footer">
                <p class="mb-1">Terima Kasih</p>
                <p class="mb-0"><small>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</small></p>
            </div>
        </div>
        
        <div class="text-center no-print mt-4">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Struk
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
