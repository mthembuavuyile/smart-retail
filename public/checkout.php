<?php
/**
 * Checkout page.
 *
 * Shows an order summary of the current cart contents and a confirmation form.
 * On POST, creates the order via OrderManager and processes a simulated payment
 * via PaymentManager. On success, clears the cart and redirects to the
 * order confirmation page.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Product/ProductManager.php';
require_once __DIR__ . '/../src/Order/OrderManager.php';
require_once __DIR__ . '/../src/Payment/PaymentManager.php';

start_secure_session();

// Must be logged in
require_login();

// Cart must not be empty
if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$pdo = get_db_connection();
$productManager = new ProductManager($pdo);
$orderManager   = new OrderManager($pdo);
$paymentManager = new PaymentManager($pdo);

$message = [];

// Build cart details for display
$cartItemsDetails = [];
$cartTotal = 0;

foreach ($_SESSION['cart'] as $productId => $quantity) {
    $product = $productManager->getProductById($productId);
    if ($product) {
        $cartItemsDetails[] = ['product' => $product, 'quantity' => $quantity];
        $cartTotal += $product['Price'] * $quantity;
    }
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID        = $_SESSION['CustomerID'];
    $shippingAddressID = 1; // uses the customer's primary address

    $newOrderId = $orderManager->createOrder($customerID, $_SESSION['cart'], $shippingAddressID);

    if ($newOrderId) {
        // Process payment (simulated)
        $paymentManager->processPayment($newOrderId, $cartTotal);

        // Clear the cart and send to confirmation
        unset($_SESSION['cart']);
        redirect('order_confirmation.php?order_id=' . $newOrderId);
    } else {
        $message = ['type' => 'error', 'text' => 'There was a problem placing your order. Please check stock levels or try again.'];
    }
}

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="checkout-container">
        <h1>Checkout</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo sanitize_output($message['type']); ?>">
                <?php echo sanitize_output($message['text']); ?>
            </div>
        <?php endif; ?>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItemsDetails as $item): ?>
                            <tr>
                                <td><?php echo sanitize_output($item['product']['Name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo format_price($item['product']['Price']); ?></td>
                                <td><?php echo format_price($item['product']['Price'] * $item['quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="order-total">Grand Total: <?php echo format_price($cartTotal); ?></div>
        </div>

        <div class="checkout-form">
            <h2>Shipping &amp; Payment</h2>
            <p class="checkout-note">Confirm your details below to place the order.</p>

            <form action="checkout.php" method="POST">
                <div class="checkout-field">
                    <div class="field-label">Shipping Address</div>
                    <div class="field-value">Your primary address on file will be used.</div>
                </div>

                <div class="checkout-field">
                    <div class="field-label">Payment Method</div>
                    <div class="field-value">Payment is processed via our secure gateway (simulated for demo).</div>
                </div>

                <button type="submit" class="btn btn-accent" style="width:100%;">Place Order</button>
            </form>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../templates/footer.php';
?>