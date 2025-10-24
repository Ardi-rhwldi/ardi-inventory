<?php
require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    
    /**
     * Get all categories ordered by name
     */
    public function getAllCategories() {
        return $this->findAll('category_name ASC');
    }
    
    /**
     * Get category with item count
     */
    public function getCategoriesWithItemCount() {
        $sql = "SELECT c.*, COUNT(i.item_id) as item_count 
                FROM {$this->table} c 
                LEFT JOIN items i ON c.category_id = i.category_id AND i.is_active = true 
                GROUP BY c.category_id 
                ORDER BY c.category_name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
