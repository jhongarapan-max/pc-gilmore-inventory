<?php
/**
 * Delete Item
 */
require_once '../../init.php';
requirePermission('inventory');

$item_id = intval($_GET['id'] ?? 0);

if (!$item_id) {
    redirect('modules/inventory/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get item details before deleting
    $stmt = $db->prepare("SELECT item_name FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Delete item (cascade will handle related records)
        $deleteStmt = $db->prepare("DELETE FROM items WHERE item_id = ?");
        $deleteStmt->execute([$item_id]);
        
        // Log audit
        logAudit('delete', 'inventory', "Deleted item: {$item['item_name']}", $item_id);
        
        $_SESSION['success'] = 'Item deleted successfully!';
    } else {
        $_SESSION['error'] = 'Item not found.';
    }
    
} catch (Exception $e) {
    error_log("Delete item error: " . $e->getMessage());
    $_SESSION['error'] = 'Error deleting item. It may be referenced in sales or purchase orders.';
}

redirect('modules/inventory/index.php');
