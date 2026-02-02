<?php
/**
 * POS API - Handle item lookup and checkout
 */
require_once '../../init.php';
requirePermission('pos');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($action === 'getItem') {
        // Get item by barcode
        $input = json_decode(file_get_contents('php://input'), true);
        $barcode = sanitize($input['barcode'] ?? '');
        
        if (empty($barcode)) {
            echo json_encode(['success' => false, 'message' => 'Barcode is required']);
            exit;
        }
        
        $stmt = $db->prepare("
            SELECT * FROM items 
            WHERE barcode = ? AND is_active = 1
        ");
        $stmt->execute([$barcode]);
        $item = $stmt->fetch();
        
        if ($item) {
            echo json_encode(['success' => true, 'item' => $item]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
        }
        
    } elseif ($action === 'checkout') {
        // Process checkout
        $input = json_decode(file_get_contents('php://input'), true);
        
        $cart = $input['cart'] ?? [];
        $customer_name = sanitize($input['customer_name'] ?? '');
        $customer_contact = sanitize($input['customer_contact'] ?? '');
        $customer_address = sanitize($input['customer_address'] ?? '');
        $payment_method = sanitize($input['payment_method'] ?? 'cash');
        $amount_received = floatval($input['amount_received'] ?? 0);
        $change = floatval($input['change'] ?? 0);
        
        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Generate sale number
            $sale_number = 'SALE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Calculate total
            $total_amount = 0;
            foreach ($cart as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            // Insert sale record
            $stmt = $db->prepare("
                INSERT INTO sales (sale_number, customer_name, customer_contact, customer_address,
                                  total_amount, payment_method, payment_received, change_amount, 
                                  cashier_id, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '')
            ");
            $stmt->execute([
                $sale_number,
                $customer_name,
                $customer_contact,
                $customer_address,
                $total_amount,
                $payment_method,
                $amount_received,
                $change,
                $_SESSION['user_id']
            ]);
            
            $sale_id = $db->lastInsertId();
            
            // Insert sale items and update stock
            $saleItemStmt = $db->prepare("
                INSERT INTO sale_items (sale_id, item_id, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $updateStockStmt = $db->prepare("
                UPDATE items SET stock_quantity = stock_quantity - ? WHERE item_id = ?
            ");
            
            $stockMovementStmt = $db->prepare("
                INSERT INTO stock_movements (item_id, movement_type, quantity, unit_cost, 
                                            reference_no, notes, user_id)
                VALUES (?, 'sale', ?, ?, ?, 'POS Sale', ?)
            ");
            
            foreach ($cart as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                
                // Check stock availability
                $checkStmt = $db->prepare("SELECT stock_quantity FROM items WHERE item_id = ?");
                $checkStmt->execute([$item['item_id']]);
                $currentStock = $checkStmt->fetch()['stock_quantity'];
                
                if ($currentStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for item: {$item['item_name']}");
                }
                
                // Insert sale item
                $saleItemStmt->execute([
                    $sale_id,
                    $item['item_id'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                ]);
                
                // Update stock
                $updateStockStmt->execute([
                    $item['quantity'],
                    $item['item_id']
                ]);
                
                // Log stock movement
                $stockMovementStmt->execute([
                    $item['item_id'],
                    $item['quantity'],
                    $item['price'],
                    $sale_number,
                    $_SESSION['user_id']
                ]);
            }
            
            // Log audit
            logAudit('sale', 'pos', "Completed sale: {$sale_number} - Total: {$total_amount}", $sale_id);
            
            // Commit transaction
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'sale_id' => $sale_id,
                'sale_number' => $sale_number,
                'message' => 'Sale completed successfully'
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("POS API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
