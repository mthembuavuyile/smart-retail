<?php
/**
 * Shared header template for the customer-facing storefront.
 *
 * Outputs the <!DOCTYPE>, <head>, opening <body>, navigation bar, and
 * opens the <main> element. Every public page includes this at the top
 * and closes with footer.php.
 */
require_once __DIR__ . '/../src/Core/functions.php';
start_secure_session();

$scriptPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base = $scriptPath === '' ? '.' : $scriptPath;

$cartCount = get_cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart Retail System — browse products, manage your cart, and place orders online.">
    <title>Smart Retail System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/css/style.css">
</head>
<body>
    <nav class="site-nav">
        <a href="<?php echo $base; ?>/index.php" class="logo">Smart<span>Retail</span></a>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?php echo $base; ?>/products.php">Products</a></li>
            <li>
                <a href="<?php echo $base; ?>/cart.php" class="cart-link">
                    Cart<?php if ($cartCount > 0): ?><span class="cart-badge"><?php echo $cartCount; ?></span><?php endif; ?>
                </a>
            </li>

            <?php if (isset($_SESSION['CustomerID'])): ?>
                <li><a href="<?php echo $base; ?>/order_history.php">My Orders</a></li>
                <li><a href="<?php echo $base; ?>/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base; ?>/login.php">Login</a></li>
                <li><a href="<?php echo $base; ?>/register.php">Register</a></li>
            <?php endif; ?>

            <li><a href="<?php echo $base; ?>/admin/index.php" class="nav-admin">Admin</a></li>
        </ul>
    </nav>

    <main>
