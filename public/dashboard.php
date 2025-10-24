<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/SaleTransaction.php';

requireLogin();

$pageTitle = 'Dashboard';

$itemModel = new Item();
$saleModel = new SaleTransaction();

$totalItems = $itemModel->count('is_active = ?', [true]);
$lowStockItems = count($itemModel->getLowStockItems());

$todaySales = $saleModel->getDailySales(date('Y-m-d'));
$todayTransactions = $todaySales['total_transactions'] ?? 0;
$todayRevenue = $todaySales['net_sales'] ?? 0;
$todayProfit = $todaySales['total_profit'] ?? 0;

$recentSales = $saleModel->findAll('sale_date DESC', 10);
$lowStockList = $itemModel->getLowStockItems();

include __DIR__ . '/../views/layouts/header.php';
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-0">Total Barang</p>
                        <p class="stat-value text-primary"><?php echo $totalItems; ?></p>
                    </div>
                    <i class="bi bi-box-seam text-primary" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-0">Stok Menipis</p>
                        <p class="stat-value text-warning"><?php echo $lowStockItems; ?></p>
                    </div>
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-0">Transaksi Hari Ini</p>
                        <p class="stat-value text-success"><?php echo $todayTransactions; ?></p>
                    </div>
                    <i class="bi bi-receipt text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-0">Pendapatan Hari Ini</p>
                        <p class="stat-value text-success" style="font-size: 1.5rem;"><?php echo formatRupiah($todayRevenue); ?></p>
                    </div>
                    <i class="bi bi-cash-stack text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up"></i> Grafik Penjualan 7 Hari Terakhir
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-exclamation-triangle"></i> Peringatan Stok Minimum
            </div>
            <div class="card-body p-0">
                <?php if (count($lowStockList) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($lowStockList, 0, 5) as $item): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['sku']); ?></small>
                                    </div>
                                    <span class="badge bg-danger"><?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($lowStockList) > 5): ?>
                        <div class="card-footer text-center">
                            <a href="<?php echo url('reports/stock.php'); ?>" class="btn btn-sm btn-outline-warning">
                                Lihat Semua (<?php echo count($lowStockList); ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                        <p class="mt-2">Semua stok barang mencukupi</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Transaksi Penjualan Terbaru
            </div>
            <div class="card-body p-0">
                <?php if (count($recentSales) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Pembayaran</th>
                                    <th>Metode</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($sale['sale_number']); ?></strong></td>
                                        <td><?php echo formatDate($sale['sale_date']); ?></td>
                                        <td><?php echo formatRupiah($sale['total_amount']); ?></td>
                                        <td><?php echo formatRupiah($sale['payment_amount']); ?></td>
                                        <td><span class="badge bg-info"><?php echo strtoupper($sale['payment_method']); ?></span></td>
                                        <td>
                                            <a href="<?php echo url('pos/receipt.php?id=' . $sale['sale_id']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-printer"></i> Struk
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Belum ada transaksi penjualan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$salesData = $saleModel->getSalesReport(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
$chartLabels = [];
$chartData = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d/m', strtotime($date));
    
    $found = false;
    foreach ($salesData as $data) {
        if ($data['date'] == $date) {
            $chartData[] = $data['net_sales'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $chartData[] = 0;
    }
}

$extraJS = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'];
include __DIR__ . '/../views/layouts/footer.php';
?>

<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Penjualan (Rp)',
            data: <?php echo json_encode($chartData); ?>,
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Penjualan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
