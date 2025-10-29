<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Item.php';
require_once __DIR__ . '/../../models/Category.php';

requireLogin();

$itemModel = new Item();
$categoryModel = new Category();

// HANYA memuat kategori, bukan SEMUA barang (Optimasi Memori)
$categories = $categoryModel->getAllCategories(); 

$pageTitle = 'Tambah Barang Baru';
$itemId = $_GET['id'] ?? null;
$item = [];
$error = [];

// Cek jika ini mode edit
if ($itemId) {
    // Memuat HANYA SATU barang
    $item = $itemModel->getItemById($itemId);
    if (!$item) {
        setFlashMessage('danger', 'Barang tidak ditemukan!');
        redirect(url('items/index.php'));
    }
    $pageTitle = 'Edit Barang: ' . htmlspecialchars($item['item_name']);
}

// Proses Form Submission (Tambah/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'sku' => trim($_POST['sku'] ?? ''),
        'barcode' => trim($_POST['barcode'] ?? ''),
        'item_name' => trim($_POST['item_name'] ?? ''),
        'category_id' => $_POST['category_id'] ?? null,
        'purchase_price' => (float) str_replace(['.', ','], ['', '.'], $_POST['purchase_price'] ?? 0),
        'selling_price' => (float) str_replace(['.', ','], ['', '.'], $_POST['selling_price'] ?? 0),
        'unit' => trim($_POST['unit'] ?? ''),
        'min_stock' => (int) ($_POST['min_stock'] ?? 0),
    ];

    // Validasi
    if (empty($data['sku'])) $error['sku'] = 'SKU wajib diisi.';
    if (empty($data['item_name'])) $error['item_name'] = 'Nama Barang wajib diisi.';
    if (empty($data['category_id'])) $error['category_id'] = 'Kategori wajib dipilih.';
    if ($data['purchase_price'] <= 0) $error['purchase_price'] = 'Harga Beli harus lebih dari 0.';
    if ($data['selling_price'] <= 0) $error['selling_price'] = 'Harga Jual harus lebih dari 0.';
    if (empty($data['unit'])) $error['unit'] = 'Satuan wajib diisi.';
    
    // Jika tidak ada error
    if (empty($error)) {
        try {
            if ($itemId) {
                // Mode Edit
                $itemModel->update($itemId, $data);
                setFlashMessage('success', 'Barang berhasil diupdate.');
            } else {
                // Mode Tambah
                $itemModel->create($data);
                setFlashMessage('success', 'Barang baru berhasil ditambahkan.');
            }
            redirect(url('items/index.php'));

        } catch (Exception $e) {
            setFlashMessage('danger', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    
    $item = array_merge($item, $data);
}

$flashMessage = getFlashMessage();

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2><i class="bi bi-box"></i> <?php echo $pageTitle; ?></h2>
    </div>
</div>

<?php if ($flashMessage): ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?>"><?php echo $flashMessage['message']; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU (Kode Barang) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($error['sku']) ? 'is-invalid' : ''; ?>" id="sku" name="sku" value="<?php echo htmlspecialchars($item['sku'] ?? ''); ?>" required>
                        <?php if (isset($error['sku'])): ?><div class="invalid-feedback"><?php echo $error['sku']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="barcode" class="form-label">Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo htmlspecialchars($item['barcode'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="item_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($error['item_name']) ? 'is-invalid' : ''; ?>" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name'] ?? ''); ?>" required>
                        <?php if (isset($error['item_name'])): ?><div class="invalid-feedback"><?php echo $error['item_name']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo isset($error['category_id']) ? 'is-invalid' : ''; ?>" id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php 
                            $selectedCategoryId = $item['category_id'] ?? null;
                            foreach ($categories as $category): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($selectedCategoryId == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($error['category_id'])): ?><div class="invalid-feedback"><?php echo $error['category_id']; ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="purchase_price" class="form-label">Harga Beli (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control number-format <?php echo isset($error['purchase_price']) ? 'is-invalid' : ''; ?>" id="purchase_price" name="purchase_price" value="<?php echo number_format($item['purchase_price'] ?? 0, 0, ',', '.'); ?>" required>
                        <?php if (isset($error['purchase_price'])): ?><div class="invalid-feedback"><?php echo $error['purchase_price']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="selling_price" class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control number-format <?php echo isset($error['selling_price']) ? 'is-invalid' : ''; ?>" id="selling_price" name="selling_price" value="<?php echo number_format($item['selling_price'] ?? 0, 0, ',', '.'); ?>" required>
                        <?php if (isset($error['selling_price'])): ?><div class="invalid-feedback"><?php echo $error['selling_price']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="unit" class="form-label">Satuan (Contoh: Pcs, Kg) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($error['unit']) ? 'is-invalid' : ''; ?>" id="unit" name="unit" value="<?php echo htmlspecialchars($item['unit'] ?? ''); ?>" required>
                        <?php if (isset($error['unit'])): ?><div class="invalid-feedback"><?php echo $error['unit']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="min_stock" class="form-label">Stok Minimum</label>
                        <input type="number" class="form-control" id="min_stock" name="min_stock" value="<?php echo htmlspecialchars($item['min_stock'] ?? 0); ?>" min="0">
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="<?php echo url('items/index.php'); ?>" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
            
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('.number-format');
    
    numberInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = new Intl.NumberFormat('id-ID').format(value);
        });
        
        if(input.value) {
            let value = input.value.replace(/[^0-9]/g, '');
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }
    });
});
</script>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>