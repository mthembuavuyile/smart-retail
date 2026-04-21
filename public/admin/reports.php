<?php
/**
 * Admin reports page.
 * Displays a low-stock alert table and a 7-day sales summary.
 */
require_once __DIR__ . '/../../src/Core/functions.php';
require_once __DIR__ . '/../../src/Core/db_connect.php';
require_once __DIR__ . '/../../src/Reporting/ReportManager.php';

start_secure_session();
require_admin();

$pdo = get_db_connection();
$reportManager = new ReportManager($pdo);

$lowStockItems = $reportManager->getLowStockReport();

$endDate   = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-6 days'));
$salesData = $reportManager->getSalesReport($startDate, $endDate);

require_once __DIR__ . '/../../templates/admin_header.php';
?>

<h1>Reports</h1>
<p class="admin-subtitle">Key business reports at a glance.</p>

<!-- Low Stock Report -->
<div class="report-section widget">
    <h2>Low Stock Alert</h2>
    <?php if (empty($lowStockItems)): ?>
        <p class="text-muted">All products are sufficiently stocked.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>Threshold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockItems as $item): ?>
                        <tr class="row-low-stock">
                            <td><?php echo sanitize_output($item['Name']); ?></td>
                            <td><?php echo sanitize_output($item['SKU']); ?></td>
                            <td><strong><?php echo $item['StockLevel']; ?></strong></td>
                            <td><?php echo $item['LowStockThreshold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Sales Report -->
<div class="report-section widget">
    <h2>Sales Summary &mdash; Last 7 Days</h2>
    <?php if (empty($salesData)): ?>
        <p class="text-muted">No completed sales recorded in this period.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesData as $day): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($day['Day'])); ?></td>
                            <td><?php echo $day['NumberOfOrders']; ?></td>
                            <td><?php echo format_price($day['DailyRevenue']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$pdo = null;
require_once __DIR__ . '/../../templates/admin_footer.php';
?>