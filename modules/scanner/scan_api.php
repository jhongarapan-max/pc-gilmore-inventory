<?php
/**
 * Scanner API - Search item by barcode
 */
require_once '../../init.php';
requirePermission('scanner');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$barcode = sanitize($input['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'Barcode is required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Search for item by barcode
    $stmt = $db->prepare("
        SELECT i.*, c.category_name 
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.category_id 
        WHERE i.barcode = ? AND i.is_active = 1
    ");
    $stmt->execute([$barcode]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Log the scan
        logAudit('scan', 'scanner', "Scanned item: {$item['item_name']}", $item['item_id']);
        
        echo json_encode([
            'success' => true,
            'item' => $item
        ]);
    } else {
        // Log failed scan
        logAudit('scan_failed', 'scanner', "Barcode not found: {$barcode}");
        
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Scanner API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error searching for item'
    ]);
}
