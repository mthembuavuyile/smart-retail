<?php
/**
 * Product catalogue listing page.
 * Shows all products in a responsive grid.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Product/ProductManager.php';

start_secure_session();

$pdo = get_db_connection();
$productManager = new ProductManager($pdo);
$products = $productManager->getAllProducts();

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <h1 class="section-title">All Products</h1>

    <?php if ($products): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $product['ProductID']; ?>">
                        <div class="card-image">
                            <img src="<?php echo !empty($product['ImageURL']) ? sanitize_output($product['ImageURL']) : 'images/placeholder.png'; ?>"
                                 alt="<?php echo sanitize_output($product['Name']); ?>">
                        </div>
                    </a>
                    <div class="card-body">
                        <div class="card-category"><?php echo sanitize_output($product['CategoryName'] ?? 'Uncategorized'); ?></div>
                        <h3 class="card-title"><?php echo sanitize_output($product['Name']); ?></h3>
                        <div class="card-price"><?php echo format_price($product['Price']); ?></div>
                        <a href="cart.php?add=<?php echo $product['ProductID']; ?>" class="btn btn-accent">Add to Cart</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted text-center">No products are available at the moment.</p>
    <?php endif; ?>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>