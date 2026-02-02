<?php
/**
 * Bootstrap file - Include this at the top of every PHP file
 */

// Define access constant
define('ACCESS_ALLOWED', true);

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Include session management
require_once __DIR__ . '/includes/session.php';

// Include helper functions
require_once __DIR__ . '/includes/functions.php';
