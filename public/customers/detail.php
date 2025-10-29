<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Customer.php';

requireAdmin();

$customerModel = new Customer();
$customer = null;

// Tentukan path root relatif
$root_dir = __DIR__ . '/../../'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'ID pelanggan tidak valid.');
    header('Location: index.php');
    exit;
}

$customer_id = $_GET['id'];
$customer = $customerModel->getCustomerWithStats($customer_id);

if (!$customer) {
    setFlashMessage('error', 'Pelanggan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Detail Pelanggan: ' . htmlspecialchars($customer['customer_name']);

// PERBAIKAN: Menggunakan $root_dir
include $root_dir . 'views/layouts/header.php';
?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-info-circle"></i> <?php echo $pageTitle; ?></h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Informasi Kontak
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($customer['customer_name']); ?></p>
                    <p><strong>Telepon:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? '-'); ?></p>
                    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
                    <p><strong>Kota:</strong> <?php echo htmlspecialchars($customer['city']); ?></p>
                    <p><strong>Terdaftar Sejak:</strong> <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    Statistik Pembelian
                </div>
                <div class="card-body">
                    <p>
                        <strong>Total Pembelian:</strong> 
                        <span class="float-end badge bg-primary"><?php echo number_format($customer['total_purchases']); ?> Transaksi</span>
                    </p>
                    <hr>
                    <p>
                        <strong>Total Belanja:</strong> 
                        <span class="float-end badge bg-success">Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?></span>
                    </p>
                    <hr>
                    <p>
                        <strong>Poin Loyalitas:</strong> 
                        <span class="float-end badge bg-info text-dark"><?php echo number_format($customer['points'] ?? 0); ?> Poin</span>
                    </p>
                </div>
            </div>
        </div>
        
    </div>

</div>

<?php 
// PERBAIKAN: Menggunakan $root_dir
include $root_dir . 'views/layouts/footer.php'; 
?>