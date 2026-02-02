<?php
/**
 * Unauthorized Access Page
 */
require_once 'init.php';
$pageTitle = 'Unauthorized Access';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3 text-center mt-5">
        <div class="card">
            <div class="card-body p-5">
                <i class="bi bi-shield-exclamation text-danger" style="font-size: 80px;"></i>
                <h1 class="mt-4">Access Denied</h1>
                <p class="lead">You don't have permission to access this resource.</p>
                <p class="text-muted">
                    Your current role: <strong><?php echo strtoupper($_SESSION['user_role'] ?? 'UNKNOWN'); ?></strong>
                </p>
                <hr>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
