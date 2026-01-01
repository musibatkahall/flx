<?php
/**
 * External API Integration Functions
 * Fetch data from free external APIs and store in our database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Fetch Daily Horoscope from Aztro API
 * FREE API - No key needed
 */
function fetch_external_horoscope($zodiac_sign, $day = 'today')
{
    $url = "https://aztro.sameerkumar.website/?sign={$zodiac_sign}&day={$day}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return json_decode($response, true);
    }

    return null;
}

/**
 * Import horoscope from external API to database
 */
function import_horoscope_from_api($zodiac_sign, $period = 'daily', $admin_id)
{
    $db = get_db_connection();

    // Map period to API day parameter
    $day_map = [
        'daily' => 'today',
        'weekly' => 'today', // We'll use today's for weekly too
        'monthly' => 'today'
    ];

    $day = $day_map[$period] ?? 'today';

    // Fetch from external API
    $external_data = fetch_external_horoscope($zodiac_sign, $day);

    if (!$external_data) {
        return ['success' => false, 'message' => 'Failed to fetch from external API'];
    }

    // Get today's date
    $target_date = date('Y-m-d');

    // Check if already exists
    $stmt = $db->prepare("
        SELECT id FROM horoscopes 
        WHERE zodiac_sign = :sign AND period = :period AND target_date = :date
    ");
    $stmt->execute([
        'sign' => $zodiac_sign,
        'period' => $period,
        'date' => $target_date
    ]);

    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Horoscope already exists for this date'];
    }

    // Parse external API response
    $content = $external_data['description'] ?? 'No description available';
    $mood = $external_data['mood'] ?? '';
    $lucky_number = $external_data['lucky_number'] ?? '';
    $lucky_time = $external_data['lucky_time'] ?? '';
    $lucky_color = $external_data['color'] ?? '';

    // Calculate scores (external API doesn't provide these, so we'll use compatibility as base)
    $compatibility = $external_data['compatibility'] ?? '';
    $base_score = 75; // Default

    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO horoscopes 
        (zodiac_sign, period, target_date, content, love_score, career_score, 
         health_score, lucky_number, lucky_color, lucky_time, mood, created_by)
        VALUES (:zodiac, :period, :date, :content, :love, :career, :health, 
                :number, :color, :time, :mood, :admin_id)
    ");

    $result = $stmt->execute([
        'zodiac' => $zodiac_sign,
        'period' => $period,
        'date' => $target_date,
        'content' => $content,
        'love' => $base_score,
        'career' => $base_score,
        'health' => $base_score,
        'number' => $lucky_number,
        'color' => $lucky_color,
        'time' => $lucky_time,
        'mood' => $mood,
        'admin_id' => $admin_id
    ]);

    if ($result) {
        return [
            'success' => true,
            'message' => "Imported {$zodiac_sign} horoscope successfully!",
            'id' => $db->lastInsertId()
        ];
    }

    return ['success' => false, 'message' => 'Failed to save to database'];
}

/**
 * Fetch Random Tarot Card from tarotapi.dev
 * FREE API - No key needed
 */
function fetch_external_tarot_card()
{
    $url = "https://tarotapi.dev/api/v1/cards/random?n=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $data = json_decode($response, true);
        return $data['cards'][0] ?? null;
    }

    return null;
}

/**
 * Import tarot card from external API to database
 */
function import_tarot_from_api($admin_id)
{
    $db = get_db_connection();

    // Fetch from external API
    $external_data = fetch_external_tarot_card();

    if (!$external_data) {
        return ['success' => false, 'message' => 'Failed to fetch from external API'];
    }

    // Check if card already exists
    $card_name = $external_data['name'] ?? '';
    $stmt = $db->prepare("SELECT id FROM tarot_cards WHERE name = :name");
    $stmt->execute(['name' => $card_name]);

    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Card already exists in database'];
    }

    // Determine card type and suit
    $card_type = (isset($external_data['arcana']) && strtolower($external_data['arcana']) === 'major arcana')
        ? 'major_arcana' : 'minor_arcana';

    $suit_map = [
        'cups' => 'cups',
        'wands' => 'wands',
        'swords' => 'swords',
        'pentacles' => 'pentacles'
    ];

    $suit_raw = strtolower($external_data['suit'] ?? '');
    $suit = $suit_map[$suit_raw] ?? 'none';

    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO tarot_cards 
        (name, card_type, suit, number, meaning_upright, meaning_reversed, 
         description, keywords)
        VALUES (:name, :type, :suit, :number, :upright, :reversed, :desc, :keywords)
    ");

    $result = $stmt->execute([
        'name' => $card_name,
        'type' => $card_type,
        'suit' => $suit,
        'number' => $external_data['value_int'] ?? 0,
        'upright' => $external_data['meaning_up'] ?? '',
        'reversed' => $external_data['meaning_rev'] ?? '',
        'desc' => $external_data['desc'] ?? '',
        'keywords' => implode(', ', array_slice($external_data['keywords'] ?? [], 0, 5))
    ]);

    if ($result) {
        return [
            'success' => true,
            'message' => "Imported {$card_name} successfully!",
            'id' => $db->lastInsertId()
        ];
    }

    return ['success' => false, 'message' => 'Failed to save to database'];
}

/**
 * Import all 12 zodiac horoscopes at once
 */
function import_all_horoscopes($period, $admin_id)
{
    $zodiac_signs = [
        'aries',
        'taurus',
        'gemini',
        'cancer',
        'leo',
        'virgo',
        'libra',
        'scorpio',
        'sagittarius',
        'capricorn',
        'aquarius',
        'pisces'
    ];

    $results = [];
    $success_count = 0;
    $fail_count = 0;

    foreach ($zodiac_signs as $sign) {
        $result = import_horoscope_from_api($sign, $period, $admin_id);
        $results[] = $result;

        if ($result['success']) {
            $success_count++;
        } else {
            $fail_count++;
        }

        // Sleep to avoid rate limiting
        usleep(500000); // 0.5 second delay
    }

    return [
        'success' => $success_count > 0,
        'message' => "Imported {$success_count} horoscopes, {$fail_count} failed",
        'details' => $results
    ];
}
?>