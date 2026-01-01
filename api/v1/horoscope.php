<?php
/**
 * Horoscope API Endpoint
 * Play Console Compliant - Content delivery only, NO user tracking
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../includes/security_functions.php';

// Set CORS headers
set_cors_headers();

// Rate limiting
$ip_hash = get_client_ip(true);
if (is_rate_limited($ip_hash, 'horoscope')) {
    json_response(['error' => 'Rate limit exceeded'], 429);
}
increment_rate_limit($ip_hash, 'horoscope');

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Get parameters
$zodiac_sign = strtolower(sanitize_input($_GET['sign'] ?? ''));
$period = strtolower(sanitize_input($_GET['period'] ?? 'daily'));
$date = sanitize_input($_GET['date'] ?? date('Y-m-d'));

// Validate zodiac sign
$valid_signs = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];
if (!in_array($zodiac_sign, $valid_signs)) {
    json_response(['error' => 'Invalid zodiac sign'], 400);
}

// Validate period
$valid_periods = ['daily', 'weekly', 'monthly'];
if (!in_array($period, $valid_periods)) {
    json_response(['error' => 'Invalid period'], 400);
}

try {
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT zodiac_sign, period, target_date as date, content, 
               love_score, career_score, health_score,
               lucky_number, lucky_color, lucky_time, mood
        FROM horoscopes 
        WHERE zodiac_sign = :sign 
          AND period = :period 
          AND target_date = :date
          AND is_active = 1
        LIMIT 1
    ");

    $stmt->execute([
        'sign' => $zodiac_sign,
        'period' => $period,
        'date' => $date
    ]);

    $horoscope = $stmt->fetch();

    if (!$horoscope) {
        json_response(['error' => 'No horoscope found for the specified date'], 404);
    }

    // Return horoscope data
    json_response([
        'success' => true,
        'data' => $horoscope
    ]);

} catch (Exception $e) {
    error_log("Horoscope API error: " . $e->getMessage());
    json_response(['error' => 'Internal server error'], 500);
}
?>