<?php
// Pastikan file config dimuat pertama kali
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Customer.php';

requireAdmin();

$customerModel = new Customer();
$customer = [];
$isEdit = false;
$pageTitle = 'Tambah Pelanggan Baru';
$errors = getFlashMessage('errors') ?? [];

// Tentukan path root relatif
$root_dir = __DIR__ . '/../../'; 

// Cek apakah mode Edit
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $customer_id = $_GET['id'];
    $customer = $customerModel->findById($customer_id);

    if ($customer) {
        $isEdit = true;
        $pageTitle = 'Edit Pelanggan: ' . htmlspecialchars($customer['customer_name']);
    } else {
        setFlashMessage('error', 'Pelanggan tidak ditemukan.');
        header('Location: index.php');
        exit;
    }
}

// PERBAIKAN DI BARIS INI (Baris 28)
include $root_dir . 'views/layouts/header.php';
?>

<div class="container-fluid mt-4">

    <h2><i class="bi bi-person-lines-fill"></i> <?php echo $pageTitle; ?></h2>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            
            <form action="process.php" method="POST">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
                    <input type="hidden" name="action" value="update">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="customer_name" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?php echo isset($errors['customer_name']) ? 'is-invalid' : ''; ?>" 
                           id="customer_name" 
                           name="customer_name" 
                           value="<?php echo htmlspecialchars($_POST['customer_name'] ?? $customer['customer_name'] ?? ''); ?>" 
                           required>
                    <?php if (isset($errors['customer_name'])): ?><div class="invalid-feedback"><?php echo $errors['customer_name']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? $customer['phone'] ?? ''); ?>" 
                           required>
                    <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?php echo $errors['phone']; ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                              id="address" 
                              name="address"><?php echo htmlspecialchars($_POST['address'] ?? $customer['address'] ?? ''); ?></textarea>
                    <?php if (isset($errors['address'])): ?><div class="invalid-feedback"><?php echo $errors['address']; ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label">Kota</label>
                    <input type="text" 
                           class="form-control <?php echo isset($errors['city']) ? 'is-invalid' : ''; ?>" 
                           id="city" 
                           name="city" 
                           value="<?php echo htmlspecialchars($_POST['city'] ?? $customer['city'] ?? ''); ?>">
                    <?php if (isset($errors['city'])): ?><div class="invalid-feedback"><?php echo $errors['city']; ?></div><?php endif; ?>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Data</button>
                </div>
            </form>
            
        </div>
    </div>

</div>

<?php 
// PERBAIKAN DI BAGIAN INI JUGA
include $root_dir . 'views/layouts/footer.php'; 
?>