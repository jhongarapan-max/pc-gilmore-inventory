<?php
/**
 * Test Checkout API
 * Simulate a checkout request to debug the issue
 */

require_once 'init.php';

echo "=== Testing POS Checkout API ===\n\n";

// Simulate cart data
$cart = [
    [
        'item_id' => 1,
        'item_name' => 'Test Item',
        'price' => 100.00,
        'quantity' => 1
    ]
];

$checkoutData = [
    'cart' => $cart,
    'customer_name' => 'Test Customer',
    'customer_contact' => '09123456789',
    'customer_address' => 'Test Address',
    'payment_method' => 'cash',
    'amount_received' => 100.00,
    'change' => 0.00
];

echo "Test data:\n";
echo json_encode($checkoutData, JSON_PRETTY_PRINT) . "\n\n";

// Simulate the API call
$_GET['action'] = 'checkout';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture the input
global $HTTP_RAW_POST_DATA;
$HTTP_RAW_POST_DATA = json_encode($checkoutData);

// Include the API file
ob_start();
include 'modules/pos/pos_api.php';
$output = ob_get_clean();

echo "API Response:\n";
echo $output . "\n";
