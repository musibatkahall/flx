<?php
/**
 * Insights Management  
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
        $period = sanitize_input($_POST['period'] ?? '');
        $date = sanitize_input($_POST['target_date'] ?? '');
        $category = sanitize_input($_POST['category'] ?? '');
        $title = sanitize_input($_POST['title'] ?? '');
        $content = sanitize_input($_POST['content'] ?? '');
        $icon = sanitize_input($_POST['icon'] ?? '');
        $color = sanitize_input($_POST['color_code'] ?? '');

        try {
            $stmt = $db->prepare("
                INSERT INTO insights 
                (period, target_date, category, title, content, icon, color_code, created_by)
                VALUES (:period, :date, :category, :title, :content, :icon, :color, :admin_id)
            ");

            $stmt->execute([
                'period' => $period,
                'date' => $date,
                'category' => $category,
                'title' => $title,
                'content' => $content,
                'icon' => $icon,
                'color' => $color,
                'admin_id' => $admin['id']
            ]);

            $insight_id = $db->lastInsertId();
            log_admin_action($admin['id'], 'create_insight', 'insights', $insight_id);
            $success = 'Insight created successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to create insight: ' . $e->getMessage();
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $stmt = $db->prepare("DELETE FROM insights WHERE id = :id");
            $stmt->execute(['id' => $id]);

            log_admin_action($admin['id'], 'delete_insight', 'insights', $id);
            $success = 'Insight deleted successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to delete insight.';
        }
    }
}

// Fetch insights
$db = get_db_connection();
$stmt = $db->query("
    SELECT i.*, a.username as created_by_name 
    FROM insights i 
    LEFT JOIN admin_users a ON i.created_by = a.id 
    ORDER BY i.target_date DESC 
    LIMIT 50
");
$insights = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Insights - AstroFlux Admin</title>
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>✨ Manage Insights</h1>
            <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='block'">
                + New Insight
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
                        <th>Period</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Icon</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insights as $insight): ?>
                        <tr>
                            <td>
                                <?php echo $insight['id']; ?>
                            </td>
                            <td>
                                <?php echo ucfirst($insight['period']); ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($insight['target_date'])); ?>
                            </td>
                            <td>
                                <?php echo ucwords(str_replace('_', ' ', $insight['category'])); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($insight['title']); ?>
                            </td>
                            <td>
                                <?php echo $insight['icon']; ?>
                            </td>
                            <td>
                                <?php echo $insight['created_by_name'] ?? 'N/A'; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete this insight?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $insight['id']; ?>">
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
            <h2>Create New Insight</h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label>Period *</label>
                        <select name="period" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Target Date *</label>
                        <input type="date" name="target_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="cosmic_energy">Cosmic Energy</option>
                        <option value="love">Love & Relationships</option>
                        <option value="career">Career & Finance</option>
                        <option value="health">Health & Wellness</option>
                        <option value="personal_growth">Personal Growth</option>
                        <option value="key_dates">Key Dates</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Today's Cosmic Energy">
                </div>

                <div class="form-group">
                    <label>Content *</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text" name="icon" placeholder="✨">
                    </div>

                    <div class="form-group">
                        <label>Color Code</label>
                        <input type="color" name="color_code" value="#DAA520">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Insight</button>
            </form>
        </div>
    </div>
</body>

</html>