<?php
/**
 * General Configuration
 */

// Application settings
define('APP_NAME', 'PC Gilmore Inventory System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/pc-gilmore-inventory-system/');

// Environment Setup (development or production)
define('ENVIRONMENT', 'development');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Auto-detect HTTPS for secure cookies
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
ini_set('session.cookie_secure', $is_https ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');

// Session timeout (in seconds) - 8 hours
define('SESSION_TIMEOUT', 28800);

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting based on environment
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_CASHIER', 'cashier');
define('ROLE_WAREHOUSE', 'warehouse');
define('ROLE_VIEWER', 'viewer');

// Permission levels
$GLOBALS['role_permissions'] = [
    ROLE_ADMIN => ['all'],
    ROLE_MANAGER => ['inventory', 'pos', 'stock', 'reports', 'suppliers'],
    ROLE_CASHIER => ['pos', 'scanner'],
    ROLE_WAREHOUSE => ['inventory', 'stock', 'scanner'],
    ROLE_VIEWER => ['reports', 'scanner']
];
