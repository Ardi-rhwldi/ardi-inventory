<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

requireLogin();

$pageTitle = 'Laporan Penjualan';
$saleModel = new SaleTransaction();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$salesReport = $saleModel->getSalesReport($startDate, $endDate);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="bi bi-graph-up"></i> Laporan Penjualan</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Periode: <?php echo formatDate($startDate, 'd/m/Y'); ?> - <?php echo formatDate($endDate, 'd/m/Y'); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-end">Total Transaksi</th>
                        <th class="text-end">Penjualan Kotor</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Penjualan Bersih</th>
                        <th class="text-end">Pelanggan Unik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalTransactions = 0;
                    $totalGross = 0;
                    $totalDiscount = 0;
                    $totalNet = 0;
                    
                    foreach ($salesReport as $row): 
                        $totalTransactions += $row['total_transactions'];
                        $totalGross += $row['gross_sales'];
                        $totalDiscount += $row['total_discount'];
                        $totalNet += $row['net_sales'];
                    ?>
                    <tr>
                        <td><?php echo formatDate($row['date'], 'd/m/Y'); ?></td>
                        <td class="text-end"><?php echo $row['total_transactions']; ?></td>
                        <td class="text-end"><?php echo formatRupiah($row['gross_sales']); ?></td>
                        <td class="text-end text-danger"><?php echo formatRupiah($row['total_discount']); ?></td>
                        <td class="text-end"><strong><?php echo formatRupiah($row['net_sales']); ?></strong></td>
                        <td class="text-end"><?php echo $row['unique_customers']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-end"><?php echo $totalTransactions; ?></th>
                        <th class="text-end"><?php echo formatRupiah($totalGross); ?></th>
                        <th class="text-end text-danger"><?php echo formatRupiah($totalDiscount); ?></th>
                        <th class="text-end"><strong><?php echo formatRupiah($totalNet); ?></strong></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
