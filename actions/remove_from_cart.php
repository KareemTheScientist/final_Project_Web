<?php
require_once '../config/init.php';
require_once '../db.php';

// Check if user is logged in (optional - remove if you want guest carts)
requireLogin('../pages/login.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $plant_id = filter_input(INPUT_POST, 'plant_id', FILTER_VALIDATE_INT);

    if (!$plant_id) {
        throw new Exception('Invalid plant ID');
    }

    // Check if cart exists and has the item
    if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']['items'][$plant_id])) {
        throw new Exception('Item not found in cart');
    }

    // Remove the item
    unset($_SESSION['cart']['items'][$plant_id]);

    // Update cart totals
    updateCartTotals();

    $response = [
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => $_SESSION['cart']['count'],
        'cart_total' => number_format($_SESSION['cart']['total'], 2)
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

function updateCartTotals() {
    $count = 0;
    $total = 0.00;
    
    if (isset($_SESSION['cart']['items'])) {
        foreach ($_SESSION['cart']['items'] as $item) {
            $count += $item['quantity'];
            $total += $item['price'] * $item['quantity'];
        }
    }
    
    $_SESSION['cart']['count'] = $count;
    $_SESSION['cart']['total'] = $total;
    
    // If cart is empty, reset it
    if ($count === 0) {
        $_SESSION['cart'] = [
            'items' => [],
            'count' => 0,
            'total' => 0.00
        ];
    }
}
?>