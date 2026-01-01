<?php
/**
 * Admin Settings
 * Play Console Compliant - Admin configuration only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();
require_admin_role('admin'); // Only admins can access settings

$admin = get_current_admin();
$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    verify_csrf();

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        $result = update_admin_password($admin['id'], $current_password, $new_password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get system settings
$db = get_db_connection();
$stmt = $db->query("SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key");
$settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settings_data as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get audit log
$stmt = $db->prepare("
    SELECT l.*, a.username 
    FROM admin_audit_log l 
    LEFT JOIN admin_users a ON l.admin_id = a.id 
    ORDER BY l.created_at DESC 
    LIMIT 20
");
$stmt->execute();
$audit_logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AstroFlux Admin</title>
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>⚙️ Settings</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Change Password -->
        <div class="card">
            <h2>Change Password</h2>
            <form method="POST" style="max-width: 500px;">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="change_password" value="1">

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                    <small>Minimum 12 characters, must include uppercase, lowercase, number, and special
                        character</small>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

        <!-- System Info -->
        <div class="card">
            <h2>System Information</h2>
            <table class="data-table">
                <tr>
                    <th>App Version</th>
                    <td>
                        <?php echo $settings['app_version'] ?? '1.0.0'; ?>
                    </td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td>
                        <?php echo phpversion(); ?>
                    </td>
                </tr>
                <tr>
                    <th>API Version</th>
                    <td>
                        <?php echo API_VERSION; ?>
                    </td>
                </tr>
                <tr>
                    <th>Rate Limit</th>
                    <td>
                        <?php echo $settings['api_rate_limit'] ?? 100; ?> requests/minute
                    </td>
                </tr>
            </table>
        </div>

        <!-- Security Info -->
        <div class="card">
            <h2>🔒 Security & Compliance</h2>
            <div style="line-height: 1.8;">
                <p><strong>Play Console Compliance Status: ✅ COMPLIANT</strong></p>
                <ul style="margin-left: 20px;">
                    <li>✅ No personal user data collection</li>
                    <li>✅ No device tracking or identifiers</li>
                    <li>✅ No analytics or tracking cookies</li>
                    <li>✅ Admin actions only logging</li>
                    <li>✅ IP addresses hashed (privacy)</li>
                    <li>✅ HTTPS encryption ready</li>
                    <li>✅ Rate limiting enabled</li>
                    <li>✅ CSRF protection active</li>
                </ul>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <h2>Recent Admin Activity</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Table</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td>
                                <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($log['action']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($log['table_name'] ?? '-'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>