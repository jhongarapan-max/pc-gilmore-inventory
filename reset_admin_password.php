<?php
/**
 * Admin Password Reset Script
 * Run this once to fix the admin password, then DELETE this file!
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>PC Gilmore - Admin Password Reset</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Generate correct hash for 'admin123'
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashedPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green; font-size: 18px;'><strong>SUCCESS!</strong> Admin password has been reset to: <code>admin123</code></p>";
        echo "<p>You can now login with:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
        echo "<p><a href='login.php'>Click here to login</a></p>";
        echo "<hr>";
        echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (reset_admin_password.php) after logging in!</p>";
    } else {
        echo "<p style='color: orange;'>No admin user found. You may need to import the database first.</p>";
        echo "<p>Run this SQL in phpMyAdmin:</p>";
        echo "<pre>INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '" . $hashedPassword . "', 'System Administrator', 'admin@pcgilmore.com', 'admin');</pre>";
    }
    
    // Also display the hash for reference
    echo "<hr>";
    echo "<p><strong>Generated Hash:</strong></p>";
    echo "<pre>" . $hashedPassword . "</pre>";
    echo "<p><small>This hash is for password: admin123</small></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>MySQL is running in XAMPP</li>";
    echo "<li>Database 'pc_gilmore_inventory' exists</li>";
    echo "<li>The schema.sql has been imported</li>";
    echo "</ul>";
}
?>
