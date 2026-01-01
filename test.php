<?php
/**
 * Quick Test File - Check if everything works
 */

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>✅ PHP is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
require_once __DIR__ . '/config/database.php';

try {
    $db = get_db_connection();
    echo "<p>✅ Database Connected!</p>";

    // Test queries
    $horoscopes = $db->query("SELECT COUNT(*) FROM horoscopes")->fetchColumn();
    $tarot = $db->query("SELECT COUNT(*) FROM tarot_cards")->fetchColumn();
    $insights = $db->query("SELECT COUNT(*) FROM insights")->fetchColumn();

    echo "<p>📊 Horoscopes: $horoscopes</p>";
    echo "<p>🔮 Tarot Cards: $tarot</p>";
    echo "<p>✨ Insights: $insights</p>";

    echo "<h2>✅ Everything is working perfectly!</h2>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Test security functions
require_once __DIR__ . '/includes/security_functions.php';

echo "<p>✅ Security functions loaded</p>";
echo "<p>IP Hash: " . get_client_ip(true) . "</p>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='admin/login.php'>Go to Admin Login</a></li>";
echo "<li><a href='api-tester.html'>Test APIs</a></li>";
echo "<li><a href='api/v1/horoscope?sign=aries&period=daily'>Test Horoscope API</a></li>";
echo "</ul>";
?>