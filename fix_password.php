<?php
/**
 * SIMPLE PASSWORD FIX
 * Just run this file and it will fix the admin password
 */

// Database connection settings (same as your config)
$host = 'localhost';
$dbname = 'pc_gilmore_inventory';
$user = 'root';
$pass = '';

echo "<h1>Fixing Admin Password...</h1>";

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Connected to database: <strong>$dbname</strong></p>";
    
    // Generate new password hash for 'admin123'
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p>Generated new hash for password: <strong>$password</strong></p>";
    
    // Check if admin exists
    $check = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $exists = $check->fetchColumn();
    
    if ($exists > 0) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = 'admin'");
        $stmt->execute([$hash]);
        echo "<p style='color:green;font-size:24px;'><strong>SUCCESS! Admin password updated!</strong></p>";
    } else {
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, is_active) VALUES ('admin', ?, 'System Administrator', 'admin@pcgilmore.com', 'admin', 1)");
        $stmt->execute([$hash]);
        echo "<p style='color:green;font-size:24px;'><strong>SUCCESS! Admin user created!</strong></p>";
    }
    
    echo "<hr>";
    echo "<h2>Login Credentials:</h2>";
    echo "<p style='font-size:20px;'><strong>Username:</strong> admin</p>";
    echo "<p style='font-size:20px;'><strong>Password:</strong> admin123</p>";
    echo "<br>";
    echo "<a href='login.php' style='background:#0d6efd;color:white;padding:15px 30px;text-decoration:none;font-size:18px;border-radius:5px;'>Go to Login Page</a>";
    echo "<br><br><br>";
    echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete this file (fix_password.php) after logging in!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;font-size:18px;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure <strong>MySQL is running</strong> in XAMPP Control Panel</li>";
    echo "<li>Make sure database <strong>'pc_gilmore_inventory'</strong> exists</li>";
    echo "<li>Import the <strong>database/schema.sql</strong> file in phpMyAdmin if you haven't</li>";
    echo "</ol>";
}
?>
