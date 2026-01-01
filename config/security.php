<?php
/**
 * Security Configuration
 * Play Console Compliant - Maximum security, no user tracking
 */

// JWT Configuration
define('JWT_SECRET', '7f3c8e9a2b4d6f1e5a7c9b3d8f2e6a4c1b7d9f3e5a8c2b6d1f4e7a9c3b8d2f6'); // CHANGE THIS!
define('JWT_ALGORITHM', 'HS256');
define('JWT_ACCESS_EXPIRY', 900); // 15 minutes
define('JWT_REFRESH_EXPIRY', 604800); // 7 days

// Session Configuration
define('SESSION_NAME', 'ASTROFLUX_ADMIN_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_COOKIE_SECURE', true); // HTTPS only
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

// Password Policy
define('PASSWORD_MIN_LENGTH', 12);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', true);

// Rate Limiting
define('RATE_LIMIT_API_REQUESTS', 100); // Per minute
define('RATE_LIMIT_LOGIN_ATTEMPTS', 5); // Per 15 minutes
define('RATE_LIMIT_WINDOW', 60); // 1 minute
define('LOCKOUT_TIME', 900); // 15 minutes

// Encryption
define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('ENCRYPTION_KEY', hash('sha256', 'your-encryption-key-here')); // CHANGE THIS!

// CORS - Only allow your app
define('ALLOWED_ORIGINS', [
    'http://localhost', // Development
    // 'https://yourdomain.com' // Production
]);

// API Version
define('API_VERSION', 'v1');

// File Upload (if needed)
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

/**
 * Initialize security headers
 * COMPLIANCE: No tracking, no analytics
 */
function init_security_headers()
{
    // Prevent clickjacking
    header('X-Frame-Options: DENY');

    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');

    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer Policy - Don't send referrer
    header('Referrer-Policy: no-referrer');

    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';");

    // Permissions Policy - Disable all features that could track users
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');

    // Remove server signature
    header_remove('X-Powered-By');
}

/**
 * Validate origin for CORS
 */
function validate_origin($origin)
{
    return in_array($origin, ALLOWED_ORIGINS);
}

/**
 * Set CORS headers
 */
function set_cors_headers()
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (validate_origin($origin)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            http_response_code(204);
            exit;
        }
    }
}

// Initialize security
init_security_headers();
?>