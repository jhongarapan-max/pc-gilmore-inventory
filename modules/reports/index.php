<?php
/**
 * Reports Module
 */
require_once '../../init.php';
requirePermission('reports');

$pageTitle = 'Reports';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get date range from filters
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Sales summary
    $salesStmt = $db->prepare("
        SELECT COUNT(*) as total_transactions, 
               COALESCE(SUM(total_amount), 0) as total_sales,
               COALESCE(AVG(total_amount), 0) as avg_transaction
        FROM sales 
        WHERE DATE(sale_date) BETWEEN ? AND ?
    ");
    $salesStmt->execute([$dateFrom, $dateTo]);
    $salesSummary = $salesStmt->fetch();
    
    // Top selling items
    $topItemsStmt = $db->prepare("
        SELECT i.item_name, i.barcode, 
               SUM(si.quantity) as total_qty,
               SUM(si.subtotal) as total_revenue
        FROM sale_items si
        JOIN items i ON si.item_id = i.item_id
        JOIN sales s ON si.sale_id = s.sale_id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        GROUP BY si.item_id
        ORDER BY total_qty DESC
        LIMIT 10
    ");
    $topItemsStmt->execute([$dateFrom, $dateTo]);
    $topItems = $topItemsStmt->fetchAll();
    
    // Low stock items
    $lowStockStmt = $db->query("
        SELECT i.*, c.category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        WHERE i.stock_quantity <= i.min_stock_level AND i.is_active = 1
        ORDER BY i.stock_quantity ASC
        LIMIT 20
    ");
    $lowStockItems = $lowStockStmt->fetchAll();
    
    // Daily sales chart data
    $dailySalesStmt = $db->prepare("
        SELECT DATE(sale_date) as sale_day, 
               COUNT(*) as transactions,
               SUM(total_amount) as daily_total
        FROM sales
        WHERE DATE(sale_date) BETWEEN ? AND ?
        GROUP BY DATE(sale_date)
        ORDER BY sale_day ASC
    ");
    $dailySalesStmt->execute([$dateFrom, $dateTo]);
    $dailySales = $dailySalesStmt->fetchAll();
    
    // Payment methods breakdown
    $paymentStmt = $db->prepare("
        SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
        FROM sales
        WHERE DATE(sale_date) BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $paymentStmt->execute([$dateFrom, $dateTo]);
    $paymentMethods = $paymentStmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Reports error: " . $e->getMessage());
    $error = "Error loading reports.";
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-graph-up"></i> Reports & Analytics</h2>
        <hr>
    </div>
</div>

<!-- Date Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Apply Filter
                        </button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" onclick="window.print();" class="btn btn-secondary w-100">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sales Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Sales</h6>
                <h2><?php echo formatCurrency($salesSummary['total_sales']); ?></h2>
                <small><?php echo formatDate($dateFrom); ?> - <?php echo formatDate($dateTo); ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Transactions</h6>
                <h2><?php echo number_format($salesSummary['total_transactions']); ?></h2>
                <small>Total number of sales</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Average Transaction</h6>
                <h2><?php echo formatCurrency($salesSummary['avg_transaction']); ?></h2>
                <small>Per transaction</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Selling Items -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Selling Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topItems)): ?>
                                <tr><td colspan="4" class="text-center py-3">No sales data</td></tr>
                            <?php else: ?>
                                <?php foreach ($topItems as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo sanitize($item['item_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo $item['barcode']; ?></small>
                                        </td>
                                        <td class="text-end"><?php echo number_format($item['total_qty']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($item['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th class="text-end">Current</th>
                                <th class="text-end">Min Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lowStockItems)): ?>
                                <tr><td colspan="4" class="text-center py-3 text-success">All items have sufficient stock!</td></tr>
                            <?php else: ?>
                                <?php foreach ($lowStockItems as $item): ?>
                                    <tr class="<?php echo $item['stock_quantity'] == 0 ? 'out-of-stock-row' : 'low-stock-row'; ?>">
                                        <td>
                                            <strong><?php echo sanitize($item['item_name']); ?></strong>
                                        </td>
                                        <td><?php echo sanitize($item['category_name']); ?></td>
                                        <td class="text-end">
                                            <span class="badge <?php echo $item['stock_quantity'] == 0 ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                                <?php echo $item['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?php echo $item['min_stock_level']; ?></td>
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

<!-- Payment Methods Breakdown -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment Methods</h5>
            </div>
            <div class="card-body">
                <?php if (empty($paymentMethods)): ?>
                    <p class="text-center text-muted">No payment data</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <tbody>
                            <?php foreach ($paymentMethods as $method): ?>
                                <tr>
                                    <td><strong><?php echo ucfirst($method['payment_method']); ?></strong></td>
                                    <td class="text-end"><?php echo $method['count']; ?> transactions</td>
                                    <td class="text-end"><strong><?php echo formatCurrency($method['total']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Daily Sales Trend -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-calendar3"></i> Daily Sales</h5>
            </div>
            <div class="card-body">
                <?php if (empty($dailySales)): ?>
                    <p class="text-center text-muted">No sales data</p>
                <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm">
                            <tbody>
                                <?php foreach ($dailySales as $day): ?>
                                    <tr>
                                        <td><?php echo formatDate($day['sale_day']); ?></td>
                                        <td class="text-end"><?php echo $day['transactions']; ?> sales</td>
                                        <td class="text-end"><strong><?php echo formatCurrency($day['daily_total']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
