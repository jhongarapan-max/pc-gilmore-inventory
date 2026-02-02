<?php
/**
 * Print Receipt
 */
require_once '../../init.php';
requirePermission('pos');

$sale_id = intval($_GET['sale_id'] ?? 0);

if (!$sale_id) {
    die('Invalid sale ID');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get sale details
    $stmt = $db->prepare("
        SELECT s.*, u.full_name as cashier_name
        FROM sales s
        JOIN users u ON s.cashier_id = u.user_id
        WHERE s.sale_id = ?
    ");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();
    
    if (!$sale) {
        die('Sale not found');
    }
    
    // Get sale items
    $stmt = $db->prepare("
        SELECT si.*, i.item_name, i.barcode
        FROM sale_items si
        JOIN items i ON si.item_id = i.item_id
        WHERE si.sale_id = ?
    ");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error loading receipt');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $sale['sale_number']; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 20px auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
        }
        .info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        .items {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }
        .item {
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .totals {
            margin: 15px 0;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total-line.grand {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>PC GILMORE</h2>
        <p>Computer Retailer & Wholesaler</p>
        <p style="font-size: 10px;">Invoice Receipt</p>
    </div>
    
    <div class="info">
        <div><strong>Receipt #:</strong> <?php echo $sale['sale_number']; ?></div>
        <div><strong>Date & Time:</strong> <?php echo formatDateTime($sale['sale_date']); ?></div>
        <div><strong>Cashier:</strong> <?php echo $sale['cashier_name']; ?></div>
        
        <?php if ($sale['customer_name'] || $sale['customer_contact'] || $sale['customer_address']): ?>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #000;">
            <?php if ($sale['customer_name']): ?>
            <div><strong>Customer:</strong> <?php echo sanitize($sale['customer_name']); ?></div>
            <?php endif; ?>
            <?php if ($sale['customer_contact']): ?>
            <div><strong>Contact:</strong> <?php echo sanitize($sale['customer_contact']); ?></div>
            <?php endif; ?>
            <?php if ($sale['customer_address']): ?>
            <div><strong>Address:</strong> <?php echo sanitize($sale['customer_address']); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="items">
        <?php foreach ($items as $item): ?>
        <div class="item">
            <div class="item-name"><?php echo sanitize($item['item_name']); ?></div>
            <div class="item-details">
                <span><?php echo $item['quantity']; ?> x <?php echo formatCurrency($item['unit_price']); ?></span>
                <span><?php echo formatCurrency($item['subtotal']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="totals">
        <div class="total-line grand">
            <span>TOTAL:</span>
            <span><?php echo formatCurrency($sale['total_amount']); ?></span>
        </div>
        <div class="total-line">
            <span>Payment (<?php echo ucfirst($sale['payment_method']); ?>):</span>
            <span><?php echo formatCurrency($sale['payment_received']); ?></span>
        </div>
        <div class="total-line">
            <span>Change:</span>
            <span><?php echo formatCurrency($sale['change_amount']); ?></span>
        </div>
    </div>
    
    <div class="footer">
        <p>Thank you for your business!</p>
        <p style="margin-top: 10px;">** NO RETURN, NO EXCHANGE **</p>
        <p style="margin-top: 10px;">Powered by PC Gilmore Inventory System</p>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">Print Receipt</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px;">Close</button>
    </div>
    
    <script>
        // Auto print on load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
