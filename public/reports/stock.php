<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Item.php';

requireLogin();
requireAdmin();

$pageTitle = 'Laporan Stok';
$itemModel = new Item();

$stockReport = $itemModel->getStockReport();

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="bi bi-boxes"></i> Laporan Stok Barang</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="stockTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Profit/Unit</th>
                        <th>Nilai Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $totalValue = 0;
                    foreach ($stockReport as $item): 
                        $totalValue += $item['stock_value'];
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><code><?php echo htmlspecialchars($item['sku']); ?></code></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? '-'); ?></td>
                        <td class="text-end"><?php echo $item['stock_quantity']; ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td class="text-end"><?php echo formatRupiah($item['purchase_price']); ?></td>
                        <td class="text-end"><?php echo formatRupiah($item['selling_price']); ?></td>
                        <td class="text-end text-success"><strong><?php echo formatRupiah($item['profit_per_unit']); ?></strong></td>
                        <td class="text-end"><?php echo formatRupiah($item['stock_value']); ?></td>
                        <td>
                            <?php if ($item['stock_status'] == 'Out of Stock'): ?>
                                <span class="badge bg-danger">Habis</span>
                            <?php elseif ($item['stock_status'] == 'Low Stock'): ?>
                                <span class="badge bg-warning">Menipis</span>
                            <?php else: ?>
                                <span class="badge bg-success">Tersedia</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="9" class="text-end">Total Nilai Stok:</th>
                        <th class="text-end"><?php echo formatRupiah($totalValue); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
