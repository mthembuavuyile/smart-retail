<?php
/**
 * Single product detail page.
 * Shows full product info with an add-to-cart form.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Product/ProductManager.php';

start_secure_session();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$productId = (int)$_GET['id'];

$pdo = get_db_connection();
$productManager = new ProductManager($pdo);
$product = $productManager->getProductById($productId);

if (!$product) {
    redirect('index.php');
}

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="product-detail">
        <div class="detail-image">
            <img src="<?php echo !empty($product['ImageURL']) ? sanitize_output($product['ImageURL']) : 'images/placeholder.png'; ?>"
                 alt="<?php echo sanitize_output($product['Name']); ?>">
        </div>
        <div class="detail-info">
            <div class="detail-category"><?php echo sanitize_output($product['CategoryName'] ?? 'Uncategorized'); ?></div>
            <h1><?php echo sanitize_output($product['Name']); ?></h1>
            <p class="detail-sku">SKU: <?php echo sanitize_output($product['SKU']); ?></p>

            <p class="detail-description">
                <?php echo nl2br(sanitize_output($product['Description'])); ?>
            </p>

            <div class="detail-price"><?php echo format_price($product['Price']); ?></div>

            <form action="cart.php" method="GET">
                <input type="hidden" name="add" value="<?php echo $product['ProductID']; ?>">
                <div class="quantity-row">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1">
                </div>
                <button type="submit" class="btn btn-accent">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>