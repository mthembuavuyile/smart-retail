<?php
/**
 * Handles order creation, retrieval, and status management.
 *
 * Order creation is wrapped in a database transaction so that the order,
 * its line items, and the inventory deductions either all succeed or all roll back.
 */

require_once __DIR__ . '/../Inventory/InventoryManager.php';

class OrderManager {
    private $pdo;
    private $inventoryManager;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
        $this->inventoryManager = new InventoryManager($db_connection);
    }

    /**
     * Creates a new order from the current cart contents.
     *
     * Validates stock availability for every item before committing.
     * On success the order row, all line items, and inventory deductions
     * are committed atomically.
     *
     * @param int   $customerID        The purchasing customer's ID.
     * @param array $cartItems         Map of ProductID => quantity.
     * @param int   $shippingAddressID The delivery address ID.
     * @return int|false The new OrderID on success, false on failure.
     */
    public function createOrder($customerID, $cartItems, $shippingAddressID) {
        if (empty($cartItems) || !$customerID) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            // Verify stock for every item before proceeding
            foreach ($cartItems as $productId => $quantity) {
                if (!$this->inventoryManager->checkStock($productId, $quantity)) {
                    $this->pdo->rollBack();
                    error_log("Order failed: insufficient stock for Product $productId");
                    return false;
                }
            }

            // Insert the order header with a zero total (updated below)
            $stmt = $this->pdo->prepare(
                'INSERT INTO Orders (CustomerID, ShippingAddressID, Status, TotalAmount)
                 VALUES (:customerID, :shippingAddressID, "Pending", 0)'
            );
            $stmt->execute([
                ':customerID'        => $customerID,
                ':shippingAddressID' => $shippingAddressID,
            ]);

            $orderID = $this->pdo->lastInsertId();
            $totalAmount = 0;

            // Insert each line item and accumulate the total
            $itemStmt = $this->pdo->prepare(
                'INSERT INTO OrderItems (OrderID, ProductID, Quantity, PriceAtPurchase)
                 VALUES (:orderID, :productID, :quantity, :price)'
            );

            foreach ($cartItems as $productId => $quantity) {
                $price = $this->getProductPrice($productId);
                $itemStmt->execute([
                    ':orderID'   => $orderID,
                    ':productID' => $productId,
                    ':quantity'  => $quantity,
                    ':price'     => $price,
                ]);
                $totalAmount += $price * $quantity;
            }

            // Write back the computed total
            $update = $this->pdo->prepare(
                'UPDATE Orders SET TotalAmount = :total WHERE OrderID = :id'
            );
            $update->execute([':total' => $totalAmount, ':id' => $orderID]);

            // Deduct inventory
            $this->inventoryManager->deductStock($cartItems);

            $this->pdo->commit();
            return $orderID;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Order creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns all orders placed by a specific customer, newest first.
     *
     * @param int $customerID The customer whose orders to fetch.
     * @return array List of order summary rows.
     */
    public function getOrdersByCustomerId($customerID) {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT OrderID, OrderDate, TotalAmount, Status
                 FROM Orders
                 WHERE CustomerID = :customerID
                 ORDER BY OrderDate DESC'
            );
            $stmt->execute([':customerID' => $customerID]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Failed to fetch orders for customer $customerID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single order with its line items.
     *
     * @param int $orderID The order to look up.
     * @return array|false Order data with an 'items' key, or false if not found.
     */
    public function getOrderById($orderID) {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.Status,
                        c.FirstName, c.LastName
                 FROM Orders o
                 JOIN Customers c ON o.CustomerID = c.CustomerID
                 WHERE o.OrderID = :orderID'
            );
            $stmt->execute([':orderID' => $orderID]);
            $order = $stmt->fetch();

            if (!$order) {
                return false;
            }

            // Attach line items
            $itemStmt = $this->pdo->prepare(
                'SELECT oi.Quantity, oi.PriceAtPurchase, p.Name
                 FROM OrderItems oi
                 JOIN Products p ON oi.ProductID = p.ProductID
                 WHERE oi.OrderID = :orderID'
            );
            $itemStmt->execute([':orderID' => $orderID]);
            $order['items'] = $itemStmt->fetchAll();

            return $order;

        } catch (PDOException $e) {
            error_log("Failed to fetch order #$orderID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns all orders with customer names, newest first.
     * Used by the admin panel.
     *
     * @return array List of order rows.
     */
    public function getAllOrders() {
        try {
            $stmt = $this->pdo->query(
                'SELECT o.OrderID, o.OrderDate, o.Status, o.TotalAmount,
                        c.FirstName, c.LastName
                 FROM Orders o
                 JOIN Customers c ON o.CustomerID = c.CustomerID
                 ORDER BY o.OrderDate DESC'
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Failed to fetch all orders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the status of an order.
     *
     * @param int    $orderID   The order to update.
     * @param string $newStatus One of: Pending, Processing, Shipped, Completed, Cancelled.
     * @return bool True on success.
     */
    public function updateOrderStatus($orderID, $newStatus) {
        $allowed = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
        if (!in_array($newStatus, $allowed, true)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE Orders SET Status = :status WHERE OrderID = :orderId'
            );
            $stmt->execute([':status' => $newStatus, ':orderId' => $orderID]);
            return true;

        } catch (PDOException $e) {
            error_log("Failed to update order #$orderID status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Looks up the current catalogue price of a product.
     *
     * @param int $productID The product to price.
     * @return float|false The price, or false if the product doesn't exist.
     */
    private function getProductPrice($productID) {
        $stmt = $this->pdo->prepare(
            'SELECT Price FROM Products WHERE ProductID = :productID'
        );
        $stmt->execute([':productID' => $productID]);
        return $stmt->fetchColumn();
    }
}
