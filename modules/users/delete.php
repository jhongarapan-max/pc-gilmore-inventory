<?php
/**
 * Delete User (Admin Only)
 */
require_once '../../init.php';

if ($_SESSION['user_role'] !== ROLE_ADMIN) {
    redirect('unauthorized.php');
}

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    redirect('modules/users/index.php');
}

// Cannot delete self
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot delete your own account.';
    redirect('modules/users/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $deleteStmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $deleteStmt->execute([$user_id]);
        
        logAudit('delete', 'users', "Deleted user: {$user['username']}", $user_id);
        
        $_SESSION['success'] = 'User deleted successfully!';
    } else {
        $_SESSION['error'] = 'User not found.';
    }
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    $_SESSION['error'] = 'Error deleting user.';
}

redirect('modules/users/index.php');
