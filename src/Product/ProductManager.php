<?php
/**
 * Provides read access to the product catalogue.
 *
 * All queries join with the Categories table so callers always
 * receive the human-readable category name alongside product data.
 */
class ProductManager {
    private $pdo;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    /**
     * Fetches a single product by its primary key.
     *
     * @param int $productId The product ID to look up.
     * @return array|false Product row as an associative array, or false if not found.
     */
    public function getProductById($productId) {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.ProductID, p.SKU, p.Name, p.Description, p.Price,
                        p.ImageURL, c.CategoryName
                 FROM Products p
                 LEFT JOIN Categories c ON p.CategoryID = c.CategoryID
                 WHERE p.ProductID = :productId'
            );
            $stmt->execute([':productId' => $productId]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log('Product fetch failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns every product in the catalogue, ordered alphabetically.
     *
     * @return array List of product rows.
     */
    public function getAllProducts() {
        try {
            $stmt = $this->pdo->query(
                'SELECT p.ProductID, p.Name, p.Price, p.ImageURL, c.CategoryName
                 FROM Products p
                 LEFT JOIN Categories c ON p.CategoryID = c.CategoryID
                 ORDER BY p.Name ASC'
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Failed to get all products: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns products belonging to a specific category.
     *
     * @param int $categoryId The category ID to filter by.
     * @return array List of matching product rows.
     */
    public function getProductsByCategory($categoryId) {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.ProductID, p.Name, p.Price, p.ImageURL, c.CategoryName
                 FROM Products p
                 LEFT JOIN Categories c ON p.CategoryID = c.CategoryID
                 WHERE p.CategoryID = :categoryId
                 ORDER BY p.Name ASC'
            );
            $stmt->execute([':categoryId' => $categoryId]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Category product fetch failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns all product categories.
     *
     * @return array List of category rows (CategoryID, CategoryName).
     */
    public function getAllCategories() {
        try {
            $stmt = $this->pdo->query(
                'SELECT CategoryID, CategoryName FROM Categories ORDER BY CategoryName ASC'
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Category fetch failed: ' . $e->getMessage());
            return [];
        }
    }
}