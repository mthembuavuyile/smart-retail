<?php
/**
 * Admin order management page.
 * Lists all orders and allows status updates.
 */
require_once __DIR__ . '/../../src/Core/functions.php';
require_once __DIR__ . '/../../src/Core/db_connect.php';
require_once __DIR__ . '/../../src/Order/OrderManager.php';

start_secure_session();
require_admin();

$pdo = get_db_connection();
$orderManager = new OrderManager($pdo);
$message = [];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId   = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = trim($_POST['status'] ?? '');

    if ($orderId && $orderManager->updateOrderStatus($orderId, $newStatus)) {
        $message = ['type' => 'success', 'text' => "Order #{$orderId} updated to {$newStatus}."];
    } else {
        $message = ['type' => 'error', 'text' => 'Failed to update order status.'];
    }
}

$orders = $orderManager->getAllOrders();

require_once __DIR__ . '/../../templates/admin_header.php';
?>

<h1>Manage Orders</h1>
<p class="admin-subtitle">View and update the status of all customer orders.</p>

<?php if (!empty($message)): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo sanitize_output($message['text']); ?>
    </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <p class="text-muted">No orders found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo sanitize_output($order['OrderID']); ?></td>
                        <td><?php echo sanitize_output($order['FirstName'] . ' ' . $order['LastName']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['OrderDate'])); ?></td>
                        <td><?php echo format_price($order['TotalAmount']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($order['Status']); ?>">
                                <?php echo sanitize_output($order['Status']); ?>
                            </span>
                        </td>
                        <td>
                            <form action="orders.php" method="POST" class="inline-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                <select name="status">
                                    <?php foreach (['Pending','Processing','Shipped','Completed','Cancelled'] as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo ($order['Status'] === $s) ? 'selected' : ''; ?>>
                                            <?php echo $s; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm">Update</button>
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