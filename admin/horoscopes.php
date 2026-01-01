<?php
/**
 * Horoscope Management
 * Play Console Compliant - Content management only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();
$admin = get_current_admin();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $db = get_db_connection();

    if ($action === 'create') {
        $zodiac = sanitize_input($_POST['zodiac_sign'] ?? '');
        $period = sanitize_input($_POST['period'] ?? '');
        $date = sanitize_input($_POST['target_date'] ?? '');
        $content = sanitize_input($_POST['content'] ?? '');
        $love_score = (int) ($_POST['love_score'] ?? 0);
        $career_score = (int) ($_POST['career_score'] ?? 0);
        $health_score = (int) ($_POST['health_score'] ?? 0);
        $lucky_number = sanitize_input($_POST['lucky_number'] ?? '');
        $lucky_color = sanitize_input($_POST['lucky_color'] ?? '');
        $lucky_time = sanitize_input($_POST['lucky_time'] ?? '');
        $mood = sanitize_input($_POST['mood'] ?? '');

        try {
            $stmt = $db->prepare("
                INSERT INTO horoscopes 
                (zodiac_sign, period, target_date, content, love_score, career_score, 
                 health_score, lucky_number, lucky_color, lucky_time, mood, created_by)
                VALUES (:zodiac, :period, :date, :content, :love, :career, :health, 
                        :number, :color, :time, :mood, :admin_id)
            ");

            $stmt->execute([
                'zodiac' => $zodiac,
                'period' => $period,
                'date' => $date,
                'content' => $content,
                'love' => $love_score,
                'career' => $career_score,
                'health' => $health_score,
                'number' => $lucky_number,
                'color' => $lucky_color,
                'time' => $lucky_time,
                'mood' => $mood,
                'admin_id' => $admin['id']
            ]);

            $horoscope_id = $db->lastInsertId();
            log_admin_action($admin['id'], 'create_horoscope', 'horoscopes', $horoscope_id);
            $success = 'Horoscope created successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to create horoscope: ' . $e->getMessage();
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $stmt = $db->prepare("DELETE FROM horoscopes WHERE id = :id");
            $stmt->execute(['id' => $id]);

            log_admin_action($admin['id'], 'delete_horoscope', 'horoscopes', $id);
            $success = 'Horoscope deleted successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to delete horoscope.';
        }
    }
}

// Fetch horoscopes
$db = get_db_connection();
$stmt = $db->query("
    SELECT h.*, a.username as created_by_name 
    FROM horoscopes h 
    LEFT JOIN admin_users a ON h.created_by = a.id 
    ORDER BY h.target_date DESC, h.zodiac_sign ASC 
    LIMIT 50
");
$horoscopes = $stmt->fetchAll();

$zodiac_signs = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];
$periods = ['daily', 'weekly', 'monthly'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Horoscopes - AstroFlux Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>📊 Manage Horoscopes</h1>
            <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='block'">
                + New Horoscope
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zodiac</th>
                        <th>Period</th>
                        <th>Date</th>
                        <th>Content Preview</th>
                        <th>Scores</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horoscopes as $h): ?>
                        <tr>
                            <td>
                                <?php echo $h['id']; ?>
                            </td>
                            <td>
                                <?php echo ucfirst($h['zodiac_sign']); ?>
                            </td>
                            <td>
                                <?php echo ucfirst($h['period']); ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($h['target_date'])); ?>
                            </td>
                            <td>
                                <?php echo substr($h['content'], 0, 50) . '...'; ?>
                            </td>
                            <td>
                                ❤️
                                <?php echo $h['love_score']; ?>%
                                💼
                                <?php echo $h['career_score']; ?>%
                                🏥
                                <?php echo $h['health_score']; ?>%
                            </td>
                            <td>
                                <?php echo $h['created_by_name'] ?? 'N/A'; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete this horoscope?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('createModal').style.display='none'">&times;</span>
            <h2>Create New Horoscope</h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Zodiac Sign *</label>
                    <select name="zodiac_sign" required>
                        <?php foreach ($zodiac_signs as $sign): ?>
                            <option value="<?php echo $sign; ?>">
                                <?php echo ucfirst($sign); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Period *</label>
                    <select name="period" required>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?php echo $period; ?>">
                                <?php echo ucfirst($period); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Target Date *</label>
                    <input type="date" name="target_date" required>
                </div>

                <div class="form-group">
                    <label>Content *</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Love Score (0-100)</label>
                        <input type="number" name="love_score" min="0" max="100" value="75">
                    </div>

                    <div class="form-group">
                        <label>Career Score (0-100)</label>
                        <input type="number" name="career_score" min="0" max="100" value="75">
                    </div>

                    <div class="form-group">
                        <label>Health Score (0-100)</label>
                        <input type="number" name="health_score" min="0" max="100" value="75">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Lucky Number</label>
                        <input type="text" name="lucky_number" placeholder="7, 14, 23">
                    </div>

                    <div class="form-group">
                        <label>Lucky Color</label>
                        <input type="text" name="lucky_color" placeholder="Gold">
                    </div>

                    <div class="form-group">
                        <label>Lucky Time</label>
                        <input type="text" name="lucky_time" placeholder="3-5 PM">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mood</label>
                    <input type="text" name="mood" placeholder="Energetic">
                </div>

                <button type="submit" class="btn btn-primary">Create Horoscope</button>
            </form>
        </div>
    </div>
</body>

</html>