<div class="header">
    <h1>✨ AstroFlux Admin Panel</h1>
    <div class="user-info">
        <span>👤
            <?php echo htmlspecialchars($admin['username'] ?? ''); ?> (
            <?php echo htmlspecialchars($admin['role'] ?? ''); ?>)
        </span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<nav class="nav">
    <a href="index.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a>
    <a href="horoscopes.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) == 'horoscopes.php' ? 'active' : ''; ?>">Horoscopes</a>
    <a href="tarot.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tarot.php' ? 'active' : ''; ?>">Tarot</a>
    <a href="insights.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) == 'insights.php' ? 'active' : ''; ?>">Insights</a>
    <a href="settings.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">Settings</a>
</nav>