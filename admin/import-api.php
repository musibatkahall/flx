<?php
/**
 * Import from External APIs
 * Fetch data from free APIs and store in database
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/external_api.php';

require_admin_login();
$admin = get_current_admin();

$success = '';
$error = '';
$import_results = [];

// Handle import actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'import_single_horoscope') {
        $zodiac = $_POST['zodiac_sign'] ?? '';
        $period = $_POST['period'] ?? 'daily';
        
        $result = import_horoscope_from_api($zodiac, $period, $admin['id']);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    if ($action === 'import_all_horoscopes') {
        $period = $_POST['period'] ?? 'daily';
        
        $result = import_all_horoscopes($period, $admin['id']);
        $import_results = $result['details'] ?? [];
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    if ($action === 'import_tarot') {
        $count = min((int)($_POST['count'] ?? 1), 10);
        $success_count = 0;
        $fail_count = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $result = import_tarot_from_api($admin['id']);
            if ($result['success']) {
                $success_count++;
            } else {
                $fail_count++;
            }
            usleep(500000); // 0.5s delay
        }
        
        $success = "Imported {$success_count} cards, {$fail_count} failed/duplicates";
    }
}

$zodiac_signs = ['aries','taurus','gemini','cancer','leo','virgo','libra','scorpio','sagittarius','capricorn','aquarius','pisces'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import from External APIs - AstroFlux Admin</title>
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
        .import-card {
            background: linear-gradient(135deg, #1a2a5e 0%, #2d1b69 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .import-card h3 {
            margin-bottom: 15px;
            color: #DAA520;
        }
        .import-card p {
            margin-bottom: 20px;
            opacity: 0.9;
            line-height: 1.6;
        }
        .import-form {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 8px;
        }
        .import-form label {
            color: white !important;
        }
        .import-form select,
        .import-form input {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .import-form select option {
            background: #1a2a5e;
            color: white;
        }
        .api-info {
            background: rgba(218,165,32,0.1);
            border: 1px solid rgba(218,165,32,0.3);
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .results-table {
            margin-top: 20px;
        }
        .results-table td {
            padding: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>📡 Import from External APIs</h1>
        <p>Fetch fresh data from free external APIs and save to your database</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Import Horoscopes -->
        <div class="import-card">
            <h3>🌟 Import Horoscopes from Aztro API</h3>
            <p>Free API providing daily horoscopes for all zodiac signs. No API key required!</p>
            
            <div class="import-form">
                <h4 style="color: white; margin-bottom: 15px;">Import Single Horoscope</h4>
                <form method="POST" style="display: grid; gap: 15px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="import_single_horoscope">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Zodiac Sign</label>
                            <select name="zodiac_sign" required>
                                <?php foreach ($zodiac_signs as $sign): ?>
                                    <option value="<?php echo $sign; ?>"><?php echo ucfirst($sign); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Period</label>
                            <select name="period">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Import Single</button>
                </form>
                
                <hr style="margin: 30px 0; border-color: rgba(255,255,255,0.2);">
                
                <h4 style="color: white; margin-bottom: 15px;">Import ALL 12 Zodiac Signs at Once</h4>
                <form method="POST" style="display: grid; gap: 15px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="import_all_horoscopes">
                    
                    <div class="form-group">
                        <label>Period</label>
                        <select name="period">
                            <option value="daily">Daily (All 12 Signs)</option>
                            <option value="weekly">Weekly (All 12 Signs)</option>
                            <option value="monthly">Monthly (All 12 Signs)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Import All 12 Horoscopes</button>
                </form>
            </div>
            
            <div class="api-info">
                <strong>📖 API Source:</strong> https://aztro.sameerkumar.website/<br>
                <strong>✅ Status:</strong> Free, No Key Required<br>
                <strong>⚡ Rate Limit:</strong> 500 requests/day
            </div>
        </div>
        
        <!-- Import Tarot -->
        <div class="import-card">
            <h3>🔮 Import Tarot Cards from TarotAPI</h3>
            <p>Free Tarot card database with all 78 cards. No API key required!</p>
            
            <div class="import-form">
                <form method="POST" style="display: grid; gap: 15px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="import_tarot">
                    
                    <div class="form-group">
                        <label>Number of Cards to Import</label>
                        <input type="number" name="count" min="1" max="10" value="5">
                        <small style="color: rgba(255,255,255,0.7);">Max 10 at once to avoid duplicates</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Import Random Tarot Cards</button>
                </form>
            </div>
            
            <div class="api-info">
                <strong>📖 API Source:</strong> https://tarotapi.dev/<br>
                <strong>✅ Status:</strong> Free, No Key Required<br>
                <strong>⚡ Rate Limit:</strong> Unlimited
            </div>
        </div>
        
        <!-- Results Table -->
        <?php if (!empty($import_results)): ?>
        <div class="card">
            <h2>Import Results</h2>
            <table class="data-table results-table">
                <thead>
                    <tr>
                        <th>Zodiac Sign</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($import_results as $idx => $result): ?>
                    <tr>
                        <td><?php echo ucfirst($zodiac_signs[$idx] ?? ''); ?></td>
                        <td><?php echo $result['success'] ? '✅ Success' : '❌ Failed'; ?></td>
                        <td><?php echo htmlspecialchars($result['message'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Info -->
        <div class="card">
            <h2>ℹ️ How It Works</h2>
            <ol style="line-height: 2;">
                <li><strong>Import:</strong> Click buttons above to fetch data from external free APIs</li>
                <li><strong>Store:</strong> Data is saved to YOUR database</li>
                <li><strong>Edit:</strong> Go to Horoscopes/Tarot pages to customize imported data</li>
                <li><strong>Serve:</strong> Your app fetches from YOUR APIs (not external)</li>
                <li><strong>Control:</strong> You own the data and can edit anytime!</li>
            </ol>
            
            <p style="margin-top: 20px; padding: 15px; background: #d1fae5; border-radius: 5px;">
                <strong>💡 Pro Tip:</strong> Import daily horoscopes every morning using CRON job!<br>
                <code style="background: white; padding: 5px; border-radius: 3px;">0 6 * * * curl -X POST https://astro.musibatkahall.site/admin/import-api.php</code>
            </p>
        </div>
    </div>
</body>
</html>
