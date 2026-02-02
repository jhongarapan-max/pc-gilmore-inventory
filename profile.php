<?php
/**
 * User Profile
 */
require_once 'init.php';
requireLogin();

$pageTitle = 'My Profile';
$message = '';
$messageType = '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user details
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        
        if (empty($full_name) || empty($email)) {
            $message = 'Full name and email are required.';
            $messageType = 'danger';
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $_SESSION['user_id']]);
            
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            
            logAudit('update', 'profile', 'Updated profile information');
            
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
    
    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'All password fields are required.';
            $messageType = 'danger';
        } elseif (!password_verify($current_password, $user['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'New passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($new_password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'danger';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            
            logAudit('update', 'profile', 'Changed password');
            
            $message = 'Password changed successfully!';
            $messageType = 'success';
        }
    }
    
} catch (Exception $e) {
    error_log("Profile error: " . $e->getMessage());
    $message = "Error updating profile.";
    $messageType = 'danger';
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-person-circle"></i> My Profile</h2>
        <hr>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo strtoupper($user['role']); ?>" disabled>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               minlength="6" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               minlength="6" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="bi bi-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Account Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Account Created:</strong><br><?php echo formatDateTime($user['created_at']); ?></p>
                <p><strong>Last Updated:</strong><br><?php echo formatDateTime($user['updated_at']); ?></p>
                <p><strong>Last Login:</strong><br><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'N/A'; ?></p>
                <p><strong>Account Status:</strong><br>
                    <span class="badge bg-success">Active</span>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
