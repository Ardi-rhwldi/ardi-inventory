<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/PurchaseTransaction.php';

requireAdmin();

$purchaseModel = new PurchaseTransaction();
$root_dir = __DIR__ . '/../../';

// 1. Ambil ID dari URL dan Validasi
$purchase_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$purchase_id) {
    setFlashMessage('error', 'ID Transaksi Pembelian tidak valid.');
    header('Location: index.php');
    exit;
}

// 2. Ambil data transaksi secara lengkap
// Method ini harus ada di models/PurchaseTransaction.php (seperti yang sudah kita bahas)
$purchase = $purchaseModel->getPurchaseWithDetails($purchase_id); 

if (!$purchase) {
    setFlashMessage('error', 'Transaksi Pembelian tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Detail Transaksi Pembelian #' . $purchase['purchase_number'];

include $root_dir . 'views/layouts/header.php';
?>

<div class="container-fluid mt-4">
    <h2><i class="bi bi-cart-check"></i> <?php echo $pageTitle; ?></h2>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th style="width: 40%;">Nomor Pembelian</th>
                            <td>:</td>
                            <td><strong><?php echo htmlspecialchars($purchase['purchase_number']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Beli</th>
                            <td>:</td>
                            <td><?php echo date('d M Y', strtotime($purchase['purchase_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Total Biaya</th>
                            <td>:</td>
                            <td><h4 class="text-danger">Rp <?php echo number_format($purchase['total_amount'], 0, ',', '.'); ?></h4></td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($purchase['supplier_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Kontak Supplier</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($purchase['phone'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Dicatat Oleh</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($purchase['created_by'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Waktu Catat</th>
                            <td>:</td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($purchase['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($purchase['notes'] ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Rincian Barang Masuk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>SKU & Nama Barang</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga Beli (@)</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($purchase['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($item['sku'] . ' - ' . $item['item_name']); ?></td>
                                        <td class="text-center"><?php echo number_format($item['quantity'], 0, ',', '.'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? '-'); ?></td>
                                        <td class="text-end">Rp <?php echo number_format($item['purchase_price'], 0, ',', '.'); ?></td>
                                        <td class="text-end">
                                            <strong>Rp <?php echo number_format($item['quantity'] * $item['purchase_price'], 0, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">TOTAL TRANSAKSI</th>
                                    <th class="text-end text-danger">
                                        Rp <?php echo number_format($purchase['total_amount'], 0, ',', '.'); ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <a href="index.php" class="btn btn-secondary me-2">Kembali ke Daftar Pembelian</a>
<button type="button" class="btn btn-success" onclick="window.print()">
    <i class="bi bi-printer"></i> Cetak Dokumen
</button>

</div>

<?php 
include $root_dir . 'views/layouts/footer.php'; 
?>