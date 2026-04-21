<?php
/**
 * Admin inventory management page.
 * Shows all product stock levels and allows direct stock updates.
 */
require_once __DIR__ . '/../../src/Core/functions.php';
require_once __DIR__ . '/../../src/Core/db_connect.php';
require_once __DIR__ . '/../../src/Inventory/InventoryManager.php';

start_secure_session();
require_admin();

$pdo = get_db_connection();
$inventoryManager = new InventoryManager($pdo);
$message = [];

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $newStock  = filter_input(INPUT_POST, 'stock_level', FILTER_VALIDATE_INT);

    if ($productId && $newStock !== false && $newStock >= 0) {
        if ($inventoryManager->updateStock($productId, $newStock)) {
            $message = ['type' => 'success', 'text' => "Stock updated for Product #{$productId}."];
        } else {
            $message = ['type' => 'error', 'text' => 'Failed to update stock.'];
        }
    } else {
        $message = ['type' => 'error', 'text' => 'Invalid input for stock update.'];
    }
}

$inventory = $inventoryManager->getAllInventory();

require_once __DIR__ . '/../../templates/admin_header.php';
?>

<h1>Manage Inventory</h1>
<p class="admin-subtitle">Monitor stock levels and restock products as needed.</p>

<?php if (!empty($message)): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo sanitize_output($message['text']); ?>
    </div>
<?php endif; ?>

<?php if (empty($inventory)): ?>
    <p class="text-muted">No inventory records found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Stock</th>
                    <th>Threshold</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item):
                    $isLow = $item['StockLevel'] <= $item['LowStockThreshold'];
                ?>
                    <tr class="<?php echo $isLow ? 'row-low-stock' : ''; ?>">
                        <td><?php echo sanitize_output($item['Name']); ?></td>
                        <td><?php echo sanitize_output($item['SKU']); ?></td>
                        <td>
                            <strong><?php echo $item['StockLevel']; ?></strong>
                            <?php if ($isLow): ?>
                                <span class="stock-alert">(Low)</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['LowStockThreshold']; ?></td>
                        <td>
                            <form action="inventory.php" method="POST" class="inline-form">
                                <input type="hidden" name="product_id" value="<?php echo $item['ProductID']; ?>">
                                <input type="number" name="stock_level" value="<?php echo $item['StockLevel']; ?>" min="0" required>
                                <button type="submit" name="update_stock" class="btn btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
$pdo = null;
require_once __DIR__ . '/../../templates/admin_footer.php';
?>