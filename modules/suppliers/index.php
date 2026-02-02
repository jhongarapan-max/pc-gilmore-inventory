<?php
/**
 * Suppliers Management
 */
require_once '../../init.php';
requirePermission('suppliers');

$pageTitle = 'Suppliers';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all suppliers
    $stmt = $db->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
    $suppliers = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Suppliers error: " . $e->getMessage());
    $error = "Error loading suppliers.";
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-truck"></i> Suppliers Management</h2>
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
            <i class="bi bi-plus-circle"></i> Add New Supplier
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Suppliers List (<?php echo count($suppliers); ?> suppliers)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suppliers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No suppliers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($supplier['supplier_name']); ?></strong></td>
                                        <td><?php echo sanitize($supplier['contact_person']); ?></td>
                                        <td><?php echo sanitize($supplier['phone']); ?></td>
                                        <td><?php echo sanitize($supplier['email']); ?></td>
                                        <td><?php echo sanitize($supplier['address']); ?></td>
                                        <td>
                                            <?php if ($supplier['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="edit.php?id=<?php echo $supplier['supplier_id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $supplier['supplier_id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirmDelete('<?php echo sanitize($supplier['supplier_name']); ?>');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
