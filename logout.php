<?php
/**
 * Logout
 */
require_once 'init.php';

// Log audit before destroying session
if (isLoggedIn()) {
    logAudit('logout', 'auth', 'User logged out');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
redirect('login.php');
