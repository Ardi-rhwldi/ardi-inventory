<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Stock Movement Model
 * Mencatat setiap pergerakan stok (in/out)
 */
class StockMovement extends BaseModel {
    protected $table = 'stock_movements';
    protected $primaryKey = 'movement_id';

    /**
     * Mencatat pergerakan stok baru ke tabel stock_movements
     */
    public function logMovement($itemId, $quantityChange, $quantityBefore, $quantityAfter, $movementType, $referenceId = null, $notes = null, $userId = null) {
        $data = [
            'item_id' => $itemId,
            'movement_type' => $movementType,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => $userId,
            'movement_date' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    public function getMovementHistory($itemId = null, $startDate = null, $endDate = null) {
        $sql = "SELECT sm.*, i.item_name, i.sku, u.full_name as user_name
                FROM {$this->table} sm
                INNER JOIN items i ON sm.item_id = i.item_id
                LEFT JOIN users u ON sm.user_id = u.user_id
                WHERE 1=1";
        $params = [];
        
        if ($itemId) {
            $sql .= " AND sm.item_id = ?";
            $params[] = $itemId;
        }
        
        if ($startDate && $endDate) {
            $sql .= " AND DATE(sm.movement_date) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY sm.movement_date DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}