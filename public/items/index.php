<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Item.php';
require_once __DIR__ . '/../../models/Category.php';

requireLogin();

$pageTitle = 'Kelola Barang';
$itemModel = new Item();
$categoryModel = new Category();

$items = $itemModel->getItemsWithCategory();
$categories = $categoryModel->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        try {
            $itemModel->delete($_POST['item_id']);
            setFlashMessage('success', 'Barang berhasil dihapus');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Error: ' . $e->getMessage());
        }
        redirect(url('items/index.php'));
    }
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="bi bi-box"></i> Kelola Barang</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo url('items/form.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Barang Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="itemsTable">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($item['sku']); ?></code></td>
                        <td><small><?php echo htmlspecialchars($item['barcode'] ?? '-'); ?></small></td>
                        <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['category_name'] ?? '-'); ?></span></td>
                        <td><?php echo formatRupiah($item['purchase_price']); ?></td>
                        <td><strong><?php echo formatRupiah($item['selling_price']); ?></strong></td>
                        <td>
                            <span class="badge <?php echo $item['stock_quantity'] <= $item['min_stock'] ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $item['stock_quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td>
                            <a href="<?php echo url('items/form.php?id=' . $item['item_id']); ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus barang ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
