<?php
/**
 * Insights API Endpoint
 * Play Console Compliant - Content delivery only, NO user tracking
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../includes/security_functions.php';

// Set CORS headers
set_cors_headers();

// Rate limiting
$ip_hash = get_client_ip(true);
if (is_rate_limited($ip_hash, 'insights')) {
    json_response(['error' => 'Rate limit exceeded'], 429);
}
increment_rate_limit($ip_hash, 'insights');

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Get parameters
$period = strtolower(sanitize_input($_GET['period'] ?? 'daily'));
$date = sanitize_input($_GET['date'] ?? date('Y-m-d'));
$category = sanitize_input($_GET['category'] ?? '');

// Validate period
$valid_periods = ['daily', 'weekly', 'monthly'];
if (!in_array($period, $valid_periods)) {
    json_response(['error' => 'Invalid period'], 400);
}

try {
    $db = get_db_connection();

    $sql = "
        SELECT period, target_date as date, category, title, content, icon, color_code
        FROM insights 
        WHERE period = :period 
          AND target_date = :date
          AND is_active = 1
    ";

    $params = [
        'period' => $period,
        'date' => $date
    ];

    // Filter by category if provided
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }

    $sql .= " ORDER BY category";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $insights = $stmt->fetchAll();

    if (empty($insights)) {
        json_response(['error' => 'No insights found for the specified date'], 404);
    }

    // Group by category
    $grouped = [];
    foreach ($insights as $insight) {
        $grouped[$insight['category']][] = $insight;
    }

    json_response([
        'success' => true,
        'period' => $period,
        'date' => $date,
        'data' => $grouped,
        'count' => count($insights)
    ]);

} catch (Exception $e) {
    error_log("Insights API error: " . $e->getMessage());
    json_response(['error' => 'Internal server error'], 500);
}
?>