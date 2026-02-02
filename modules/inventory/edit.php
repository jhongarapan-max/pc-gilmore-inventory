<?php
/**
 * Edit Item
 */
require_once '../../init.php';
requirePermission('inventory');

$pageTitle = 'Edit Item';
$message = '';
$messageType = '';
$item = null;

try {
    $db = Database::getInstance()->getConnection();
    $item_id = intval($_GET['id'] ?? 0);
    
    if (!$item_id) {
        redirect('modules/inventory/index.php');
    }
    
    // Get item details
    $stmt = $db->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        $_SESSION['error'] = 'Item not found.';
        redirect('modules/inventory/index.php');
    }
    
    // Get categories for dropdown
    $categoriesStmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name");
    $categories = $categoriesStmt->fetchAll();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $barcode = sanitize($_POST['barcode']);
        $item_name = sanitize($_POST['item_name']);
        $description = sanitize($_POST['description']);
        $category_id = intval($_POST['category_id']);
        $unit = sanitize($_POST['unit']);
        $cost_price = floatval($_POST['cost_price']);
        $selling_price = floatval($_POST['selling_price']);
        $min_stock_level = intval($_POST['min_stock_level']);
        $max_stock_level = intval($_POST['max_stock_level']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate
        if (empty($barcode) || empty($item_name)) {
            $message = 'Barcode and Item Name are required.';
            $messageType = 'danger';
        } else {
            // Check if barcode already exists (excluding current item)
            $checkStmt = $db->prepare("SELECT item_id FROM items WHERE barcode = ? AND item_id != ?");
            $checkStmt->execute([$barcode, $item_id]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Barcode already exists. Please use a unique barcode.';
                $messageType = 'danger';
            } else {
                // Update item
                $stmt = $db->prepare("
                    UPDATE items SET 
                        barcode = ?, item_name = ?, description = ?, category_id = ?, unit = ?,
                        cost_price = ?, selling_price = ?, min_stock_level = ?, max_stock_level = ?,
                        is_active = ?
                    WHERE item_id = ?
                ");
                
                $stmt->execute([
                    $barcode, $item_name, $description, $category_id, $unit,
                    $cost_price, $selling_price, $min_stock_level, $max_stock_level,
                    $is_active, $item_id
                ]);
                
                // Log audit
                logAudit('update', 'inventory', "Updated item: {$item_name}", $item_id);
                
                $_SESSION['success'] = 'Item updated successfully!';
                redirect('modules/inventory/index.php');
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Edit item error: " . $e->getMessage());
    $message = "Error updating item.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-pencil"></i> Edit Item</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Inventory</a></li>
                <li class="breadcrumb-item active">Edit Item</li>
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
                <h5 class="mb-0">Edit Item Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode / QR Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barcode" name="barcode" required
                                   value="<?php echo htmlspecialchars($item['barcode']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required
                                   value="<?php echo htmlspecialchars($item['item_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" 
                                            <?php echo $item['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo sanitize($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" 
                                   value="<?php echo htmlspecialchars($item['unit']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cost_price" class="form-label">Cost Price (₱)</label>
                            <input type="number" class="form-control currency-input" id="cost_price" name="cost_price" 
                                   step="0.01" min="0" value="<?php echo $item['cost_price']; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="selling_price" class="form-label">Selling Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control currency-input" id="selling_price" name="selling_price" 
                                   step="0.01" min="0" value="<?php echo $item['selling_price']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="text" class="form-control" value="<?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>" disabled>
                            <small class="text-muted">Use Stock In/Out to adjust</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="min_stock_level" class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" 
                                   min="0" value="<?php echo $item['min_stock_level']; ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="max_stock_level" class="form-label">Max Stock Level</label>
                            <input type="number" class="form-control" id="max_stock_level" name="max_stock_level" 
                                   min="0" value="<?php echo $item['max_stock_level']; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active (Item is available for sale)
                            </label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Item Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo formatDateTime($item['created_at']); ?></p>
                <p><strong>Last Updated:</strong> <?php echo formatDateTime($item['updated_at']); ?></p>
                <hr>
                <p class="text-muted">
                    <i class="bi bi-exclamation-circle"></i>
                    To adjust stock quantity, use the Stock In/Out module.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
