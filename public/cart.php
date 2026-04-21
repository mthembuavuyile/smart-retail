<?php
/**
 * Shopping cart page.
 *
 * Handles three actions via GET parameters:
 *   ?add=<ProductID>[&quantity=<n>]  — adds an item
 *   ?remove=<ProductID>             — removes an item
 *   (no params)                     — displays the cart
 *
 * Cart contents are stored in $_SESSION['cart'] as [ProductID => quantity].
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Product/ProductManager.php';

start_secure_session();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$pdo = get_db_connection();
$productManager = new ProductManager($pdo);

// --- Add item ---
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $productId = (int)$_GET['add'];
    $quantity  = (isset($_GET['quantity']) && is_numeric($_GET['quantity'])) ? max(1, (int)$_GET['quantity']) : 1;

    $product = $productManager->getProductById($productId);
    if ($product) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
    }
    redirect('cart.php');
}

// --- Remove item ---
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    redirect('cart.php');
}

// --- Display cart ---
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="cart-container">
        <h1>Your Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <p>Your cart is empty.</p>
                <a href="products.php" class="btn">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cartTotal = 0;
                        foreach ($_SESSION['cart'] as $productId => $quantity):
                            $product = $productManager->getProductById($productId);
                            if (!$product) continue;
                            $itemTotal = $product['Price'] * $quantity;
                            $cartTotal += $itemTotal;
                        ?>
                            <tr>
                                <td><?php echo sanitize_output($product['Name']); ?></td>
                                <td><?php echo format_price($product['Price']); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td><?php echo format_price($itemTotal); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $productId; ?>" class="btn-remove">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <span class="cart-total">Total: <?php echo format_price($cartTotal); ?></span>
                <a href="checkout.php" class="btn btn-accent">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$pdo = null;
include_once __DIR__ . '/../templates/footer.php';
?>