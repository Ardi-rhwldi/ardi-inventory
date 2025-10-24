<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

requireLogin();

$pageTitle = 'Laporan Laba Kotor';
$saleModel = new SaleTransaction();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$profitReport = $saleModel->getProfitReport($startDate, $endDate);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="bi bi-currency-dollar"></i> Laporan Laba Kotor</h2>
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
                        <th class="text-end">Pendapatan (Revenue)</th>
                        <th class="text-end">Modal (Cost)</th>
                        <th class="text-end">Laba Kotor</th>
                        <th class="text-end">Margin (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalRevenue = 0;
                    $totalCost = 0;
                    $totalProfit = 0;
                    
                    foreach ($profitReport as $row): 
                        $totalRevenue += $row['revenue'];
                        $totalCost += $row['cost'];
                        $totalProfit += $row['gross_profit'];
                        $margin = $row['revenue'] > 0 ? ($row['gross_profit'] / $row['revenue']) * 100 : 0;
                    ?>
                    <tr>
                        <td><?php echo formatDate($row['date'], 'd/m/Y'); ?></td>
                        <td class="text-end"><?php echo formatRupiah($row['revenue']); ?></td>
                        <td class="text-end text-danger"><?php echo formatRupiah($row['cost']); ?></td>
                        <td class="text-end text-success"><strong><?php echo formatRupiah($row['gross_profit']); ?></strong></td>
                        <td class="text-end"><span class="badge bg-info"><?php echo number_format($margin, 2); ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-end"><?php echo formatRupiah($totalRevenue); ?></th>
                        <th class="text-end text-danger"><?php echo formatRupiah($totalCost); ?></th>
                        <th class="text-end text-success"><strong><?php echo formatRupiah($totalProfit); ?></strong></th>
                        <th class="text-end">
                            <span class="badge bg-info">
                                <?php echo $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 2) : 0; ?>%
                            </span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
