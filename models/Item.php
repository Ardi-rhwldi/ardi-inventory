<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/StockMovement.php';

/**
 * Item Model
 */
class Item extends BaseModel {
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    
    // --- METHOD UNTUK TRANSAKSI (PERBAIKAN ERROR) ---
    /**
     * Memulai transaksi database (Proxy untuk $this->db->beginTransaction())
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }
    
    /**
     * Commit transaksi (Proxy untuk $this->db->commit())
     */
    public function commit() {
        $this->db->commit();
    }
    
    /**
     * Rollback transaksi (Proxy untuk $this->db->rollBack())
     */
    public function rollBack() {
        $this->db->rollBack();
    }
    // --- AKHIR METHOD PERBAIKAN ERROR ---

    // --- METHOD UNTUK CRUD YANG DIGUNAKAN DI form.php ---
    
    /**
     * Get item by ID (Menggunakan method bawaan BaseModel)
     */
    public function getItemById($id) {
        return $this->findById($id); 
    }

    /**
     * Create a new item (Digunakan untuk Form Tambah)
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = $data['is_active'] ?? true; 
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;

        $itemId = $this->insert($data); 
        
        // Catat stok awal sebagai pergerakan 'initial_stock'
        if ($itemId && $data['stock_quantity'] > 0) {
            $this->logStockMovement(
                $itemId, 
                $data['stock_quantity'], 
                0, // Before: 0
                $data['stock_quantity'], // After: Stok awal
                'initial_stock', 
                $itemId, 
                'Stok awal saat pendaftaran item',
                $_SESSION['user_id'] ?? null
            );
        }
        return $itemId;
    }
    
    /**
     * Update an existing item (Digunakan untuk Form Edit)
     */
    public function update($id, $data) {
        // Hapus stock_quantity dari data update karena hanya boleh diubah via updateStock
        if (isset($data['stock_quantity'])) {
            unset($data['stock_quantity']); 
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        // PENTING: Memanggil update dari BASEMODEL menggunakan parent::update()
        return parent::update($id, $data); 
    }

    /**
     * Soft delete an item (Mengubah is_active menjadi false)
     */
    public function delete($id) {
        return parent::update($id, ['is_active' => false, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // --- KODE INTI: METHOD updateStock UNTUK INTEGRASI TRANSAKSI ---
    
    /**
     * Update stock quantity dan mencatat pergerakan stok.
     */
    public function updateStock($itemId, $quantityChange, $movementType = 'adjustment', $referenceId = null, $notes = null) {
        $item = $this->findById($itemId);
        if (!$item) {
            throw new Exception("Barang ID {$itemId} tidak ditemukan.");
        }
        
        $quantityBefore = $item['stock_quantity']; 
        $quantityAfter = $quantityBefore + $quantityChange;
        
        // Pengecekan krusial: mencegah stok menjadi negatif
        if ($quantityAfter < 0) {
            throw new Exception("Stok untuk '{$item['item_name']}' tidak cukup. Tersedia: {$quantityBefore}");
        }
        
        // 1. Menggunakan parent::update untuk mengubah stok di tabel items
        parent::update($itemId, [ 
            'stock_quantity' => $quantityAfter, 
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // 2. Mencatat Pergerakan Stok dengan detail kuantitas
        $this->logStockMovement(
            $itemId, 
            $quantityChange, 
            $quantityBefore, 
            $quantityAfter,
            $movementType, 
            $referenceId, 
            $notes, 
            $_SESSION['user_id'] ?? null
        );

        return true;
    }
    
    /**
     * Method khusus untuk pengurangan stok saat transaksi penjualan (Sale).
     */
    public function updateStockForSale($itemId, $quantity, $saleId) {
        // Kuantitas yang diubah harus NEGATIF karena ini adalah PENGURANGAN stok.
        $quantityChange = -abs($quantity); 
        $movementType = 'sale';
        $notes = "Pengurangan stok dari Transaksi Penjualan #{$saleId}";
        
        return $this->updateStock(
            $itemId, 
            $quantityChange, 
            $movementType, 
            $saleId, 
            $notes
        );
    }


    /**
     * Fungsi helper untuk mencatat pergerakan stok ke StockMovement Model.
     */
    protected function logStockMovement($itemId, $quantityChange, $quantityBefore, $quantityAfter, $movementType, $referenceId, $notes, $userId) {
        $movementModel = new StockMovement();
        
        $movementModel->logMovement(
            $itemId, 
            $quantityChange, 
            $quantityBefore, 
            $quantityAfter,
            $movementType, 
            $referenceId, 
            $notes, 
            $userId
        );
    }
    
    // --- METHOD UNTUK INDEX / REPORT ---
    
    /**
     * Get all active items
     */
    public function getActiveItems($orderBy = 'item_name ASC') {
        return $this->findWhere('is_active = ?', [true]);
    }
    
    
    /**
     * Get items with category 
     */
    public function getItemsWithCategory() {
        $sql = "SELECT i.*, c.category_name 
                FROM {$this->table} i 
                LEFT JOIN categories c ON i.category_id = c.category_id 
                WHERE i.is_active = ? 
                ORDER BY i.item_name";
        
        $stmt = $this->db->query($sql, [true]);
        return $stmt->fetchAll();
    }
    
    // START: METHOD PENCARIAN BARANG
    /**
     * Search items by SKU, Barcode, or Name.
     */
    public function searchItems(string $keyword): array
    {
        // 1. Siapkan nilai keyword dengan wildcard
        $keywordWildcard = '%' . $keyword . '%'; 

        // 2. Gunakan placeholder unik untuk setiap pemanggilan LIKE
        $sql = "
            SELECT 
                i.*, 
                c.category_name 
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.category_id
            WHERE 
                i.is_active = 1 AND 
                (
                    i.sku LIKE :keyword1 OR      
                    i.barcode LIKE :keyword2 OR    
                    i.item_name LIKE :keyword3
                )
            ORDER BY i.item_name ASC
        ";

        // 3. Petakan nilai keyword ke setiap placeholder unik
        $params = [
            ':keyword1' => $keywordWildcard,
            ':keyword2' => $keywordWildcard,
            ':keyword3' => $keywordWildcard
        ];

        return $this->db->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
    // END: METHOD PENCARIAN BARANG
    
    /**
     * Get low stock items (stok <= min_stock)
     */
    public function getLowStockItems() {
        $sql = "SELECT i.*, c.category_name 
                FROM {$this->table} i 
                LEFT JOIN categories c ON i.category_id = c.category_id 
                WHERE i.stock_quantity <= i.min_stock 
                AND i.is_active = ? 
                ORDER BY i.stock_quantity ASC";
        
        $stmt = $this->db->query($sql, [true]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get stock report (using view)
     */
    public function getStockReport() {
        return $this->query("SELECT * FROM v_stock_report ORDER BY item_name")->fetchAll();
    }
    
    /**
     * Check if SKU exists (for validation)
     */
    public function skuExists($sku, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE sku = ? AND item_id != ?";
            $stmt = $this->db->query($sql, [$sku, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE sku = ?";
            $stmt = $this->db->query($sql, [$sku]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Get all items (Diperlukan oleh form.php untuk JS lookup)
     */
    public function getAll() {
        // Mengambil semua item aktif, diurutkan berdasarkan nama
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY item_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}

