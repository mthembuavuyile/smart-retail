<?php
/**
 * Generates business reports for the admin dashboard.
 *
 * Provides sales summaries, low-stock alerts, and aggregate
 * statistics used by the dashboard overview widgets.
 */
class ReportManager {
    private $pdo;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    /**
     * Returns daily revenue and order counts within a date range.
     * Only includes orders with status "Shipped" or "Completed".
     *
     * @param string $startDate Start of range (Y-m-d).
     * @param string $endDate   End of range (Y-m-d).
     * @return array Rows with Day, DailyRevenue, and NumberOfOrders.
     */
    public function getSalesReport($startDate, $endDate) {
        try {
            $endDateFull = $endDate . ' 23:59:59';

            $stmt = $this->pdo->prepare(
                'SELECT DATE(OrderDate) AS Day,
                        SUM(TotalAmount) AS DailyRevenue,
                        COUNT(OrderID) AS NumberOfOrders
                 FROM Orders
                 WHERE Status IN ("Shipped", "Completed")
                   AND OrderDate BETWEEN :startDate AND :endDate
                 GROUP BY Day
                 ORDER BY Day ASC'
            );
            $stmt->execute([':startDate' => $startDate, ':endDate' => $endDateFull]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Sales report failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns all products whose stock is at or below their low-stock threshold.
     *
     * @return array Rows with Name, SKU, StockLevel, and LowStockThreshold.
     */
    public function getLowStockReport() {
        try {
            $stmt = $this->pdo->query(
                'SELECT p.Name, p.SKU, i.StockLevel, i.LowStockThreshold
                 FROM Inventory i
                 JOIN Products p ON i.ProductID = p.ProductID
                 WHERE i.StockLevel <= i.LowStockThreshold
                 ORDER BY i.StockLevel ASC'
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Low stock report failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns aggregate counts for the admin dashboard overview.
     *
     * @return array Associative array with keys: total_revenue, order_count,
     *               customer_count, product_count, pending_orders.
     */
    public function getDashboardStats() {
        $stats = [
            'total_revenue'  => 0,
            'order_count'    => 0,
            'customer_count' => 0,
            'product_count'  => 0,
            'pending_orders' => 0,
        ];

        try {
            // Revenue from completed/shipped orders
            $row = $this->pdo->query(
                'SELECT COALESCE(SUM(TotalAmount), 0) AS revenue
                 FROM Orders WHERE Status IN ("Shipped", "Completed")'
            )->fetch();
            $stats['total_revenue'] = $row['revenue'];

            $row = $this->pdo->query('SELECT COUNT(*) AS cnt FROM Orders')->fetch();
            $stats['order_count'] = $row['cnt'];

            $row = $this->pdo->query('SELECT COUNT(*) AS cnt FROM Customers')->fetch();
            $stats['customer_count'] = $row['cnt'];

            $row = $this->pdo->query('SELECT COUNT(*) AS cnt FROM Products')->fetch();
            $stats['product_count'] = $row['cnt'];

            $row = $this->pdo->query(
                'SELECT COUNT(*) AS cnt FROM Orders WHERE Status = "Pending"'
            )->fetch();
            $stats['pending_orders'] = $row['cnt'];

        } catch (PDOException $e) {
            error_log('Dashboard stats failed: ' . $e->getMessage());
        }

        return $stats;
    }
}