<?php
/**
 * Database connection factory.
 *
 * Returns a PDO instance configured for MySQL with sensible defaults:
 * exceptions on errors, associative fetch mode, and native prepared statements.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Creates and returns a PDO database connection.
 *
 * @return PDO Active database connection.
 * @throws PDOException if the connection cannot be established (caught and logged).
 */
function get_db_connection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);

    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('A database error occurred. Please try again later.');
    }
}
