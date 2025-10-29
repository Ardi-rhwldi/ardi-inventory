<?php
// Pastikan file config dimuat pertama kali
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Customer.php';

// HANYA ADMIN YANG BOLEH AKSES
requireAdmin();

$customerModel = new Customer();
$pageTitle = 'Kelola Data Pelanggan';
$flashMessage = getFlashMessage();

// --- LOGIKA PENCARIAN & PENGAMBILAN DATA ---
$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    // Menggunakan method search dari Customer Model
    $customers = $customerModel->search($keyword);
    $subtitle = 'Hasil Pencarian Pelanggan untuk: "' . htmlspecialchars($keyword) . '"';
} else {
    // Menggunakan method getActiveCustomers dari Customer Model
    $customers = $customerModel->getActiveCustomers();
    $subtitle = 'Daftar Pelanggan Aktif';
}

// PERBAIKAN: Ganti include ROOT_PATH dengan include yang menggunakan jalur relatif atau __DIR__
// Asumsi: Jika config.php sudah mendefinisikan ROOT_PATH, kita tetap bisa menggunakannya.
// Tapi untuk mencegah error, kita gunakan jalur relatif yang lebih aman jika ROOT_PATH belum tersedia.
// Baris ke-27 yang error ada di bagian ini:
// include ROOT_PATH . '/views/layouts/header.php';

// Menggunakan jalur relatif yang aman (Asumsi struktur file Anda):
$root_dir = __DIR__ . '/../../'; 
include $root_dir . 'views/layouts/header.php'; // Baris 27 (Perbaikan)

?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-people"></i> <?php echo $pageTitle; ?></h2>
        <a href="form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pelanggan Baru</a>
    </div>

    <?php if ($flashMessage): ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?>"><?php echo $flashMessage['message']; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="index.php">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Cari nama, telepon, atau email pelanggan..." 
                           name="keyword" 
                           value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                    <?php if (!empty($keyword)): ?>
                    <a href="index.php" class="btn btn-outline-danger">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?= $subtitle ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>Telepon</th>
                            <th>Alamat & Kota</th>
                            <th>Poin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php if (!empty($keyword)): ?>
                                        Data pelanggan dengan kata kunci "**<?= htmlspecialchars($keyword) ?>**" tidak ditemukan.
                                    <?php else: ?>
                                        Belum ada data pelanggan aktif.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['address']) . ', ' . htmlspecialchars($customer['city']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark"><?php echo number_format($customer['points'] ?? 0); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="form.php?id=<?php echo $customer['customer_id']; ?>" 
                                           class="btn btn-sm btn-warning mb-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $customer['customer_id']; ?>" 
                                           class="btn btn-sm btn-danger mb-1" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan pelanggan ini?')"
                                           title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="detail.php?id=<?php echo $customer['customer_id']; ?>" 
                                           class="btn btn-sm btn-secondary mb-1" title="Detail">
                                            <i class="bi bi-info-circle"></i>
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
// PERBAIKAN: Ganti include ROOT_PATH dengan jalur relatif yang sama
include $root_dir . 'views/layouts/footer.php'; 
?>