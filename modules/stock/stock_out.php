<?php
/**
 * Stock Out - Remove inventory
 */
require_once '../../init.php';
requirePermission('stock');

$pageTitle = 'Stock Out';
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
        $reference_no = sanitize($_POST['reference_no']);
        $notes = sanitize($_POST['notes']);
        
        if ($item_id && $quantity > 0) {
            // Check current stock
            $stockStmt = $db->prepare("SELECT stock_quantity, item_name FROM items WHERE item_id = ?");
            $stockStmt->execute([$item_id]);
            $itemData = $stockStmt->fetch();
            
            if ($itemData['stock_quantity'] < $quantity) {
                $message = "Insufficient stock! Current stock: {$itemData['stock_quantity']}";
                $messageType = 'danger';
            } else {
                $db->beginTransaction();
                
                try {
                    // Update item stock
                    $stmt = $db->prepare("UPDATE items SET stock_quantity = stock_quantity - ? WHERE item_id = ?");
                    $stmt->execute([$quantity, $item_id]);
                    
                    // Log stock movement
                    $stmt = $db->prepare("
                        INSERT INTO stock_movements (item_id, movement_type, quantity, reference_no, notes, user_id)
                        VALUES (?, 'out', ?, ?, ?, ?)
                    ");
                    $stmt->execute([$item_id, $quantity, $reference_no, $notes, $_SESSION['user_id']]);
                    
                    // Log audit
                    logAudit('stock_out', 'stock', "Removed {$quantity} units from {$itemData['item_name']}", $item_id);
                    
                    $db->commit();
                    
                    $message = 'Stock removed successfully!';
                    $messageType = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        }
    }
    
} catch (Exception $e) {
    error_log("Stock Out error: " . $e->getMessage());
    $message = "Error removing stock.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-box-arrow-up"></i> Stock Out</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item">Stock</li>
                <li class="breadcrumb-item active">Stock Out</li>
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
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Remove Stock</h5>
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
                                        data-barcode="<?php echo $item['barcode']; ?>"
                                        data-stock="<?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>">
                                    <?php echo sanitize($item['item_name']); ?> 
                                    (Stock: <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="currentStockDisplay"></div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Remove <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               min="1" required placeholder="Enter quantity to remove">
                        <small class="text-danger">Cannot exceed current stock level</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference_no" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" 
                               placeholder="e.g., Transfer #, Damage Report #">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" required
                                  placeholder="Reason for stock removal (e.g., damaged, lost, transferred)"></textarea>
                        <small class="text-muted">Reason is required for audit purposes</small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="history.php" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> View History
                        </a>
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="bi bi-dash-circle"></i> Remove Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Important</h5>
            </div>
            <div class="card-body">
                <p><strong>Stock Out is used for:</strong></p>
                <ul>
                    <li>Damaged items</li>
                    <li>Lost items</li>
                    <li>Stock transfers</li>
                    <li>Write-offs</li>
                    <li>Adjustments</li>
                </ul>
                <hr>
                <p class="text-danger mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    <strong>Note:</strong> Regular sales are automatically deducted via POS. Use Stock Out only for non-sale reductions.
                </p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Quick Links</h6>
            </div>
            <div class="card-body">
                <a href="stock_in.php" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-box-arrow-in-down"></i> Stock In
                </a>
                <a href="history.php" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-clock-history"></i> Stock History
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Show current stock when item selected
document.getElementById('item_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const currentStock = selectedOption.getAttribute('data-stock');
    
    if (currentStock) {
        document.getElementById('currentStockDisplay').innerHTML = 
            '<div class="alert alert-info"><strong>Current Stock:</strong> ' + currentStock + '</div>';
        document.getElementById('quantity').setAttribute('max', parseInt(currentStock));
    } else {
        document.getElementById('currentStockDisplay').innerHTML = '';
    }
});

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
            select.dispatchEvent(new Event('change'));
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
