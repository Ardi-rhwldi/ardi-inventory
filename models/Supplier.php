<?php
require_once __DIR__ . '/BaseModel.php';

class Supplier extends BaseModel {
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    
    /**
     * Get all active suppliers
     */
    public function getActiveSuppliers() {
        return $this->findWhere('is_active = ?', [true]);
    }
    
    /**
     * Get ALL active suppliers (Diperlukan oleh form.php untuk dropdown)
     */
    public function getAllActiveSuppliers() {
        // Asumsi 'is_active' menggunakan integer (1)
        return $this->findWhere('is_active = ?', [1]);
    }
    /**
     * Search suppliers
     */
    public function search($keyword) {
        $likeOp = DB_TYPE === 'pgsql' ? 'ILIKE' : 'LIKE';
        $sql = "SELECT * FROM {$this->table} 
                WHERE (supplier_name {$likeOp} ? OR contact_person {$likeOp} ? OR phone {$likeOp} ?) 
                ORDER BY supplier_name";
        
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get supplier with purchase stats
     */
    public function getSupplierWithStats($supplierId) {
        $sql = "SELECT s.*, 
                COUNT(pt.purchase_id) as total_purchases,
                COALESCE(SUM(pt.total_amount), 0) as total_spent
                FROM {$this->table} s 
                LEFT JOIN purchase_transactions pt ON s.supplier_id = pt.supplier_id 
                WHERE s.supplier_id = ? 
                GROUP BY s.supplier_id";
        
        $stmt = $this->db->query($sql, [$supplierId]);
        return $stmt->fetch();
    }
}
