<?php
/**
 * Customer registration page.
 *
 * Collects first name, last name, email, and password via a POST form.
 * On success, redirects to login.php with a success status.
 */
require_once __DIR__ . '/../src/Core/db_connect.php';
require_once __DIR__ . '/../src/Core/functions.php';
require_once __DIR__ . '/../src/Customer/CustomerManager.php';

start_secure_session();

// Already logged in — go home
if (isset($_SESSION['CustomerID'])) {
    redirect('index.php');
}

$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $firstName = sanitize_input($_POST['firstName'] ?? '');
    $lastName  = sanitize_input($_POST['lastName'] ?? '');
    $password        = $_POST['password'];        // never sanitize before hashing
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $message = ['type' => 'error', 'text' => 'Passwords do not match.'];
    } else {
        $pdo = get_db_connection();
        $customerManager = new CustomerManager($pdo);
        $result = $customerManager->registerCustomer($email, $password, $firstName, $lastName);

        if ($result === true) {
            redirect('login.php?status=reg_success');
        } else {
            $message = ['type' => 'error', 'text' => $result];
        }
    }
}

include_once __DIR__ . '/../templates/header.php';
?>

<div class="container fade-in">
    <div class="form-container">
        <h1>Create an Account</h1>
        <p class="form-subtitle">Join to start shopping with us.</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo sanitize_output($message['type']); ?>">
                <?php echo sanitize_output($message['text']); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" placeholder="e.g. Avuyile" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" placeholder="e.g. Mthembu" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            </div>
            <button type="submit" class="btn">Create Account</button>
        </form>

        <p class="form-footer">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</div>

<?php
include_once __DIR__ . '/../templates/footer.php';
?>