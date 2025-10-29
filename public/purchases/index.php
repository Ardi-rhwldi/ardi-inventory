<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/PurchaseTransaction.php';
// Anda mungkin juga perlu memuat Supplier model jika ingin menampilkan info lebih lanjut

requireAdmin(); // Pastikan hanya admin yang bisa mengakses

$purchaseModel = new PurchaseTransaction();
$pageTitle = 'Daftar Transaksi Pembelian (IN)';
$flashMessage = getFlashMessage();

// Tentukan path root relatif untuk mengatasi error ROOT_PATH
$root_dir = __DIR__ . '/../../'; 

// Ambil data pembelian terbaru (default limit 100 seperti di model)
$purchases = $purchaseModel->getAllPurchasesWithSupplier(100);

include $root_dir . 'views/layouts/header.php';
?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-cart-plus"></i> <?php echo $pageTitle; ?></h2>
        <a href="form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Pembelian Baru (IN)</a>
    </div>

    <?php if ($flashMessage): ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?>"><?php echo $flashMessage['message']; ?></div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Histori Pembelian Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>No. Pembelian</th>
                            <th>Tanggal Beli</th>
                            <th>Supplier</th>
                            <th class="text-end">Total Biaya</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal Input</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($purchases)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data transaksi pembelian.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($purchase['purchase_number']); ?></strong>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($purchase['purchase_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['supplier_name'] ?? 'N/A'); ?></td>
                                    <td class="text-end">
                                        Rp <?php echo number_format($purchase['total_amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($purchase['created_by'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($purchase['created_at'])); ?></td>
                                    <td class="text-center">
                                        <a href="detail.php?id=<?php echo $purchase['purchase_id']; ?>" 
                                           class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
include $root_dir . 'views/layouts/footer.php'; 
?>