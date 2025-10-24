<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Item.php';

/**
 * Purchase Transaction Model
 * MENAMBAH stok barang dari supplier
 */
class PurchaseTransaction extends BaseModel {
    protected $table = 'purchase_transactions';
    protected $primaryKey = 'purchase_id';
    
    /**
     * Generate nomor transaksi pembelian
     */
    public function generatePurchaseNumber() {
        if (DB_TYPE === 'pgsql') {
            $sql = "SELECT generate_purchase_number() as number";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return $result['number'];
        } else {
            $sql = "CALL generate_purchase_number(@number)";
            $this->db->query($sql);
            $stmt = $this->db->query("SELECT @number as number");
            $result = $stmt->fetch();
            return $result['number'];
        }
    }
    
    /**
     * Create purchase transaction dengan detail items
     * ATOMIK: Semua operasi dalam satu transaksi
     */
    public function createPurchase($headerData, $itemsData) {
        try {
            $this->db->beginTransaction();
            
            // Generate purchase number
            $purchaseNumber = $this->generatePurchaseNumber();
            $headerData['purchase_number'] = $purchaseNumber;
            $headerData['created_by'] = $_SESSION['user_id'] ?? null;
            
            // Insert header
            $purchaseId = $this->insert($headerData);
            
            // Insert details and update stock
            $itemModel = new Item();
            $totalAmount = 0;
            
            foreach ($itemsData as $item) {
                // Insert purchase detail
                $detailData = [
                    'purchase_id' => $purchaseId,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'subtotal' => $item['quantity'] * $item['purchase_price']
                ];
                
                $sql = "INSERT INTO purchase_details (purchase_id, item_id, quantity, purchase_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $this->db->query($sql, array_values($detailData));
                
                // Update item stock (MENAMBAH)
                $itemModel->updateStock(
                    $item['item_id'], 
                    $item['quantity'], 
                    'purchase', 
                    $purchaseId,
                    "Pembelian #{$purchaseNumber}"
                );
                
                // Update purchase price in items table
                $itemModel->update($item['item_id'], [
                    'purchase_price' => $item['purchase_price']
                ]);
                
                $totalAmount += $detailData['subtotal'];
            }
            
            // Update total amount in header
            $this->update($purchaseId, ['total_amount' => $totalAmount]);
            
            $this->db->commit();
            return $purchaseId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get purchase with details
     */
    public function getPurchaseWithDetails($purchaseId) {
        // Get header
        $header = $this->findById($purchaseId);
        
        if (!$header) {
            return null;
        }
        
        // Get supplier info
        $sql = "SELECT s.* FROM suppliers s WHERE s.supplier_id = ?";
        $stmt = $this->db->query($sql, [$header['supplier_id']]);
        $header['supplier'] = $stmt->fetch();
        
        // Get details
        $sql = "SELECT pd.*, i.item_name, i.sku, i.unit 
                FROM purchase_details pd 
                INNER JOIN items i ON pd.item_id = i.item_id 
                WHERE pd.purchase_id = ?";
        
        $stmt = $this->db->query($sql, [$purchaseId]);
        $header['items'] = $stmt->fetchAll();
        
        return $header;
    }
    
    /**
     * Get all purchases with supplier info
     */
    public function getAllPurchasesWithSupplier($limit = 100) {
        $sql = "SELECT pt.*, s.supplier_name 
                FROM {$this->table} pt 
                LEFT JOIN suppliers s ON pt.supplier_id = s.supplier_id 
                ORDER BY pt.purchase_date DESC, pt.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
}
