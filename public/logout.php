<?php
/**
 * Logout handler.
 * Destroys the session and redirects to the homepage.
 */
require_once __DIR__ . '/../src/Core/functions.php';

start_secure_session();

// Clear all session data
$_SESSION = [];

// Expire the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

redirect('index.php');