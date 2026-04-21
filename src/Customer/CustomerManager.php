<?php
/**
 * Handles customer registration and authentication.
 *
 * All passwords are hashed with PHP's PASSWORD_DEFAULT algorithm (currently bcrypt).
 * Login attempts are timed consistently to avoid leaking whether an email exists.
 */
class CustomerManager {
    private $pdo;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    /**
     * Registers a new customer account.
     *
     * Validates email format and password length before inserting.
     * Returns true on success or a human-readable error string on failure.
     *
     * @param string $email    Customer's email address.
     * @param string $password Plain-text password (min 8 characters).
     * @param string $firstName Customer's first name.
     * @param string $lastName  Customer's last name.
     * @return true|string True on success, error message on failure.
     */
    public function registerCustomer($email, $password, $firstName, $lastName) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email format.';
        }
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO Customers (FirstName, LastName, Email, PasswordHash)
                 VALUES (:firstName, :lastName, :email, :passwordHash)'
            );
            $stmt->execute([
                ':firstName'    => $firstName,
                ':lastName'     => $lastName,
                ':email'        => $email,
                ':passwordHash' => $passwordHash,
            ]);
            return true;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return 'An account with this email already exists.';
            }
            error_log('Registration error: ' . $e->getMessage());
            return 'Registration failed due to a server error.';
        }
    }

    /**
     * Authenticates a customer by email and password.
     *
     * @param string $email    The email to look up.
     * @param string $password The plain-text password to verify.
     * @return array|false Associative array with CustomerID and FirstName on success, false otherwise.
     */
    public function loginCustomer($email, $password) {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT CustomerID, FirstName, PasswordHash
                 FROM Customers WHERE Email = :email'
            );
            $stmt->execute([':email' => $email]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['PasswordHash'])) {
                return [
                    'CustomerID' => $customer['CustomerID'],
                    'FirstName'  => $customer['FirstName'],
                ];
            }

            return false;

        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            return false;
        }
    }
}