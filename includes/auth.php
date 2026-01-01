<?php
/**
 * Authentication System
 * Play Console Compliant - Admin authentication only, NO user tracking
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/security_functions.php';

/**
 * Start secure session
 * COMPLIANCE: HttpOnly, Secure, SameSite cookies
 */
function start_secure_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', SESSION_COOKIE_SECURE ? 1 : 0);
    ini_set('session.cookie_samesite', SESSION_COOKIE_SAMESITE);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

    session_name(SESSION_NAME);
    session_start();

    // Regenerate session ID periodically (security best practice)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Admin login
 * COMPLIANCE: Rate limited, secure, logs admin actions only
 */
function admin_login($username, $password)
{
    $db = get_db_connection();
    $ip_hash = get_client_ip(true);

    // Check rate limiting (prevent brute force)
    if (is_rate_limited($ip_hash, 'login')) {
        log_security_event('login_rate_limited', "Username: $username", $ip_hash);
        return [
            'success' => false,
            'message' => 'Too many login attempts. Please try again in 15 minutes.'
        ];
    }

    // Get admin user
    $stmt = $db->prepare("
        SELECT id, username, email, password_hash, role, is_active, 
               login_attempts, lockout_until
        FROM admin_users 
        WHERE (username = :username OR email = :username) AND is_active = 1
    ");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Check if user exists
    if (!$user) {
        increment_rate_limit($ip_hash, 'login');
        log_security_event('login_failed_user_not_found', "Username: $username", $ip_hash);
        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }

    // Check account lockout
    if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
        $remaining = ceil((strtotime($user['lockout_until']) - time()) / 60);
        log_security_event('login_account_locked', "User ID: {$user['id']}", $ip_hash);
        return [
            'success' => false,
            'message' => "Account is locked. Try again in $remaining minutes."
        ];
    }

    // Check if account is active
    if (!$user['is_active']) {
        log_security_event('login_inactive_account', "User ID: {$user['id']}", $ip_hash);
        return [
            'success' => false,
            'message' => 'Account is inactive. Contact administrator.'
        ];
    }

    // Verify password
    if (!verify_password($password, $user['password_hash'])) {
        // Increment failed login attempts
        $attempts = $user['login_attempts'] + 1;
        $lockout = null;

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockout = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
        }

        $stmt = $db->prepare("
            UPDATE admin_users 
            SET login_attempts = :attempts, lockout_until = :lockout 
            WHERE id = :id
        ");
        $stmt->execute([
            'attempts' => $attempts,
            'lockout' => $lockout,
            'id' => $user['id']
        ]);

        increment_rate_limit($ip_hash, 'login');
        log_security_event('login_wrong_password', "User ID: {$user['id']}, Attempt: $attempts", $ip_hash);

        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }

    // SUCCESS - Password correct
    // Reset login attempts
    $stmt = $db->prepare("
        UPDATE admin_users 
        SET login_attempts = 0, lockout_until = NULL, last_login = NOW() 
        WHERE id = :id
    ");
    $stmt->execute(['id' => $user['id']]);

    // Create session
    start_secure_session();
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['login_time'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Log successful login (admin action logging only)
    log_admin_action($user['id'], 'login', 'admin_users', $user['id']);
    log_security_event('login_success', "User ID: {$user['id']}", $ip_hash);

    return [
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in()
{
    start_secure_session();
    return isset($_SESSION['admin_id']) && isset($_SESSION['login_time']);
}

/**
 * Get current admin user
 */
function get_current_admin()
{
    if (!is_admin_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'role' => $_SESSION['admin_role'] ?? null,
    ];
}

/**
 * Require admin login (redirect if not logged in)
 */
function require_admin_login()
{
    if (!is_admin_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Check if admin has required role
 */
function has_admin_role($required_role)
{
    if (!is_admin_logged_in()) {
        return false;
    }

    $roles = [
        'editor' => 1,
        'admin' => 2,
        'super_admin' => 3
    ];

    $user_role = $_SESSION['admin_role'] ?? 'editor';
    $user_level = $roles[$user_role] ?? 0;
    $required_level = $roles[$required_role] ?? 999;

    return $user_level >= $required_level;
}

/**
 * Require specific admin role
 */
function require_admin_role($required_role)
{
    require_admin_login();

    if (!has_admin_role($required_role)) {
        http_response_code(403);
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Admin logout
 */
function admin_logout()
{
    start_secure_session();

    if (isset($_SESSION['admin_id'])) {
        log_admin_action($_SESSION['admin_id'], 'logout', 'admin_users', $_SESSION['admin_id']);
        log_security_event('logout', "User ID: {$_SESSION['admin_id']}", get_client_ip(true));
    }

    // Destroy session
    $_SESSION = [];

    // Delete session cookie
    if (isset($_COOKIE[SESSION_NAME])) {
        setcookie(SESSION_NAME, '', time() - 3600, '/', '', SESSION_COOKIE_SECURE, true);
    }

    session_destroy();
}

/**
 * Create new admin user
 * COMPLIANCE: Only for admin panel users, NOT app users
 */
function create_admin_user($username, $email, $password, $role = 'editor')
{
    $db = get_db_connection();

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (!is_valid_email($email)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }

    if (!is_strong_password($password)) {
        return [
            'success' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters and include uppercase, lowercase, number, and special character.'
        ];
    }

    // Check if username or email already exists
    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);

    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }

    // Create user
    $password_hash = hash_password($password);

    $stmt = $db->prepare("
        INSERT INTO admin_users (username, email, password_hash, role) 
        VALUES (:username, :email, :password_hash, :role)
    ");

    try {
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'role' => $role
        ]);

        $user_id = $db->lastInsertId();

        // Log admin action
        if (isset($_SESSION['admin_id'])) {
            log_admin_action($_SESSION['admin_id'], 'create_admin_user', 'admin_users', $user_id);
        }

        return [
            'success' => true,
            'message' => 'Admin user created successfully.',
            'user_id' => $user_id
        ];

    } catch (PDOException $e) {
        error_log("Failed to create admin user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create user. Please try again.'];
    }
}

/**
 * Update admin password
 */
function update_admin_password($admin_id, $current_password, $new_password)
{
    $db = get_db_connection();

    // Get current password hash
    $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = :id");
    $stmt->execute(['id' => $admin_id]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }

    // Verify current password
    if (!verify_password($current_password, $user['password_hash'])) {
        log_security_event('password_change_wrong_current', "User ID: $admin_id", get_client_ip(true));
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    // Validate new password
    if (!is_strong_password($new_password)) {
        return [
            'success' => false,
            'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters and include uppercase, lowercase, number, and special character.'
        ];
    }

    // Update password
    $new_hash = hash_password($new_password);

    $stmt = $db->prepare("UPDATE admin_users SET password_hash = :hash WHERE id = :id");
    $stmt->execute(['hash' => $new_hash, 'id' => $admin_id]);

    log_admin_action($admin_id, 'change_password', 'admin_users', $admin_id);
    log_security_event('password_changed', "User ID: $admin_id", get_client_ip(true));

    return ['success' => true, 'message' => 'Password updated successfully.'];
}

/**
 * Verify CSRF token for forms
 */
function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die('CSRF token validation failed.');
    }
}
?>