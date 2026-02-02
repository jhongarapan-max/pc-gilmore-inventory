    </div> <!-- End container-fluid -->
</div> <!-- End page-content -->
</div> <!-- End main-content -->
    
    <?php if (isLoggedIn()): ?>
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container-fluid text-center">
            <span class="text-muted"><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> &copy; <?php echo date('Y'); ?></span>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for easier DOM manipulation) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Core JS Files -->
    <script src="<?php echo BASE_URL; ?>assets/js/base.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/navigation.js"></script>
    
    <!-- Module-Specific JS -->
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
    
    // Load module-specific JS (now located under assets/js)
    $modules = ['inventory', 'pos', 'reports', 'scanner', 'stock', 'suppliers', 'users'];
    if ($currentModule && in_array($currentModule, $modules)) {
        $moduleJs = BASE_URL . 'assets/js/' . $currentModule . '.js';
        echo '<script src="' . $moduleJs . '"></script>';
    }
    ?>
    
    <?php if (isset($additionalJS)): ?>
        <?php echo $additionalJS; ?>
    <?php endif; ?>
</body>
</html>
