<?php
/**
 * Admin Logout Handler
 * Play Console Compliant - Secure logout
 */

require_once __DIR__ . '/../includes/auth.php';

// Perform logout
admin_logout();

// Redirect to login page
header('Location: login.php?logout=1');
exit;
?>