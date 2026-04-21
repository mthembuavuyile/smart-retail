<?php
/**
 * Order history page.
 * Lists all past orders for the logged-in customer.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Order/OrderManager.php';

start_secure_session();
require_login();

$pdo = get_db_connection();
$orderManager = new OrderManager($pdo);
$orders = $orderManager->getOrdersByCustomerId($_SESSION['CustomerID']);

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <h1 class="section-title">My Order History</h1>

    <?php if (empty($orders)): ?>
        <p class="text-muted">You haven&rsquo;t placed any orders yet.</p>
        <a href="products.php" class="btn mt-lg">Browse Products</a>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        $statusClass = 'badge-' . strtolower($order['Status']);
                    ?>
                        <tr>
                            <td>#<?php echo sanitize_output($order['OrderID']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['OrderDate'])); ?></td>
                            <td><?php echo format_price($order['TotalAmount']); ?></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo sanitize_output($order['Status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>