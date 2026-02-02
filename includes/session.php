<?php
/**
 * Session Management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    
    if ($elapsed > SESSION_TIMEOUT) {
        // Session expired
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['error'] = 'Your session has expired. Please login again.';
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
