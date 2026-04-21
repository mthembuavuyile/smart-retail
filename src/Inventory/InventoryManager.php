<?php
/**
 * Manages product stock levels.
 *
 * Provides stock validation and deduction for the order pipeline,
 * plus full inventory listing for the admin panel.
 */
class InventoryManager {
    private $pdo;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    /**
     * Checks whether enough stock exists for a given product.
     *
     * @param int $productID   The product to check.
     * @param int $requiredQty The quantity needed.
     * @return bool True if stock is sufficient.
     */
    public function checkStock($productID, $requiredQty) {
        $stmt = $this->pdo->prepare(
            'SELECT StockLevel FROM Inventory WHERE ProductID = :productID'
        );
        $stmt->execute([':productID' => $productID]);
        $stockLevel = $stmt->fetchColumn();

        return ($stockLevel !== false && $stockLevel >= $requiredQty);
    }

    /**
     * Deducts stock for a batch of products.
     * Should only be called after checkStock() has validated availability.
     *
     * @param array $items Map of ProductID => quantity to deduct.
     */
    public function deductStock(array $items) {
        $stmt = $this->pdo->prepare(
            'UPDATE Inventory SET StockLevel = StockLevel - :quantity
             WHERE ProductID = :productID'
        );

        foreach ($items as $productID => $quantity) {
            $stmt->execute([
                ':quantity'  => $quantity,
                ':productID' => $productID,
            ]);
        }
    }

    /**
     * Returns the full inventory list with product details.
     * Used by the admin inventory management page.
     *
     * @return array Each row contains ProductID, Name, SKU, StockLevel, LowStockThreshold.
     */
    public function getAllInventory() {
        try {
            $stmt = $this->pdo->query(
                'SELECT p.ProductID, p.Name, p.SKU, i.StockLevel, i.LowStockThreshold
                 FROM Inventory i
                 JOIN Products p ON i.ProductID = p.ProductID
                 ORDER BY p.Name ASC'
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Failed to fetch inventory: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sets the stock level for a specific product.
     *
     * @param int $productID The product to update.
     * @param int $newStock  The new absolute stock level (must be >= 0).
     * @return bool True on success.
     */
    public function updateStock($productID, $newStock) {
        if ($newStock < 0) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE Inventory SET StockLevel = :stock WHERE ProductID = :productId'
            );
            $stmt->execute([':stock' => $newStock, ':productId' => $productID]);
            return true;

        } catch (PDOException $e) {
            error_log("Failed to update stock for Product #$productID: " . $e->getMessage());
            return false;
        }
    }
}