<?php
// C:\xampp 1\htdocs\ardi-inventory\public\suppliers\index.php

// 1. Sertakan file konfigurasi dan model
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Supplier.php'; 

// Minta user untuk login (asumsi fungsi ini ada di config/config.php)
requireLogin(); 
requireAdmin();
// 2. Inisiasi Model
$supplierModel = new Supplier();

// 3. Logika Ambil Data (Termasuk Pencarian)
$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    // Jika ada keyword, panggil method search()
    $suppliers = $supplierModel->search($keyword); 
    $title = 'Hasil Pencarian untuk: "' . htmlspecialchars($keyword) . '"';
} else {
    // Jika tidak ada keyword, panggil method getActiveSuppliers()
    $suppliers = $supplierModel->getActiveSuppliers(); 
    $title = 'Kelola Supplier Aktif';
}


// Sertakan header dan layout
include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-truck"></i> <?= $title ?></h2>
        <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Supplier Baru</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="index.php">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Cari nama, kontak, atau telepon supplier..." 
                           name="keyword" 
                           value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i> Cari</button>
                    <?php if (!empty($keyword)): ?>
                    <a href="index.php" class="btn btn-outline-danger"><i class="fas fa-times"></i> Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Supplier</th>
                            <th>Kontak</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (is_array($suppliers) && count($suppliers) > 0):
                            foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['supplier_id']) ?></td>
                            <td><?= htmlspecialchars($supplier['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                            <td><?= htmlspecialchars($supplier['phone']) ?></td>
                            <td><?= htmlspecialchars($supplier['address'] . ', ' . $supplier['city']) ?></td>
                            
                            <!-- PERUBAHAN UTAMA DI SINI -->
                            <td class="d-flex justify-content-center align-items-center">
                                <!-- Tombol EDIT (Kuning) - Menggunakan btn-sm dan me-1 untuk jarak minimal -->
                                <a href="form.php?id=<?= $supplier['supplier_id'] ?>" 
                                   class="btn btn-warning btn-sm me-1" 
                                   title="Edit Data Supplier">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <!-- Tombol HAPUS/NONAKTIFKAN (Merah) -->
                                <a href="delete.php?id=<?= $supplier['supplier_id'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   title="Nonaktifkan Supplier"
                                   onclick="return confirm('Yakin ingin menonaktifkan supplier ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; 
                        else:
                        ?>
                         <tr>
                            <td colspan="6" class="text-center">
                                <?php if (!empty($keyword)): ?>
                                    Data supplier dengan kata kunci "**<?= htmlspecialchars($keyword) ?>**" tidak ditemukan.
                                <?php else: ?>
                                    Tidak ada data supplier aktif.
                                <?php endif; ?>
                            </td>
                         </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layouts/footer.php';
?>