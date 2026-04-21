<?php
/**
 * Shared header template for admin panel pages.
 *
 * Outputs the full <head> and admin navigation bar.
 * Each admin page should include this after its PHP logic block.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel &mdash; Smart Retail System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<header>
    <nav class="admin-nav">
        <a href="dashboard.php" class="logo">SRS Admin</a>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="index.php">Logout</a></li>
        </ul>
    </nav>
</header>
<main class="admin-main">
