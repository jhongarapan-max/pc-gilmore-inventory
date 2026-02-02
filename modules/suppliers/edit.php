<?php
/**
 * Edit Supplier
 */
require_once '../../init.php';
requirePermission('suppliers');

$pageTitle = 'Edit Supplier';
$message = '';
$messageType = '';
$supplier = null;

$supplier_id = intval($_GET['id'] ?? 0);

if (!$supplier_id) {
    redirect('modules/suppliers/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();
    
    if (!$supplier) {
        $_SESSION['error'] = 'Supplier not found.';
        redirect('modules/suppliers/index.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $supplier_name = sanitize($_POST['supplier_name']);
        $contact_person = sanitize($_POST['contact_person']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($supplier_name)) {
            $message = 'Supplier name is required.';
            $messageType = 'danger';
        } else {
            $stmt = $db->prepare("
                UPDATE suppliers SET 
                    supplier_name = ?, contact_person = ?, phone = ?, 
                    email = ?, address = ?, is_active = ?
                WHERE supplier_id = ?
            ");
            $stmt->execute([$supplier_name, $contact_person, $phone, $email, $address, $is_active, $supplier_id]);
            
            logAudit('update', 'suppliers', "Updated supplier: {$supplier_name}", $supplier_id);
            
            $_SESSION['success'] = 'Supplier updated successfully!';
            redirect('modules/suppliers/index.php');
        }
    }
} catch (Exception $e) {
    error_log("Edit supplier error: " . $e->getMessage());
    $message = "Error updating supplier.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-pencil"></i> Edit Supplier</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Suppliers</a></li>
                <li class="breadcrumb-item active">Edit Supplier</li>
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
                <h5 class="mb-0">Edit Supplier Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                               value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person"
                               value="<?php echo htmlspecialchars($supplier['contact_person']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($supplier['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($supplier['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($supplier['address']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo $supplier['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
