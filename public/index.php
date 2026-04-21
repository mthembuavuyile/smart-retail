<?php
/**
 * Homepage — shows a hero banner and the full product catalogue.
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

<!-- Hero Section -->
<section class="hero">
    <?php if (isset($_SESSION['CustomerFirstName'])): ?>
        <div class="welcome-badge">Welcome back, <?php echo sanitize_output($_SESSION['CustomerFirstName']); ?></div>
    <?php endif; ?>

    <h1>Quality Products, Fair Prices</h1>
    <p>Browse our curated catalogue of electronics, books, clothing, and more. Fast checkout, secure payments.</p>

    <div>
        <a href="products.php" class="btn btn-accent">Browse Products</a>
        <?php if (!isset($_SESSION['CustomerID'])): ?>
            <a href="register.php" class="btn btn-secondary" style="border-color:#fff;color:#fff;">Create Account</a>
        <?php endif; ?>
    </div>
</section>

<div class="container fade-in">
    <h2 class="section-title">All Products</h2>

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
        <p class="text-muted text-center">No products available at the moment. Please check back later.</p>
    <?php endif; ?>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>
