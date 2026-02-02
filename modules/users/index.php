<?php
/**
 * User Management (Admin Only)
 */
require_once '../../init.php';

// Only admin can access
if ($_SESSION['user_role'] !== ROLE_ADMIN) {
    redirect('unauthorized.php');
}

$pageTitle = 'User Management';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all users
    $stmt = $db->query("SELECT * FROM users ORDER BY full_name ASC");
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Users error: " . $e->getMessage());
    $error = "Error loading users.";
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-people"></i> User Management</h2>
        <hr>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-12">
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New User
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">System Users (<?php echo count($users); ?> users)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?php echo sanitize($user['full_name']); ?></strong></td>
                                    <td><?php echo sanitize($user['username']); ?></td>
                                    <td><?php echo sanitize($user['email']); ?></td>
                                    <td>
                                        <?php
                                            $roleBadge = [
                                                'admin' => 'danger',
                                                'manager' => 'primary',
                                                'cashier' => 'success',
                                                'warehouse' => 'info',
                                                'viewer' => 'secondary'
                                            ][$user['role']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $roleBadge; ?>">
                                            <?php echo strtoupper($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="delete.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirmDelete('<?php echo sanitize($user['username']); ?>');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Descriptions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Role Descriptions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><span class="badge bg-danger">ADMIN</span></h6>
                        <p class="small">Full system access including user management</p>
                        
                        <h6><span class="badge bg-primary">MANAGER</span></h6>
                        <p class="small">Inventory, POS, Stock, Reports, Suppliers</p>
                        
                        <h6><span class="badge bg-success">CASHIER</span></h6>
                        <p class="small">POS and Scanner only</p>
                    </div>
                    <div class="col-md-6">
                        <h6><span class="badge bg-info">WAREHOUSE</span></h6>
                        <p class="small">Inventory, Stock movements, Scanner</p>
                        
                        <h6><span class="badge bg-secondary">VIEWER</span></h6>
                        <p class="small">Reports and Scanner (read-only access)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
