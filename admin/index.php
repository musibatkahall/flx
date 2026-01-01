<?php
/**
 * Admin Dashboard
 * Play Console Compliant - Content management only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require admin login
require_admin_login();

$admin = get_current_admin();

// Fetch stats from database
$db = get_db_connection();

$horoscope_count = $db->query("SELECT COUNT(*) FROM horoscopes")->fetchColumn();
$tarot_count = $db->query("SELECT COUNT(*) FROM tarot_cards")->fetchColumn();
$insights_count = $db->query("SELECT COUNT(*) FROM insights")->fetchColumn();
$admin_count = $db->query("SELECT COUNT(*) FROM admin_users WHERE is_active = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AstroFlux Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #1a2a5e 0%, #2d1b69 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info span {
            font-size: 14px;
            opacity: 0.9;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .welcome h2 {
            color: #1a2a5e;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #666;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #DAA520;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #1a2a5e;
        }

        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .quick-actions h2 {
            color: #1a2a5e;
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            padding: 20px;
            background: linear-gradient(135deg, #1a2a5e 0%, #2d1b69 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            font-weight: 500;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .action-btn .icon {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>✨ AstroFlux Admin Panel</h1>
        <div class="user-info">
            <span>👤
                <?php echo htmlspecialchars($admin['username']); ?> (
                <?php echo htmlspecialchars($admin['role']); ?>)
            </span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Welcome back,
                <?php echo htmlspecialchars($admin['username']); ?>! 👋
            </h2>
            <p>Manage your astrology content from this dashboard.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Horoscopes</h3>
                <div class="number"><?php echo number_format($horoscope_count); ?></div>
            </div>
            <div class="stat-card">
                <h3>Tarot Cards</h3>
                <div class="number"><?php echo number_format($tarot_count); ?></div>
            </div>
            <div class="stat-card">
                <h3>Insights</h3>
                <div class="number"><?php echo number_format($insights_count); ?></div>
            </div>
            <div class="stat-card">
                <h3>Admin Users</h3>
                <div class="number"><?php echo number_format($admin_count); ?></div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="horoscopes.php" class="action-btn">
                    <span class="icon">📊</span>
                    Manage Horoscopes
                </a>
                <a href="tarot.php" class="action-btn">
                    <span class="icon">🔮</span>
                    Manage Tarot
                </a>
                <a href="insights.php" class="action-btn">
                    <span class="icon">✨</span>
                    Manage Insights
                </a>
                <a href="settings.php" class="action-btn">
                    <span class="icon">⚙️</span>
                    Settings
                </a>
            </div>
        </div>
    </div>
</body>

</html>