<?php
/**
 * Admin dashboard.
 *
 * Shows aggregate statistics (revenue, orders, customers, products),
 * a list of recent orders, and low-stock inventory alerts.
 */
require_once __DIR__ . '/../../src/Core/functions.php';
require_once __DIR__ . '/../../src/Core/db_connect.php';
require_once __DIR__ . '/../../src/Reporting/ReportManager.php';
require_once __DIR__ . '/../../src/Order/OrderManager.php';

start_secure_session();
require_admin();

$pdo = get_db_connection();
$reportManager = new ReportManager($pdo);
$orderManager  = new OrderManager($pdo);

// Aggregate stats for the stat cards
$stats = $reportManager->getDashboardStats();

// 5 most recent orders
$allOrders    = $orderManager->getAllOrders();
$recentOrders = array_slice($allOrders, 0, 5);

// Low-stock alerts
$lowStockItems = $reportManager->getLowStockReport();

require_once __DIR__ . '/../../templates/admin_header.php';
?>

<h1>Dashboard</h1>
<p class="admin-subtitle">Welcome back, <?php echo sanitize_output($_SESSION['AdminUsername']); ?>. Here is your store overview.</p>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo format_price($stats['total_revenue']); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['order_count']; ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['customer_count']; ?></div>
        <div class="stat-label">Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['product_count']; ?></div>
        <div class="stat-label">Products</div>
    </div>
</div>

<!-- Widgets -->
<div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="widget">
        <h2>Recent Orders</h2>
        <?php if (empty($recentOrders)): ?>
            <p class="text-muted">No orders yet.</p>
        <?php else: ?>
            <ul class="dashboard-list">
                <?php foreach ($recentOrders as $order): ?>
                    <li>
                        <span><strong>#<?php echo $order['OrderID']; ?></strong> &mdash; <?php echo sanitize_output($order['FirstName']); ?></span>
                        <span class="badge badge-<?php echo strtolower($order['Status']); ?>"><?php echo sanitize_output($order['Status']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="orders.php" class="view-all-link">View all orders &rarr;</a>
        <?php endif; ?>
    </div>

    <!-- Inventory Alerts -->
    <div class="widget">
        <h2>Inventory Alerts</h2>
        <?php if (empty($lowStockItems)): ?>
            <p class="text-muted">All stock levels are healthy.</p>
        <?php else: ?>
            <ul class="dashboard-list">
                <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                    <li>
                        <span><?php echo sanitize_output($item['Name']); ?></span>
                        <span class="stock-alert"><?php echo $item['StockLevel']; ?> left</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="inventory.php" class="view-all-link">Manage inventory &rarr;</a>
        <?php endif; ?>
    </div>
</div>

<?php
$pdo = null;
require_once __DIR__ . '/../../templates/admin_footer.php';
?>