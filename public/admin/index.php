<?php
/**
 * Admin login page.
 * Authenticates staff users against the Admins table.
 */
require_once __DIR__ . '/../../src/Core/functions.php';
require_once __DIR__ . '/../../src/Core/db_connect.php';

start_secure_session();

// Already logged in — go to dashboard
if (isset($_SESSION['AdminID'])) {
    redirect('dashboard.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo  = get_db_connection();
    $stmt = $pdo->prepare('SELECT AdminID, Username, PasswordHash FROM Admins WHERE Username = :username');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['PasswordHash'])) {
        session_regenerate_id(true);
        $_SESSION['AdminID']       = $admin['AdminID'];
        $_SESSION['AdminUsername']  = $admin['Username'];
        redirect('dashboard.php');
    } else {
        $message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login &mdash; Smart Retail System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container fade-in">
        <div class="form-container">
            <h1>Admin Login</h1>
            <p class="form-subtitle">Staff access only.</p>

            <?php if ($message): ?>
                <div class="message error"><?php echo sanitize_output($message); ?></div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Log In</button>
            </form>

            <p class="form-footer">
                <a href="../index.php">&larr; Back to store</a>
            </p>
        </div>
    </div>
</body>
</html>