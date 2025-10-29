<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Item.php';

/**
 * Sale Transaction Model (POS)
 */
class SaleTransaction extends BaseModel {
    protected $table = 'sales_transactions';
    protected $primaryKey = 'sale_id';
    
    /**
     * Generate nomor transaksi penjualan
     */
    public function generateSaleNumber() {
        if (DB_TYPE === 'pgsql') {
            $sql = "SELECT generate_sale_number() as number";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return $result['number'];
        } else {
            $sql = "CALL generate_sale_number(@number)";
            $this->db->query($sql);
            $stmt = $this->db->query("SELECT @number as number");
            $result = $stmt->fetch();
            return $result['number'];
        }
    }
    
    /**
     * KODE INTI: Create sale transaction dengan pengurangan stok ATOMIK
     */
    public function createSale($headerData, $itemsData) {
        try {
            // BEGIN TRANSACTION - Semua operasi menjadi satu kesatuan
            $this->db->beginTransaction();
            
            // Generate sale number otomatis
            $saleNumber = $this->generateSaleNumber();
            $headerData['sale_number'] = $saleNumber;
            $headerData['sale_date'] = date('Y-m-d H:i:s');
            $headerData['created_by'] = $_SESSION['user_id'] ?? null;
            
            // Insert header transaksi
            $saleId = $this->insert($headerData);
            
            // Process items and update stock
            $itemModel = new Item();
            $subtotal = 0;
            $totalProfit = 0;
            
            foreach ($itemsData as $item) {
                // Get item info untuk validasi dan hitung profit
                $itemInfo = $itemModel->findById($item['item_id']);
                
                if (!$itemInfo) {
                    throw new Exception("Item ID {$item['item_id']} tidak ditemukan");
                }
                
                // Validasi stok SEBELUM mengurangi. Kolom di tabel items adalah stock_quantity.
                if ($itemInfo['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Stok {$itemInfo['item_name']} tidak cukup. Tersedia: {$itemInfo['stock_quantity']}");
                }
                
                // Calculate amounts
                $itemSubtotal = $item['quantity'] * $item['selling_price'];
                $itemProfit = ($item['selling_price'] - $itemInfo['purchase_price']) * $item['quantity'];
                
                // Insert sale detail
                $detailData = [
                    'sale_id' => $saleId,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'], 
                    'selling_price' => $item['selling_price'],
                    'purchase_price' => $itemInfo['purchase_price'],
                    'subtotal' => $itemSubtotal,
                    'profit' => $itemProfit
                ];
                
                // Menggunakan 'quantity' di query SQL
                $sql = "INSERT INTO sale_details (sale_id, item_id, quantity, selling_price, purchase_price, subtotal, profit) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $this->db->query($sql, array_values($detailData));
                
                // KUNCI: Update stock - MENGURANGI stok (quantity negatif)
                $itemModel->updateStock(
                    $item['item_id'], 
                    -$item['quantity'],  // Negatif untuk pengurangan
                    'sale', 
                    $saleId,
                    "Penjualan #{$saleNumber}"
                );
                
                $subtotal += $itemSubtotal;
                $totalProfit += $itemProfit;
            }
            
            // Update calculated values in header
            $this->update($saleId, [
                'subtotal' => $subtotal
            ]);
            
            // COMMIT - Semua berhasil, simpan permanent
            $this->db->commit();
            
            return [
                'sale_id' => $saleId,
                'sale_number' => $saleNumber,
                'total_profit' => $totalProfit
            ];
            
        } catch (Exception $e) {
            // ROLLBACK - Ada error, batalkan semua perubahan
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get sale with details
     */
    public function getSaleWithDetails($saleId) {
        // Get header
        $header = $this->findById($saleId);
        
        if (!$header) {
            return null;
        }
        
        // Get customer info
        if ($header['customer_id']) {
            $sql = "SELECT c.* FROM customers c WHERE c.customer_id = ?";
            $stmt = $this->db->query($sql, [$header['customer_id']]);
            $header['customer'] = $stmt->fetch();
        }
        
        // Get details
        $sql = "SELECT sd.*, i.item_name, i.sku, i.unit 
                FROM sale_details sd 
                INNER JOIN items i ON sd.item_id = i.item_id 
                WHERE sd.sale_id = ?";
        
        $stmt = $this->db->query($sql, [$saleId]);
        $header['items'] = $stmt->fetchAll();
        
        return $header;
    }
    
    // ✅ PERBAIKAN: Menggunakan 2 Query untuk mencegah duplikasi Net Sales
    /**
     * Get daily sales summary (Total Transaksi, Net Sales/Revenue, Total Profit)
     */
    public function getDailySales($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // 1. Hitung Net Sales (Pendapatan) dan Total Transaksi dari tabel header (sales_transactions)
        $sqlHeader = "SELECT 
                COUNT(sale_id) as total_transactions,
                SUM(total_amount) as net_sales
                FROM {$this->table}
                WHERE DATE(sale_date) = ?";
        
        $stmtHeader = $this->db->query($sqlHeader, [$date]);
        $headerResult = $stmtHeader->fetch();
        
        // 2. Hitung Total Profit dari tabel detail (sale_details)
        $sqlDetails = "SELECT 
                SUM(sd.profit) as total_profit
                FROM sale_details sd
                INNER JOIN {$this->table} st ON sd.sale_id = st.sale_id
                WHERE DATE(st.sale_date) = ?";

        $stmtDetails = $this->db->query($sqlDetails, [$date]);
        $detailResult = $stmtDetails->fetch();

        $result = $headerResult ? array_merge($headerResult, $detailResult) : [
            'total_transactions' => 0,
            'net_sales' => 0,
            'total_profit' => 0
        ];
        
        // Penanganan nilai nol jika tidak ada transaksi
        if (!$headerResult || $result['total_transactions'] == 0) {
            return [
                'total_transactions' => 0,
                'net_sales' => 0,
                'total_profit' => 0
            ];
        }
        
        // Memastikan total_profit adalah angka (meskipun null dari query)
        $result['total_profit'] = $result['total_profit'] ?? 0;

        return $result;
    }
    
    /**
     * Get sales report by date range
     */
    public function getSalesReport($startDate, $endDate) {
        $sql = "SELECT 
                DATE(sale_date) as date,
                COUNT(sale_id) as total_transactions,
                SUM(subtotal) as gross_sales,
                SUM(discount_amount) as total_discount,
                SUM(total_amount) as net_sales,
                COUNT(DISTINCT customer_id) as unique_customers
                FROM {$this->table}
                WHERE DATE(sale_date) BETWEEN ? AND ?
                GROUP BY DATE(sale_date)
                ORDER BY DATE(sale_date) DESC";
        
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get profit report
     */
    public function getProfitReport($startDate, $endDate) {
        // PERBAIKAN: Menghapus komentar // ✅ Menggunakan sd.quantity
        $sql = "SELECT 
                DATE(st.sale_date) as date,
                SUM(sd.subtotal) as revenue,
                SUM(sd.quantity * sd.purchase_price) as cost, 
                SUM(sd.profit) as gross_profit
                FROM {$this->table} st
                INNER JOIN sale_details sd ON st.sale_id = sd.sale_id
                WHERE DATE(st.sale_date) BETWEEN ? AND ?
                GROUP BY DATE(st.sale_date)
                ORDER BY DATE(st.sale_date) DESC";
        
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get top selling items
     */
    public function getTopSellingItems($startDate = null, $endDate = null, $limit = 10) {
        // PERBAIKAN: Menghapus komentar // ✅ Menggunakan sd.quantity
        $sql = "SELECT 
                i.item_id,
                i.item_name,
                i.sku,
                SUM(sd.quantity) as total_sold, 
                SUM(sd.subtotal) as total_revenue,
                SUM(sd.profit) as total_profit
                FROM sale_details sd
                INNER JOIN items i ON sd.item_id = i.item_id
                INNER JOIN {$this->table} st ON sd.sale_id = st.sale_id";
        
        if ($startDate && $endDate) {
            $sql .= " WHERE DATE(st.sale_date) BETWEEN ? AND ?";
            $params = [$startDate, $endDate, $limit];
        } else {
            $params = [$limit];
        }
        
        $sql .= " GROUP BY i.item_id, i.item_name, i.sku
                  ORDER BY total_sold DESC
                  LIMIT ?";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}