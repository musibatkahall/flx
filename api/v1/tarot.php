<?php
/**
 * Tarot API Endpoint
 * Play Console Compliant - Content delivery only, NO user tracking
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../includes/security_functions.php';

// Set CORS headers
set_cors_headers();

// Rate limiting
$ip_hash = get_client_ip(true);
if (is_rate_limited($ip_hash, 'tarot')) {
    json_response(['error' => 'Rate limit exceeded'], 429);
}
increment_rate_limit($ip_hash, 'tarot');

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Get parameters
$action = strtolower(sanitize_input($_GET['action'] ?? 'random'));
$count = min((int) ($_GET['count'] ?? 1), 10); // Max 10 cards

try {
    $db = get_db_connection();

    if ($action === 'random') {
        // Get random tarot card(s)
        $stmt = $db->prepare("
            SELECT id, name, card_type, suit, number, emoji,
                   meaning_upright, meaning_reversed, description, keywords
            FROM tarot_cards 
            WHERE is_active = 1
            ORDER BY RAND()
            LIMIT :count
        ");

        $stmt->bindValue(':count', $count, PDO::PARAM_INT);
        $stmt->execute();

        $cards = $stmt->fetchAll();

        if (empty($cards)) {
            json_response(['error' => 'No tarot cards available'], 404);
        }

        json_response([
            'success' => true,
            'data' => $count === 1 ? $cards[0] : $cards
        ]);

    } elseif ($action === 'all') {
        // Get all tarot cards
        $stmt = $db->query("
            SELECT id, name, card_type, suit, number, emoji,
                   meaning_upright, meaning_reversed, description, keywords
            FROM tarot_cards 
            WHERE is_active = 1
            ORDER BY card_type, suit, number
        ");

        $cards = $stmt->fetchAll();

        json_response([
            'success' => true,
            'data' => $cards,
            'count' => count($cards)
        ]);

    } else {
        json_response(['error' => 'Invalid action'], 400);
    }

} catch (Exception $e) {
    error_log("Tarot API error: " . $e->getMessage());
    json_response(['error' => 'Internal server error'], 500);
}
?>