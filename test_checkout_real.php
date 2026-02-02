<?php
/**
 * Test real checkout process
 * Simulate the exact same data that the frontend sends
 */

require_once 'init.php';

// Simulate being logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Admin user
    $_SESSION['user_name'] = 'System Administrator';
    $_SESSION['user_role'] = 'admin';
}

echo "=== Testing Real Checkout Process ===\n\n";

// Simulate the cart data that would be sent from frontend
$cart = [
    [
        'item_id' => 1,
        'item_name' => 'Intel Core i5-12400F Processor',
        'price' => 9500.00,
        'quantity' => 1
    ]
];

$checkoutData = [
    'cart' => $cart,
    'customer_name' => 'Test Customer',
    'customer_contact' => '09123456789',
    'customer_address' => 'Test Address',
    'payment_method' => 'cash',
    'amount_received' => 9500.00,
    'change' => 0.00
];

echo "Test data:\n";
echo json_encode($checkoutData, JSON_PRETTY_PRINT) . "\n\n";

// Simulate the API call
$_GET['action'] = 'checkout';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture the input
$HTTP_RAW_POST_DATA = json_encode($checkoutData);

// Include the API file and capture output
ob_start();
include 'modules/pos/pos_api.php';
$output = ob_get_clean();

echo "API Response:\n";
echo $output . "\n\n";

// Parse the response
$response = json_decode($output, true);
if ($response) {
    if ($response['success']) {
        echo "✅ Checkout successful!\n";
        echo "Sale ID: " . $response['sale_id'] . "\n";
        echo "Sale Number: " . $response['sale_number'] . "\n";
    } else {
        echo "❌ Checkout failed: " . ($response['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Invalid JSON response\n";
}
