<?php
// C:\xampp 1\htdocs\ardi-inventory\public\suppliers\form.php

// Sertakan file konfigurasi dan model
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Supplier.php'; 

// Minta user untuk login (asumsi fungsi ini ada di config/config.php)
requireLogin(); 

$supplierModel = new Supplier();
$pageTitle = 'Tambah Supplier Baru';
$supplierData = [];
$supplierId = $_GET['id'] ?? null;
$isEdit = false;

// Cek apakah mode Edit
if ($supplierId) {
    $supplierData = $supplierModel->findById($supplierId);
    
    if ($supplierData) {
        $pageTitle = 'Edit Supplier: ' . htmlspecialchars($supplierData['supplier_name']);
        $isEdit = true;
    } else {
        // Jika ID tidak ditemukan
        $_SESSION['error_message'] = "Supplier ID tidak ditemukan.";
        header('Location: index.php');
        exit;
    }
}

// Sertakan header dan layout
include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container-fluid mt-4">
    <h2><i class="fas fa-truck"></i> <?= $pageTitle ?></h2>
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <form action="action.php" method="POST">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="supplier_id" value="<?= $supplierId ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="supplier_name" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="supplier_name" 
                           name="supplier_name" 
                           value="<?= htmlspecialchars($supplierData['supplier_name'] ?? '') ?>" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="contact_person" class="form-label">Nama Kontak (Contact Person)</label>
                    <input type="text" 
                           class="form-control" 
                           id="contact_person" 
                           name="contact_person" 
                           value="<?= htmlspecialchars($supplierData['contact_person'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone" 
                           name="phone" 
                           value="<?= htmlspecialchars($supplierData['phone'] ?? '') ?>" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($supplierData['address'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label">Kota</label>
                    <input type="text" 
                           class="form-control" 
                           id="city" 
                           name="city" 
                           value="<?= htmlspecialchars($supplierData['city'] ?? '') ?>">
                </div>
                
                <?php if ($isEdit): ?>
                <div class="mb-3 form-check">
                    <?php 
                        $isActive = ($supplierData['is_active'] ?? 1) == 1;
                    ?>
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?= $isActive ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Supplier Aktif</label>
                    <small class="form-text text-muted">Hapus centang untuk menonaktifkan supplier.</small>
                </div>
                <?php endif; ?>

                <hr>
                <button type="submit" name="submit_supplier" class="btn btn-success"><i class="fas fa-save"></i> Simpan Supplier</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layouts/footer.php';
?>