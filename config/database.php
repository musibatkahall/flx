<?php
/**
 * Database Configuration
 * Play Console Compliant - Only stores content, NOT user personal data
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'astroflux_admin');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('DB_CHARSET', 'utf8mb4');

/**
 * Get secure database connection
 * @return PDO Database connection
 */
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Security: no persistent connections
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set timezone
            $pdo->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed. Please contact administrator.']));
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * @return bool Connection status
 */
function test_db_connection() {
    try {
        $pdo = get_db_connection();
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        error_log("Database test failed: " . $e->getMessage());
        return false;
    }
}
?>
