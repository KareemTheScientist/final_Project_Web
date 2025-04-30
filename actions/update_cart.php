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
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$plant_id || !$quantity) {
        throw new Exception('Invalid input data');
    }

    // Check if cart exists and has the item
    if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']['items'][$plant_id])) {
        throw new Exception('Item not found in cart');
    }

    // Get current plant stock from database
    $stmt = $pdo->prepare("SELECT stock FROM plants WHERE id = ?");
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch();

    if (!$plant) {
        throw new Exception('Plant not found');
    }

    // Validate quantity against stock
    if ($quantity > $plant['stock']) {
        throw new Exception('Not enough stock available');
    }

    // Update quantity
    $_SESSION['cart']['items'][$plant_id]['quantity'] = $quantity;

    // Update cart totals
    updateCartTotals();

    $response = [
        'success' => true,
        'message' => 'Cart updated',
        'cart_count' => $_SESSION['cart']['count'],
        'cart_total' => number_format($_SESSION['cart']['total'], 2),
        'item_total' => number_format($_SESSION['cart']['items'][$plant_id]['price'] * $quantity, 2)
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

function updateCartTotals() {
    $count = 0;
    $total = 0.00;
    
    foreach ($_SESSION['cart']['items'] as $item) {
        $count += $item['quantity'];
        $total += $item['price'] * $item['quantity'];
    }
    
    $_SESSION['cart']['count'] = $count;
    $_SESSION['cart']['total'] = $total;
}
?>