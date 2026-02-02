<?php
/**
 * Delete Supplier
 */
require_once '../../init.php';
requirePermission('suppliers');

$supplier_id = intval($_GET['id'] ?? 0);

if (!$supplier_id) {
    redirect('modules/suppliers/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT supplier_name FROM suppliers WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();
    
    if ($supplier) {
        $deleteStmt = $db->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $deleteStmt->execute([$supplier_id]);
        
        logAudit('delete', 'suppliers', "Deleted supplier: {$supplier['supplier_name']}", $supplier_id);
        
        $_SESSION['success'] = 'Supplier deleted successfully!';
    } else {
        $_SESSION['error'] = 'Supplier not found.';
    }
} catch (Exception $e) {
    error_log("Delete supplier error: " . $e->getMessage());
    $_SESSION['error'] = 'Error deleting supplier.';
}

redirect('modules/suppliers/index.php');
