<?php
/**
 * Global helper functions used across the application.
 *
 * Provides input sanitization, session management, authentication guards,
 * and common formatting utilities.
 */

/**
 * Sanitizes a string for safe HTML output.
 * Converts special characters to HTML entities to prevent XSS attacks.
 *
 * @param string|null $data The raw input string.
 * @return string The escaped string, safe for rendering in HTML.
 */
function sanitize_output($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizes raw user input from forms.
 * Trims whitespace and strips any HTML tags. Use this on text inputs
 * (names, addresses, etc.) — never on passwords.
 *
 * @param string|null $data The raw form input.
 * @return string Cleaned plain-text string.
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim((string)$data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Starts a session with hardened cookie parameters.
 * Safe to call multiple times — only starts once per request.
 */
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'] ?? '',
            'secure'   => $isSecure,
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);

        session_start();
    }
}

/**
 * Issues a redirect and terminates the script.
 *
 * @param string $url Relative or absolute URL to redirect to.
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Ensures the current visitor is a logged-in customer.
 * Redirects to the login page if no active customer session exists.
 */
function require_login() {
    if (!isset($_SESSION['CustomerID'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php?status=login_required');
    }
}

/**
 * Ensures the current visitor is a logged-in admin.
 * Redirects to the admin login page if no active admin session exists.
 */
function require_admin() {
    if (!isset($_SESSION['AdminID'])) {
        redirect('index.php');
    }
}

/**
 * Formats a numeric amount as South African Rand.
 *
 * @param float|string $amount The monetary value.
 * @return string Formatted string, e.g. "R 1 500.00".
 */
function format_price($amount) {
    return 'R ' . number_format((float)$amount, 2);
}

/**
 * Returns the number of items currently in the session cart.
 * Used by the header template to show a cart badge.
 *
 * @return int Total quantity of all cart items.
 */
function get_cart_count() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}