<?php
/**
 * Stock In - Add inventory
 */
require_once '../../init.php';
requirePermission('stock');

$pageTitle = 'Stock In';
$message = '';
$messageType = '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get items for dropdown
    $itemsStmt = $db->query("SELECT item_id, item_name, barcode, stock_quantity, unit FROM items WHERE is_active = 1 ORDER BY item_name");
    $items = $itemsStmt->fetchAll();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item_id = intval($_POST['item_id']);
        $quantity = intval($_POST['quantity']);
        $unit_cost = floatval($_POST['unit_cost']);
        $reference_no = sanitize($_POST['reference_no']);
        $notes = sanitize($_POST['notes']);
        
        if ($item_id && $quantity > 0) {
            $db->beginTransaction();
            
            try {
                // Update item stock
                $stmt = $db->prepare("UPDATE items SET stock_quantity = stock_quantity + ? WHERE item_id = ?");
                $stmt->execute([$quantity, $item_id]);
                
                // Log stock movement
                $stmt = $db->prepare("
                    INSERT INTO stock_movements (item_id, movement_type, quantity, unit_cost, reference_no, notes, user_id)
                    VALUES (?, 'in', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$item_id, $quantity, $unit_cost, $reference_no, $notes, $_SESSION['user_id']]);
                
                // Get item name for audit
                $itemStmt = $db->prepare("SELECT item_name FROM items WHERE item_id = ?");
                $itemStmt->execute([$item_id]);
                $item_name = $itemStmt->fetch()['item_name'];
                
                // Log audit
                logAudit('stock_in', 'stock', "Added {$quantity} units to {$item_name}", $item_id);
                
                $db->commit();
                
                $message = 'Stock added successfully!';
                $messageType = 'success';
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        }
    }
    
} catch (Exception $e) {
    error_log("Stock In error: " . $e->getMessage());
    $message = "Error adding stock.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-box-arrow-in-down"></i> Stock In</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item">Stock</li>
                <li class="breadcrumb-item active">Stock In</li>
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
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Add Stock</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="barcodeSearch" class="form-label">Scan Barcode (Optional)</label>
                        <input type="text" class="form-control scanner-input" id="barcodeSearch" 
                               placeholder="Scan barcode to auto-select item" autocomplete="off">
                        <small class="text-muted">Scan barcode or select item from dropdown below</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="item_id" class="form-label">Select Item <span class="text-danger">*</span></label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <option value="">Choose item...</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['item_id']; ?>" 
                                        data-barcode="<?php echo $item['barcode']; ?>">
                                    <?php echo sanitize($item['item_name']); ?> 
                                    (Current: <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="1" required placeholder="Enter quantity to add">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="unit_cost" class="form-label">Unit Cost (₱)</label>
                            <input type="number" class="form-control currency-input" id="unit_cost" name="unit_cost" 
                                   step="0.01" min="0" value="0.00" placeholder="Cost per unit">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference_no" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" 
                               placeholder="e.g., PO-12345, Delivery Receipt #">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes about this stock addition"></textarea>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="history.php" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> View History
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-plus-circle"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Instructions</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li class="mb-2">Scan barcode or select item from dropdown</li>
                    <li class="mb-2">Enter quantity to add</li>
                    <li class="mb-2">Enter unit cost (optional but recommended)</li>
                    <li class="mb-2">Add reference number if available</li>
                    <li>Click "Add Stock" to save</li>
                </ol>
                <hr>
                <p class="text-muted mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    All stock movements are logged and audited.
                </p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">Quick Links</h6>
            </div>
            <div class="card-body">
                <a href="stock_out.php" class="btn btn-danger btn-sm w-100 mb-2">
                    <i class="bi bi-box-arrow-up"></i> Stock Out
                </a>
                <a href="history.php" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-clock-history"></i> Stock History
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Barcode scanner support
document.getElementById('barcodeSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = this.value.trim();
        if (barcode) {
            searchByBarcode(barcode);
        }
    }
});

function searchByBarcode(barcode) {
    const select = document.getElementById('item_id');
    const options = select.options;
    
    for (let i = 0; i < options.length; i++) {
        if (options[i].getAttribute('data-barcode') === barcode) {
            select.selectedIndex = i;
            document.getElementById('barcodeSearch').value = '';
            document.getElementById('quantity').focus();
            showToast('Item found!', 'success');
            return;
        }
    }
    
    showToast('Item not found with barcode: ' + barcode, 'warning');
    document.getElementById('barcodeSearch').value = '';
}
</script>

<?php include '../../includes/footer.php'; ?>
