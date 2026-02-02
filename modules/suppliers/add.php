<?php
/**
 * Add Supplier
 */
require_once '../../init.php';
requirePermission('suppliers');

$pageTitle = 'Add Supplier';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
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
                INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$supplier_name, $contact_person, $phone, $email, $address, $is_active]);
            
            logAudit('create', 'suppliers', "Added supplier: {$supplier_name}", $db->lastInsertId());
            
            $_SESSION['success'] = 'Supplier added successfully!';
            redirect('modules/suppliers/index.php');
        }
    } catch (Exception $e) {
        error_log("Add supplier error: " . $e->getMessage());
        $message = "Error adding supplier.";
        $messageType = 'danger';
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-plus-circle"></i> Add New Supplier</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Suppliers</a></li>
                <li class="breadcrumb-item active">Add Supplier</li>
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
                <h5 class="mb-0">Supplier Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
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
                            <i class="bi bi-save"></i> Save Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
