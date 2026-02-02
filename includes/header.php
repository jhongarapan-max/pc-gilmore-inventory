<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Core CSS Files -->
    <link href="<?php echo BASE_URL; ?>assets/css/base.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/layout.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/components.css" rel="stylesheet">
    
    <!-- Include Styles -->
    <link href="<?php echo BASE_URL; ?>includes/css/footer.css" rel="stylesheet">
    
    <!-- Module-Specific CSS -->
    <?php
    // Determine current module based on request URI
    $currentPage = basename($_SERVER['REQUEST_URI'], '.php');
    $currentModule = null;
    
    if (strpos($_SERVER['REQUEST_URI'], '/modules/') !== false) {
        $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $moduleIndex = array_search('modules', $pathParts);
        if ($moduleIndex !== false && isset($pathParts[$moduleIndex + 1])) {
            $currentModule = $pathParts[$moduleIndex + 1];
        }
    }
    
    // Load module-specific CSS (now located under assets/css)
    $modules = ['inventory', 'pos', 'reports', 'scanner', 'stock', 'suppliers', 'users'];
    if ($currentModule && in_array($currentModule, $modules)) {
        $moduleCss = BASE_URL . 'assets/css/' . $currentModule . '.css';
        echo '<link href="' . $moduleCss . '" rel="stylesheet">';
    }
    ?>
    <?php
    // current script/file (e.g., index.php, stock_in.php)
    $currentScript = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    ?>
    
    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="main-menu">
        <button class="closebtn" onclick="closeNav()">&times;</button>
        <a class="sidebar-brand" href="<?php echo BASE_URL; ?>index.php">
            <i class="bi bi-shop"></i> <?php echo APP_NAME; ?>
        </a>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentScript === 'index.php' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <?php if (hasPermission('inventory')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'inventory' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/inventory/index.php">
                    <i class="bi bi-box-seam"></i> Inventory
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('scanner')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'scanner' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/scanner/index.php">
                    <i class="bi bi-upc-scan"></i> Scanner
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('pos')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'pos' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/pos/index.php">
                    <i class="bi bi-cart3"></i> POS
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('stock')): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo ($currentModule === 'stock' ? 'active' : ''); ?>" href="#" id="stockDropdown" role="button" aria-expanded="<?php echo ($currentModule === 'stock' ? 'true' : 'false'); ?>">
                    <i class="bi bi-arrow-left-right"></i> Stock
                </a>
                <ul class="dropdown-menu <?php echo ($currentModule === 'stock' ? 'show' : ''); ?>">
                    <li><a class="dropdown-item <?php echo ($currentScript === 'stock_in.php' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/stock/stock_in.php">Stock In</a></li>
                    <li><a class="dropdown-item <?php echo ($currentScript === 'stock_out.php' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/stock/stock_out.php">Stock Out</a></li>
                    <li><a class="dropdown-item <?php echo ($currentScript === 'history.php' || $currentScript === 'stock_history.php' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/stock/history.php">History</a></li>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('suppliers')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'suppliers' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/suppliers/index.php">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('reports')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'reports' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/reports/index.php">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentModule === 'users' ? 'active' : ''); ?>" href="<?php echo BASE_URL; ?>modules/users/index.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" aria-expanded="false" id="userDropdown">
                    <i class="bi bi-person-circle"></i>
                    <strong><?php echo sanitize($_SESSION['user_name']); ?></strong>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Top Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark top-navbar-shadow">
            <div class="container-fluid">
                <button class="btn btn-dark" id="openNav" onclick="openNav()">
                    <i class="bi bi-list text-white"></i>
                </button>
                <div class="ms-auto">
                    <span class="navbar-text text-white">
                        Welcome, <?php echo sanitize($_SESSION['user_name']); ?>
                    </span>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="page-content">
            <div class="container-fluid mt-4">
    <?php else: ?>
    <!-- Non-logged in version (no sidebar) -->
    <div class="page-content">
        <div class="">
    <?php endif; ?>
