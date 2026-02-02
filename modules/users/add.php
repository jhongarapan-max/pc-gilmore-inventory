<?php
/**
 * Add User (Admin Only)
 */
require_once '../../init.php';

if ($_SESSION['user_role'] !== ROLE_ADMIN) {
    redirect('unauthorized.php');
}

$pageTitle = 'Add User';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $role = sanitize($_POST['role']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate
        if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
            $message = 'All fields are required.';
            $messageType = 'danger';
        } elseif ($password !== $confirm_password) {
            $message = 'Passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'danger';
        } else {
            // Check if username exists
            $checkStmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Username already exists.';
                $messageType = 'danger';
            } else {
                // Check if email exists
                $checkEmailStmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
                $checkEmailStmt->execute([$email]);
                
                if ($checkEmailStmt->rowCount() > 0) {
                    $message = 'Email already exists.';
                    $messageType = 'danger';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user
                    $stmt = $db->prepare("
                        INSERT INTO users (username, password, full_name, email, role, is_active)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$username, $hashed_password, $full_name, $email, $role, $is_active]);
                    
                    logAudit('create', 'users', "Created user: {$username} with role: {$role}", $db->lastInsertId());
                    
                    $_SESSION['success'] = 'User added successfully!';
                    redirect('modules/users/index.php');
                }
            }
        }
    } catch (Exception $e) {
        error_log("Add user error: " . $e->getMessage());
        $message = "Error adding user.";
        $messageType = 'danger';
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-person-plus"></i> Add New User</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Users</a></li>
                <li class="breadcrumb-item active">Add User</li>
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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin - Full system access</option>
                            <option value="manager">Manager - Inventory, POS, Stock, Reports</option>
                            <option value="cashier">Cashier - POS and Scanner only</option>
                            <option value="warehouse">Warehouse - Inventory and Stock</option>
                            <option value="viewer">Viewer - Reports only (read-only)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
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
                            <i class="bi bi-save"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Security Tips</h5>
            </div>
            <div class="card-body">
                <ul class="small">
                    <li>Use strong passwords (min 6 characters)</li>
                    <li>Assign appropriate role based on job function</li>
                    <li>Deactivate users when they leave</li>
                    <li>Review user access regularly</li>
                    <li>Never share login credentials</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
