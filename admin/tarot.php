<?php
/**
 * Tarot Card Management
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
        $name = sanitize_input($_POST['name'] ?? '');
        $card_type = sanitize_input($_POST['card_type'] ?? '');
        $suit = sanitize_input($_POST['suit'] ?? 'none');
        $number = (int) ($_POST['number'] ?? 0);
        $emoji = sanitize_input($_POST['emoji'] ?? '');
        $meaning_upright = sanitize_input($_POST['meaning_upright'] ?? '');
        $meaning_reversed = sanitize_input($_POST['meaning_reversed'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $keywords = sanitize_input($_POST['keywords'] ?? '');

        try {
            $stmt = $db->prepare("
                INSERT INTO tarot_cards 
                (name, card_type, suit, number, emoji, meaning_upright, meaning_reversed, 
                 description, keywords)
                VALUES (:name, :type, :suit, :number, :emoji, :upright, :reversed, :desc, :keywords)
            ");

            $stmt->execute([
                'name' => $name,
                'type' => $card_type,
                'suit' => $suit,
                'number' => $number,
                'emoji' => $emoji,
                'upright' => $meaning_upright,
                'reversed' => $meaning_reversed,
                'desc' => $description,
                'keywords' => $keywords
            ]);

            $card_id = $db->lastInsertId();
            log_admin_action($admin['id'], 'create_tarot_card', 'tarot_cards', $card_id);
            $success = 'Tarot card created successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to create tarot card: ' . $e->getMessage();
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $stmt = $db->prepare("DELETE FROM tarot_cards WHERE id = :id");
            $stmt->execute(['id' => $id]);

            log_admin_action($admin['id'], 'delete_tarot_card', 'tarot_cards', $id);
            $success = 'Tarot card deleted successfully!';

        } catch (PDOException $e) {
            $error = 'Failed to delete tarot card.';
        }
    }
}

// Fetch tarot cards
$db = get_db_connection();
$stmt = $db->query("SELECT * FROM tarot_cards ORDER BY card_type, suit, number LIMIT 100");
$tarot_cards = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tarot Cards - AstroFlux Admin</title>
    <style>
        <?php include __DIR__ . '/../assets/css/admin-style.php'; ?>
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>🔮 Manage Tarot Cards</h1>
            <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='block'">
                + New Tarot Card
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
                        <th>Name</th>
                        <th>Type</th>
                        <th>Suit</th>
                        <th>Number</th>
                        <th>Emoji</th>
                        <th>Keywords</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarot_cards as $card): ?>
                        <tr>
                            <td>
                                <?php echo $card['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($card['name']); ?>
                            </td>
                            <td>
                                <?php echo ucwords(str_replace('_', ' ', $card['card_type'])); ?>
                            </td>
                            <td>
                                <?php echo ucfirst($card['suit']); ?>
                            </td>
                            <td>
                                <?php echo $card['number']; ?>
                            </td>
                            <td>
                                <?php echo $card['emoji']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($card['keywords'], 0, 30)) . '...'; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this card?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $card['id']; ?>">
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
            <h2>Create New Tarot Card</h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Card Name *</label>
                    <input type="text" name="name" required placeholder="The Fool">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Card Type *</label>
                        <select name="card_type" required>
                            <option value="major_arcana">Major Arcana</option>
                            <option value="minor_arcana">Minor Arcana</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Suit</label>
                        <select name="suit">
                            <option value="none">None</option>
                            <option value="cups">Cups</option>
                            <option value="wands">Wands</option>
                            <option value="swords">Swords</option>
                            <option value="pentacles">Pentacles</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Number</label>
                        <input type="number" name="number" min="0" max="21" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Emoji</label>
                    <input type="text" name="emoji" placeholder="🔮">
                </div>

                <div class="form-group">
                    <label>Meaning Upright *</label>
                    <textarea name="meaning_upright" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label>Meaning Reversed *</label>
                    <textarea name="meaning_reversed" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Keywords (comma separated)</label>
                    <input type="text" name="keywords" placeholder="new beginnings, innocence, adventure">
                </div>

                <button type="submit" class="btn btn-primary">Create Card</button>
            </form>
        </div>
    </div>
</body>

</html>