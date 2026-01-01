<?php
/**
 * Security Functions
 * Play Console Compliant - No user tracking, only security measures
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

/**
 * Get client IP address (hashed for privacy compliance)
 * COMPLIANCE: IP is hashed, not stored in plain text
 */
function get_client_ip($hash = true)
{
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // Hash IP for privacy compliance
    return $hash ? hash('sha256', $ip . 'salt_here') : $ip;
}

/**
 * Sanitize input to prevent XSS
 */
function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

/**
 * Validate email
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function is_strong_password($password)
{
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }

    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        return false;
    }

    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        return false;
    }

    if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
        return false;
    }

    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }

    return true;
}

/**
 * Hash password securely
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Verify password
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check rate limiting
 * COMPLIANCE: For security only, not user tracking
 */
function is_rate_limited($ip_hash, $endpoint)
{
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT request_count, window_start 
        FROM api_rate_limits 
        WHERE ip_address = :ip AND endpoint = :endpoint
    ");
    $stmt->execute(['ip' => $ip_hash, 'endpoint' => $endpoint]);
    $record = $stmt->fetch();

    if (!$record) {
        return false;
    }

    $window_elapsed = time() - strtotime($record['window_start']);

    if ($window_elapsed > RATE_LIMIT_WINDOW) {
        // Reset window
        $stmt = $db->prepare("
            UPDATE api_rate_limits 
            SET request_count = 1, window_start = NOW() 
            WHERE ip_address = :ip AND endpoint = :endpoint
        ");
        $stmt->execute(['ip' => $ip_hash, 'endpoint' => $endpoint]);
        return false;
    }

    $limit = ($endpoint === 'login') ? RATE_LIMIT_LOGIN_ATTEMPTS : RATE_LIMIT_API_REQUESTS;
    return $record['request_count'] >= $limit;
}

/**
 * Increment rate limit counter
 */
function increment_rate_limit($ip_hash, $endpoint)
{
    $db = get_db_connection();

    $stmt = $db->prepare("
        INSERT INTO api_rate_limits (ip_address, endpoint, request_count, window_start) 
        VALUES (:ip, :endpoint, 1, NOW())
        ON DUPLICATE KEY UPDATE request_count = request_count + 1
    ");
    $stmt->execute(['ip' => $ip_hash, 'endpoint' => $endpoint]);
}

/**
 * Log admin action
 * COMPLIANCE: Only logs admin actions, NOT user actions
 */
function log_admin_action($admin_id, $action, $table_name = null, $record_id = null, $changes = null)
{
    try {
        $db = get_db_connection();

        $stmt = $db->prepare("
            INSERT INTO admin_audit_log 
            (admin_id, action, table_name, record_id, changes_json, ip_address, user_agent) 
            VALUES (:admin_id, :action, :table_name, :record_id, :changes, :ip, :user_agent)
        ");

        $stmt->execute([
            'admin_id' => $admin_id,
            'action' => $action,
            'table_name' => $table_name,
            'record_id' => $record_id,
            'changes' => $changes ? json_encode($changes) : null,
            'ip' => get_client_ip(true),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
    } catch (Exception $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}

/**
 * Log security event
 * COMPLIANCE: Security logging only
 */
function log_security_event($event_type, $details, $ip_hash = null)
{
    $log_file = __DIR__ . '/../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $ip_hash ?? get_client_ip(true);

    $log_message = "[$timestamp] $event_type | IP: $ip | Details: $details\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Generate secure random string
 */
function generate_random_string($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Encrypt data
 */
function encrypt_data($data)
{
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

/**
 * Decrypt data
 */
function decrypt_data($data)
{
    list($encrypted, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

/**
 * Validate JSON input
 */
function get_json_input()
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid JSON input']));
    }

    return sanitize_input($data);
}

/**
 * Send JSON response
 */
function json_response($data, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Validate required fields
 */
function validate_required_fields($data, $required_fields)
{
    $missing = [];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        json_response([
            'error' => 'Missing required fields: ' . implode(', ', $missing)
        ], 400);
    }
}

/**
 * Clean old rate limit records (auto cleanup)
 */
function clean_old_rate_limits()
{
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("DELETE FROM api_rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to clean rate limits: " . $e->getMessage());
    }
}
?>