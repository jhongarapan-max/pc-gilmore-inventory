<?php
/**
 * Inventory Management - List Items
 */
require_once '../../init.php';
requirePermission('inventory');

$pageTitle = 'Inventory Management';
$message = '';
$messageType = '';

try {
    $db = Database::getInstance()->getConnection();
    
    // Handle search and filters
    $search = $_GET['search'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $stockFilter = $_GET['stock'] ?? '';
    
    // Build query
    $query = "SELECT i.*, c.category_name 
              FROM items i 
              LEFT JOIN categories c ON i.category_id = c.category_id 
              WHERE 1=1";
    $params = [];
    
    if ($search) {
        $query .= " AND (i.barcode LIKE ? OR i.item_name LIKE ? OR i.description LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if ($categoryFilter) {
        $query .= " AND i.category_id = ?";
        $params[] = $categoryFilter;
    }
    
    if ($stockFilter === 'low') {
        $query .= " AND i.stock_quantity <= i.min_stock_level";
    } elseif ($stockFilter === 'out') {
        $query .= " AND i.stock_quantity = 0";
    }
    
    $query .= " ORDER BY i.item_name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    // Get categories for filter
    $categoriesStmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name");
    $categories = $categoriesStmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Inventory error: " . $e->getMessage());
    $message = "Error loading inventory data.";
    $messageType = 'danger';
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-box-seam"></i> Inventory Management</h2>
        <hr>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, barcode, or description" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo $categoryFilter == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="stock" class="form-select">
                            <option value="">All Stock</option>
                            <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Item</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Items List (<?php echo count($items); ?> items)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Stock</th>
                                <th>Status</th>
                                <th class="text-center no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No items found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php 
                                        $rowClass = '';
                                        if ($item['stock_quantity'] == 0) {
                                            $rowClass = 'out-of-stock-row';
                                        } elseif ($item['stock_quantity'] <= $item['min_stock_level']) {
                                            $rowClass = 'low-stock-row';
                                        }
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><code><?php echo sanitize($item['barcode']); ?></code></td>
                                        <td>
                                            <strong><?php echo sanitize($item['item_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo sanitize($item['description']); ?></small>
                                        </td>
                                        <td><?php echo sanitize($item['category_name']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($item['cost_price']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($item['selling_price']); ?></td>
                                        <td class="text-center">
                                            <?php if ($item['stock_quantity'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($item['stock_quantity'] <= $item['min_stock_level']): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="view.php?id=<?php echo $item['item_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $item['item_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $item['item_id']; ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirmDelete('<?php echo sanitize($item['item_name']); ?>');" title="Delete">
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
            <div class="card-footer no-print">
                <button onclick="window.print();" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('inventoryTable', 'inventory_<?php echo date('Y-m-d'); ?>.csv');" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
