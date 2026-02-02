<?php
/**
 * Dashboard / Home Page
 */
require_once 'init.php';
requireLogin();

$pageTitle = 'Dashboard';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get statistics
    $stats = [];
    
    // Total items
    $stmt = $db->query("SELECT COUNT(*) as count FROM items WHERE is_active = 1");
    $stats['total_items'] = $stmt->fetch()['count'];
    
    // Low stock items
    $stmt = $db->query("SELECT COUNT(*) as count FROM items WHERE stock_quantity <= min_stock_level AND is_active = 1");
    $stats['low_stock'] = $stmt->fetch()['count'];
    
    // Total stock value
    $stmt = $db->query("SELECT SUM(stock_quantity * cost_price) as value FROM items WHERE is_active = 1");
    $stats['stock_value'] = $stmt->fetch()['value'] ?? 0;
    
    // Today's sales
    $stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
                        FROM sales WHERE DATE(sale_date) = CURDATE()");
    $todaySales = $stmt->fetch();
    $stats['today_sales_count'] = $todaySales['count'];
    $stats['today_sales_total'] = $todaySales['total'];
    
    // Recent sales (last 10)
    $recentSales = [];
    if (hasPermission('reports') || hasPermission('pos')) {
        $stmt = $db->query("SELECT s.*, u.full_name as cashier_name 
                           FROM sales s 
                           JOIN users u ON s.cashier_id = u.user_id 
                           ORDER BY s.sale_date DESC LIMIT 10");
        $recentSales = $stmt->fetchAll();
    }
    
    // Low stock items
    $lowStockItems = [];
    if (hasPermission('inventory')) {
        $stmt = $db->query("SELECT i.*, c.category_name 
                           FROM items i 
                           LEFT JOIN categories c ON i.category_id = c.category_id 
                           WHERE i.stock_quantity <= i.min_stock_level AND i.is_active = 1 
                           ORDER BY i.stock_quantity ASC LIMIT 10");
        $lowStockItems = $stmt->fetchAll();
    }
    
    // Recent activities
    $recentActivities = [];
    $stmt = $db->prepare("SELECT al.*, u.full_name as user_name 
                         FROM audit_logs al 
                         LEFT JOIN users u ON al.user_id = u.user_id 
                         WHERE al.user_id = ? 
                         ORDER BY al.created_at DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $recentActivities = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Unable to load dashboard data.";
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo sanitize($_SESSION['user_name']); ?>!</p>
        <hr>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Total Items</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_items']); ?></h2>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Low Stock</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['low_stock']); ?></h2>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Stock Value</h6>
                        <h2 class="mb-0"><?php echo formatCurrency($stats['stock_value']); ?></h2>
                    </div>
                    <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Today's Sales</h6>
                        <h2 class="mb-0"><?php echo formatCurrency($stats['today_sales_total']); ?></h2>
                        <small><?php echo $stats['today_sales_count']; ?> transactions</small>
                    </div>
                    <i class="bi bi-cart3" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sales -->
    <?php if (!empty($recentSales)): ?>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Sales</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sale #</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td><?php echo sanitize($sale['sale_number']); ?></td>
                                <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                <td><?php echo sanitize($sale['cashier_name']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($sale['total_amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Low Stock Alert -->
    <?php if (!empty($lowStockItems)): ?>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Min Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><?php echo sanitize($item['item_name']); ?></td>
                                <td><?php echo sanitize($item['category_name']); ?></td>
                                <td class="text-end">
                                    <span class="badge bg-danger"><?php echo $item['stock_quantity']; ?></span>
                                </td>
                                <td class="text-end"><?php echo $item['min_stock_level']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Activities -->
<?php if (!empty($recentActivities)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Your Recent Activities</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo formatDateTime($activity['created_at']); ?></td>
                                <td><span class="badge bg-info"><?php echo sanitize($activity['action']); ?></span></td>
                                <td><?php echo sanitize($activity['module']); ?></td>
                                <td><?php echo sanitize($activity['details']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
