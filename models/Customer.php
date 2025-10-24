<?php
require_once __DIR__ . '/BaseModel.php';

class Customer extends BaseModel {
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    
    /**
     * Get all active customers
     */
    public function getActiveCustomers() {
        return $this->findWhere('is_active = ?', [true]);
    }
    
    /**
     * Search customers
     */
    public function search($keyword) {
        $likeOp = DB_TYPE === 'pgsql' ? 'ILIKE' : 'LIKE';
        $sql = "SELECT * FROM {$this->table} 
                WHERE (customer_name {$likeOp} ? OR phone {$likeOp} ? OR email {$likeOp} ?) 
                ORDER BY customer_name";
        
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get customer with purchase stats
     */
    public function getCustomerWithStats($customerId) {
        $sql = "SELECT c.*, 
                COUNT(st.sale_id) as total_purchases,
                COALESCE(SUM(st.total_amount), 0) as total_spent
                FROM {$this->table} c 
                LEFT JOIN sales_transactions st ON c.customer_id = st.customer_id 
                WHERE c.customer_id = ? 
                GROUP BY c.customer_id";
        
        $stmt = $this->db->query($sql, [$customerId]);
        return $stmt->fetch();
    }
    
    /**
     * Update customer points
     */
    public function addPoints($customerId, $points) {
        $sql = "UPDATE {$this->table} SET points = points + ? WHERE customer_id = ?";
        return $this->db->query($sql, [$points, $customerId]);
    }
}
