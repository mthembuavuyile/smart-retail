<?php
/**
 * Customer login page.
 *
 * Accepts email + password via POST form submission.
 * On success, stores customer ID and name in the session and redirects home.
 * Supports a ?status=reg_success query param to show a registration success message.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Customer/CustomerManager.php';
require_once __DIR__ . '/../src/Core/functions.php';

start_secure_session();

// Already logged in — go home
if (isset($_SESSION['CustomerID'])) {
    redirect('index.php');
}

$message = [];

// Show post-registration success message
if (isset($_GET['status']) && $_GET['status'] === 'reg_success') {
    $message = ['type' => 'success', 'text' => 'Registration successful! Please log in.'];
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // passwords are never sanitized before verification

    if (empty($email) || empty($password)) {
        $message = ['type' => 'error', 'text' => 'Email and password are required.'];
    } else {
        $pdo = get_db_connection();
        $customerManager = new CustomerManager($pdo);
        $customer = $customerManager->loginCustomer($email, $password);

        if ($customer) {
            session_regenerate_id(true);
            $_SESSION['CustomerID'] = $customer['CustomerID'];
            $_SESSION['CustomerFirstName'] = $customer['FirstName'];

            // Redirect to intended page or home
            $dest = $_SESSION['redirect_url'] ?? 'index.php';
            unset($_SESSION['redirect_url']);
            redirect($dest);
        } else {
            $message = ['type' => 'error', 'text' => 'Invalid email or password.'];
        }
    }
}

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="form-container">
        <h1>Log In</h1>
        <p class="form-subtitle">Sign in to your account to continue.</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo sanitize_output($message['type']); ?>">
                <?php echo sanitize_output($message['text']); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
            </div>
            <button type="submit" class="btn">Log In</button>
        </form>

        <p class="form-footer">
            Don&rsquo;t have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php
include_once __DIR__ . '/../templates/footer.php';
?>