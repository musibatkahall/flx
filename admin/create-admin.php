<?php
/**
 * Create First Admin User
 * One-time setup script
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security_functions.php';

$message = '';
$success = false;

// Check if admin already exists
$db = get_db_connection();
$admin_count = $db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();

if ($admin_count > 0 && !isset($_GET['force'])) {
    $message = "⚠️ Admin users already exist! <a href='debug-login.php'>View existing users</a>";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? 'admin');
    $email = sanitize_input($_POST['email'] ?? 'admin@astroflux.com');
    $password = $_POST['password'] ?? 'Admin@123456';

    try {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        // Insert admin
        $stmt = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, role, is_active) 
            VALUES (:username, :email, :password_hash, 'super_admin', 1)
        ");

        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash
        ]);

        $message = "✅ <strong>SUCCESS!</strong> Admin user created!<br>
                    Username: <strong>$username</strong><br>
                    Password: <strong>$password</strong><br><br>
                    <a href='login.php'>Go to Login Page</a>";
        $success = true;

    } catch (PDOException $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Admin User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1a2a5e;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .warning {
            background: #fef3c7;
            color: #92400e;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background: #1a2a5e;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #2d1b69;
        }

        .info {
            background: #e0f2fe;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔐 Create First Admin User</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : ($admin_count > 0 ? 'warning' : 'error'); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($admin_count == 0 || isset($_GET['force'])): ?>
            <div class="info">
                <strong>Default Credentials:</strong><br>
                Username: admin<br>
                Email: admin@astroflux.com<br>
                Password: Admin@123456<br>
            </div>

            <form method="POST">
                <label>Username:</label>
                <input type="text" name="username" value="admin" required>

                <label>Email:</label>
                <input type="email" name="email" value="admin@astroflux.com" required>

                <label>Password:</label>
                <input type="password" name="password" value="Admin@123456" required>
                <small>Min 12 characters, must include uppercase, lowercase, number, special char</small>

                <br><br>
                <button type="submit">Create Admin User</button>
            </form>
        <?php endif; ?>

        <hr>
        <p>
            <a href="debug-login.php">Debug Login Issues</a> |
            <a href="../test.php">Main Test Page</a> |
            <a href="login.php">Login Page</a>
        </p>
    </div>
</body>

</html>