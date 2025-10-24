<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Item Model
 * INTI SISTEM: Data harga jual dan stok digunakan oleh sistem kasir
 */
class Item extends BaseModel {
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    
    /**
     * Get all active items
     */
    public function getActiveItems($orderBy = 'item_name ASC') {
        return $this->findWhere('is_active = ?', [true]);
    }
    
    /**
     * Get item by SKU
     */
    public function findBySku($sku) {
        return $this->findOneWhere('sku = ?', [$sku]);
    }
    
    /**
     * Get item by barcode
     */
    public function findByBarcode($barcode) {
        return $this->findOneWhere('barcode = ?', [$barcode]);
    }
    
    /**
     * Search items by name or SKU
     */
    public function search($keyword) {
        $likeOp = DB_TYPE === 'pgsql' ? 'ILIKE' : 'LIKE';
        $sql = "SELECT * FROM {$this->table} 
                WHERE (item_name {$likeOp} ? OR sku {$likeOp} ? OR barcode {$likeOp} ?) 
                AND is_active = ? 
                ORDER BY item_name";
        
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, true]);
        return $stmt->fetchAll();
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
     * Update stock quantity
     * PENTING: Digunakan saat transaksi pembelian dan penjualan
     * TIDAK memulai transaksi sendiri - caller bertanggung jawab untuk transaction management
     */
    public function updateStock($itemId, $quantityChange, $movementType = 'adjustment', $referenceId = null, $notes = null) {
        // Get current stock
        $item = $this->findById($itemId);
        if (!$item) {
            throw new Exception("Item not found");
        }
        
        $quantityBefore = $item['stock_quantity'];
        $quantityAfter = $quantityBefore + $quantityChange;
        
        // Check if stock becomes negative
        if ($quantityAfter < 0) {
            throw new Exception("Insufficient stock. Available: {$quantityBefore}");
        }
        
        // Update stock
        $this->update($itemId, [
            'stock_quantity' => $quantityAfter,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log stock movement
        $sql = "INSERT INTO stock_movements 
                (item_id, movement_type, reference_id, quantity_before, quantity_change, quantity_after, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $itemId,
            $movementType,
            $referenceId,
            $quantityBefore,
            $quantityChange,
            $quantityAfter,
            $notes,
            $_SESSION['user_id'] ?? null
        ]);
        
        return true;
    }
    
    /**
     * Get stock report (using view)
     */
    public function getStockReport() {
        $sql = "SELECT * FROM v_stock_report ORDER BY item_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
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
}
