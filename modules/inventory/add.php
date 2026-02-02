<?php
/**
 * Add New Item
 */
require_once '../../init.php';
requirePermission('inventory');

$pageTitle = 'Add New Item';
$message = '';
$messageType = '';

try {
    $db = Database::getInstance()->getConnection();
    
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
        $stock_quantity = intval($_POST['stock_quantity']);
        $min_stock_level = intval($_POST['min_stock_level']);
        $max_stock_level = intval($_POST['max_stock_level']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate
        if (empty($barcode) || empty($item_name)) {
            $message = 'Barcode and Item Name are required.';
            $messageType = 'danger';
        } else {
            // Check if barcode already exists
            $checkStmt = $db->prepare("SELECT item_id FROM items WHERE barcode = ?");
            $checkStmt->execute([$barcode]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Barcode already exists. Please use a unique barcode.';
                $messageType = 'danger';
            } else {
                // Insert item
                $stmt = $db->prepare("
                    INSERT INTO items (barcode, item_name, description, category_id, unit, 
                                      cost_price, selling_price, stock_quantity, min_stock_level, 
                                      max_stock_level, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $barcode, $item_name, $description, $category_id, $unit,
                    $cost_price, $selling_price, $stock_quantity, $min_stock_level,
                    $max_stock_level, $is_active
                ]);
                
                $item_id = $db->lastInsertId();
                
                // Log audit
                logAudit('create', 'inventory', "Added new item: {$item_name}", $item_id);
                
                // Log initial stock if any
                if ($stock_quantity > 0) {
                    $stockStmt = $db->prepare("
                        INSERT INTO stock_movements (item_id, movement_type, quantity, unit_cost, 
                                                    notes, user_id)
                        VALUES (?, 'in', ?, ?, 'Initial stock', ?)
                    ");
                    $stockStmt->execute([$item_id, $stock_quantity, $cost_price, $_SESSION['user_id']]);
                }
                
                $_SESSION['success'] = 'Item added successfully!';
                redirect('modules/inventory/index.php');
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Add item error: " . $e->getMessage());
    $message = "Error adding item.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-plus-circle"></i> Add New Item</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Inventory</a></li>
                <li class="breadcrumb-item active">Add Item</li>
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
                <h5 class="mb-0">Item Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="addItemForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode / QR Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barcode" name="barcode" required autofocus
                                   placeholder="Scan or enter barcode">
                            <small class="text-muted">Use barcode scanner or enter manually</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required
                                   placeholder="Enter item name">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Enter item description"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>">
                                        <?php echo sanitize($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" value="pcs"
                                   placeholder="e.g., pcs, box, set">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cost_price" class="form-label">Cost Price (₱)</label>
                            <input type="number" class="form-control currency-input" id="cost_price" name="cost_price" 
                                   step="0.01" min="0" value="0.00" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="selling_price" class="form-label">Selling Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control currency-input" id="selling_price" name="selling_price" 
                                   step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="stock_quantity" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   min="0" value="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="min_stock_level" class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" 
                                   min="0" value="10">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="max_stock_level" class="form-label">Max Stock Level</label>
                            <input type="number" class="form-control" id="max_stock_level" name="max_stock_level" 
                                   min="0" value="1000">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
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
                            <i class="bi bi-save"></i> Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Use a USB barcode scanner for quick entry
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Ensure barcode is unique
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Set appropriate min/max stock levels
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Cost price is for internal tracking
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        Selling price is shown to customers
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
