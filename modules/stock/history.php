<?php
/**
 * Stock Movement History
 */
require_once '../../init.php';
requirePermission('stock');

$pageTitle = 'Stock Movement History';

try {
    $db = Database::getInstance()->getConnection();
    
    // Handle filters
    $itemFilter = $_GET['item'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    // Build query
    $query = "SELECT sm.*, i.item_name, i.barcode, u.full_name as user_name
              FROM stock_movements sm
              JOIN items i ON sm.item_id = i.item_id
              LEFT JOIN users u ON sm.user_id = u.user_id
              WHERE 1=1";
    $params = [];
    
    if ($itemFilter) {
        $query .= " AND sm.item_id = ?";
        $params[] = $itemFilter;
    }
    
    if ($typeFilter) {
        $query .= " AND sm.movement_type = ?";
        $params[] = $typeFilter;
    }
    
    if ($dateFrom) {
        $query .= " AND DATE(sm.created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    if ($dateTo) {
        $query .= " AND DATE(sm.created_at) <= ?";
        $params[] = $dateTo;
    }
    
    $query .= " ORDER BY sm.created_at DESC LIMIT 500";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $movements = $stmt->fetchAll();
    
    // Get items for filter
    $itemsStmt = $db->query("SELECT item_id, item_name FROM items WHERE is_active = 1 ORDER BY item_name");
    $items = $itemsStmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Stock history error: " . $e->getMessage());
    $error = "Error loading stock history.";
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-clock-history"></i> Stock Movement History</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                <li class="breadcrumb-item">Stock</li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </nav>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="item" class="form-select">
                            <option value="">All Items</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['item_id']; ?>" <?php echo $itemFilter == $item['item_id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($item['item_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="in" <?php echo $typeFilter === 'in' ? 'selected' : ''; ?>>Stock In</option>
                            <option value="out" <?php echo $typeFilter === 'out' ? 'selected' : ''; ?>>Stock Out</option>
                            <option value="sale" <?php echo $typeFilter === 'sale' ? 'selected' : ''; ?>>Sale</option>
                            <option value="adjustment" <?php echo $typeFilter === 'adjustment' ? 'selected' : ''; ?>>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                            <a href="history.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        </div>
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
                <h5 class="mb-0">Movement Records (<?php echo count($movements); ?> records)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="historyTable">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Cost</th>
                                <th>Reference</th>
                                <th>User</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movements)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movements as $movement): ?>
                                    <?php
                                        $typeBadge = [
                                            'in' => 'success',
                                            'out' => 'danger',
                                            'sale' => 'info',
                                            'adjustment' => 'warning'
                                        ][$movement['movement_type']] ?? 'secondary';
                                        
                                        $typeIcon = [
                                            'in' => 'box-arrow-in-down',
                                            'out' => 'box-arrow-up',
                                            'sale' => 'cart3',
                                            'adjustment' => 'gear'
                                        ][$movement['movement_type']] ?? 'circle';
                                    ?>
                                    <tr>
                                        <td><?php echo formatDateTime($movement['created_at']); ?></td>
                                        <td>
                                            <strong><?php echo sanitize($movement['item_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo $movement['barcode']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $typeBadge; ?>">
                                                <i class="bi bi-<?php echo $typeIcon; ?>"></i>
                                                <?php echo strtoupper($movement['movement_type']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($movement['movement_type'] === 'in'): ?>
                                                <span class="text-success">+<?php echo $movement['quantity']; ?></span>
                                            <?php else: ?>
                                                <span class="text-danger">-<?php echo $movement['quantity']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php echo $movement['unit_cost'] ? formatCurrency($movement['unit_cost']) : '-'; ?>
                                        </td>
                                        <td><?php echo sanitize($movement['reference_no']) ?: '-'; ?></td>
                                        <td><?php echo sanitize($movement['user_name']); ?></td>
                                        <td>
                                            <small><?php echo sanitize($movement['notes']) ?: '-'; ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button onclick="window.print();" class="btn btn-secondary btn-sm">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button onclick="exportTableToCSV('historyTable', 'stock_history_<?php echo date('Y-m-d'); ?>.csv');" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
