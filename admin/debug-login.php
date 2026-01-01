<?php
/**
 * Login Debug Page
 * Check what's happening with login
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Login Debug</h1>";

// Test 1: Check includes
echo "<h2>Test 1: Loading Files</h2>";
try {
    require_once __DIR__ . '/../config/database.php';
    echo "✅ Database config loaded<br>";

    require_once __DIR__ . '/../config/security.php';
    echo "✅ Security config loaded<br>";

    require_once __DIR__ . '/../includes/security_functions.php';
    echo "✅ Security functions loaded<br>";

    require_once __DIR__ . '/../includes/auth.php';
    echo "✅ Auth functions loaded<br>";
} catch (Exception $e) {
    echo "❌ Error loading files: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check database
echo "<h2>Test 2: Database Connection</h2>";
try {
    $db = get_db_connection();
    echo "✅ Database connected<br>";

    // Check admin users
    $count = $db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    echo "📊 Admin users in database: <strong>$count</strong><br>";

    if ($count == 0) {
        echo "<p style='color:orange'>⚠️ <strong>NO ADMIN USERS!</strong> Need to import sample data.</p>";
    } else {
        // Show admin users
        $users = $db->query("SELECT id, username, email, role, is_active FROM admin_users")->fetchAll();
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_active'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Test login function
echo "<h2>Test 3: Test Login</h2>";
echo "<p>Try logging in with: <strong>admin / Admin@123456</strong></p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    echo "<p>Attempting login with username: <strong>$username</strong></p>";

    $result = admin_login($username, $password);

    echo "<pre>";
    print_r($result);
    echo "</pre>";

    if ($result['success']) {
        echo "<p style='color:green'>✅ <strong>LOGIN SUCCESSFUL!</strong></p>";
        echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color:red'>❌ Login failed: " . $result['message'] . "</p>";
    }
}
?>

<hr>
<h3>Quick Login Test</h3>
<form method="POST">
    <input type="text" name="username" placeholder="Username" value="admin" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Test Login</button>
</form>

<hr>
<h3>Quick Fixes:</h3>
<ul>
    <li><a href="create-admin.php">Create First Admin User</a></li>
    <li><a href="../test.php">Back to Main Test</a></li>
    <li><a href="login.php">Go to Login Page</a></li>
</ul>