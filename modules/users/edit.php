<?php
/**
 * Edit User (Admin Only)
 */
require_once '../../init.php';

if ($_SESSION['user_role'] !== ROLE_ADMIN) {
    redirect('unauthorized.php');
}

$pageTitle = 'Edit User';
$message = '';
$messageType = '';
$user = null;

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    redirect('modules/users/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        redirect('modules/users/index.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = sanitize($_POST['username']);
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $role = sanitize($_POST['role']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($full_name) || empty($email)) {
            $message = 'Required fields cannot be empty.';
            $messageType = 'danger';
        } elseif (!empty($password) && $password !== $confirm_password) {
            $message = 'Passwords do not match.';
            $messageType = 'danger';
        } elseif (!empty($password) && strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'danger';
        } else {
            // Check username uniqueness
            $checkStmt = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $checkStmt->execute([$username, $user_id]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Username already exists.';
                $messageType = 'danger';
            } else {
                // Update user
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        UPDATE users SET 
                            username = ?, password = ?, full_name = ?, 
                            email = ?, role = ?, is_active = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$username, $hashed_password, $full_name, $email, $role, $is_active, $user_id]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE users SET 
                            username = ?, full_name = ?, email = ?, 
                            role = ?, is_active = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$username, $full_name, $email, $role, $is_active, $user_id]);
                }
                
                logAudit('update', 'users', "Updated user: {$username}", $user_id);
                
                $_SESSION['success'] = 'User updated successfully!';
                redirect('modules/users/index.php');
            }
        }
    }
} catch (Exception $e) {
    error_log("Edit user error: " . $e->getMessage());
    $message = "Error updating user.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-person-gear"></i> Edit User</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Users</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>
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
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Edit User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Leave password fields blank to keep current password
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password (Optional)</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                            <option value="cashier" <?php echo $user['role'] === 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                            <option value="warehouse" <?php echo $user['role'] === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                            <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active (User can login)
                            </label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> User Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo formatDateTime($user['created_at']); ?></p>
                <p><strong>Last Updated:</strong> <?php echo formatDateTime($user['updated_at']); ?></p>
                <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
