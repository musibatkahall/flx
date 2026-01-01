<?php
/**
 * ADVANCED Import System with Multiple APIs
 * Smart API selection + Bulk delete
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/external_api.php';

require_admin_login();
$admin = get_current_admin();

$success = '';
$error = '';
$import_results = [];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    // DELETE ACTIONS
    if ($action === 'delete_all_horoscopes') {
        $result = delete_all_horoscopes_by_date();
        $success = $result['message'];
    }

    if ($action === 'delete_horoscopes_by_date') {
        $date = $_POST['delete_date'] ?? date('Y-m-d');
        $result = delete_all_horoscopes_by_date($date);
        $success = $result['message'];
    }

    if ($action === 'delete_all_tarot') {
        $result = delete_all_tarot_cards();
        $success = $result['message'];
    }

    if ($action === 'delete_all_insights') {
        $result = delete_all_insights_by_date();
        $success = $result['message'];
    }

    // IMPORT ACTIONS
    if ($action === 'import_single') {
        $zodiac = $_POST['zodiac_sign'] ?? '';
        $period = $_POST['period'] ?? 'daily';

        $result = import_advanced_horoscope($zodiac, $period, $admin['id']);

        if ($result['success']) {
            $success = $result['message'] . " (Source: {$result['source']})";
        } else {
            $error = $result['message'];
        }
    }

    if ($action === 'import_all') {
        $period = $_POST['period'] ?? 'daily';

        $result = import_all_advanced($period, $admin['id']);
        $import_results = $result['details'] ?? [];

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }

    if ($action === 'import_tarot') {
        $count = min((int) ($_POST['count'] ?? 1), 20);
        $success_count = 0;
        $fail_count = 0;

        for ($i = 0; $i < $count; $i++) {
            $result = import_advanced_tarot($admin['id']);
            if ($result['success']) {
                $success_count++;
            } else {
                $fail_count++;
            }
            usleep(300000);
        }

        $success = "Imported {$success_count} cards, {$fail_count} skipped";
    }
}

$zodiac_signs = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced API Import - AstroFlux</title>
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .section h2 {
            color: #1a2a5e;
            margin-bottom: 20px;
            border-bottom: 3px solid #DAA520;
            padding-bottom: 10px;
        }

        .button-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }

        .btn-delete {
            background: #dc2626;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .api-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #DAA520;
            color: white;
            border-radius: 5px;
            font-size: 12px;
            margin: 5px;
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .feature-item {
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid #DAA520;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>🚀 Advanced API Import System</h1>
        <p>Multi-source API fetching with intelligent fallback & bulk management</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- HOROSCOPES SECTION -->
        <div class="section">
            <h2>🌟 Horoscopes - Smart Multi-API Fetching</h2>

            <div class="feature-list">
                <div class="feature-item">
                    <strong>✅ 3 API Sources</strong><br>
                    Tries best API first, falls back automatically
                </div>
                <div class="feature-item">
                    <strong>✅ Source Tracking</strong><br>
                    Know which API provided the data
                </div>
                <div class="feature-item">
                    <strong>✅ Rich Content</strong><br>
                    Mood, color, numbers, times
                </div>
            </div>

            <div class="api-badge">🔥 Horoscope-App API</div>
            <div class="api-badge">⚡ API Ninjas</div>
            <div class="api-badge">🌙 Aztro API</div>

            <h3 style="margin-top: 30px;">Import All 12 Signs</h3>
            <form method="POST" style="max-width: 500px;">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="import_all">

                <div class="form-group">
                    <label>Period</label>
                    <select name="period">
                        <option value="daily">Daily (All 12)</option>
                        <option value="weekly">Weekly (All 12)</option>
                        <option value="monthly">Monthly (All 12)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">🚀 Import All 12 Horoscopes</button>
            </form>

            <h3 style="margin-top: 30px;">⚠️ Bulk Delete</h3>
            <div class="button-row">
                <form method="POST" onsubmit="return confirm('Delete ALL horoscopes?');" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="delete_all_horoscopes">
                    <button type="submit" class="btn btn-sm btn-delete">🗑️ Delete All Horoscopes</button>
                </form>

                <form method="POST" onsubmit="return confirm('Delete horoscopes for this date?');"
                    style="display:inline-flex; gap: 10px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="delete_horoscopes_by_date">
                    <input type="date" name="delete_date" value="<?php echo date('Y-m-d'); ?>" style="padding: 6px;">
                    <button type="submit" class="btn btn-sm btn-delete">Delete by Date</button>
                </form>
            </div>
        </div>

        <!-- TAROT SECTION -->
        <div class="section">
            <h2>🔮 Tarot Cards - Premium API</h2>

            <div class="api-badge">🎴 TarotAPI.dev - All 78 Cards</div>

            <form method="POST" style="max-width: 500px; margin-top: 20px;">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="import_tarot">

                <div class="form-group">
                    <label>Number of Cards</label>
                    <input type="number" name="count" min="1" max="20" value="10">
                    <small>Import 1-20 cards at once (skips duplicates)</small>
                </div>

                <button type="submit" class="btn btn-primary">🔮 Import Random Tarot Cards</button>
            </form>

            <h3 style="margin-top: 30px;">⚠️ Bulk Delete</h3>
            <form method="POST" onsubmit="return confirm('Delete ALL tarot cards?');">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="delete_all_tarot">
                <button type="submit" class="btn btn-sm btn-delete">🗑️ Delete All Tarot Cards</button>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($import_results)): ?>
            <div class="section">
                <h2>📊 Import Results</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sign</th>
                            <th>Status</th>
                            <th>Source API</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($import_results as $idx => $result): ?>
                            <tr>
                                <td><?php echo ucfirst($zodiac_signs[$idx] ?? ''); ?></td>
                                <td><?php echo $result['success'] ? '✅' : '❌'; ?></td>
                                <td><span class="api-badge"><?php echo $result['source'] ?? 'N/A'; ?></span></td>
                                <td><?php echo htmlspecialchars($result['message'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Info -->
        <div class="section">
            <h2>ℹ️ How This Works</h2>
            <div class="feature-list">
                <div class="feature-item">
                    <strong>1️⃣ Smart Fetching</strong><br>
                    System tries multiple APIs until one works
                </div>
                <div class="feature-item">
                    <strong>2️⃣ Your Database</strong><br>
                    All data saved to YOUR database
                </div>
                <div class="feature-item">
                    <strong>3️⃣ Full Control</strong><br>
                    Edit, customize, or delete anytime
                </div>
                <div class="feature-item">
                    <strong>4️⃣ Your APIs</strong><br>
                    App fetches from YOUR endpoints
                </div>
            </div>

            <p style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 5px;">
                <strong>💡 Workflow:</strong><br>
                1. Click "Import All 12 Horoscopes"<br>
                2. System fetches from best available API<br>
                3. Edit content in Horoscopes page if needed<br>
                4. Your Android app calls YOUR API<br>
                5. Tomorrow, delete old & import fresh data!
            </p>
        </div>
    </div>
</body>

</html>