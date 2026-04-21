<?php
/**
 * Order confirmation page.
 *
 * Shown after a successful checkout. Displays the order number and
 * line-item details fetched from the database.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Order/OrderManager.php';

start_secure_session();

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('index.php');
}

$pdo = get_db_connection();
$orderManager = new OrderManager($pdo);
$order = $orderManager->getOrderById($orderId);

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="confirmation">
        <div class="check-icon">&#10003;</div>
        <h1>Thank You For Your Order!</h1>
        <p>Your order has been placed and is being processed.</p>
        <div class="order-number">Order #<?php echo sanitize_output($orderId); ?></div>

        <?php if ($order && !empty($order['items'])): ?>
            <div class="order-details">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo sanitize_output($item['Name']); ?></td>
                                    <td><?php echo $item['Quantity']; ?></td>
                                    <td><?php echo format_price($item['PriceAtPurchase']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="order-total mt-lg">Total: <?php echo format_price($order['TotalAmount']); ?></div>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php" class="btn">Continue Shopping</a>
            <a href="order_history.php" class="btn btn-secondary">View Order History</a>
        </div>
    </div>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>