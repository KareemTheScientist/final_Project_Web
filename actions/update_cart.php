<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['plant_id']) || !isset($_POST['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$plant_id = (int)$_POST['plant_id'];
$quantity = (int)$_POST['quantity'];

try {
    if ($quantity <= 0) {
        // Remove item from cart
        unset($_SESSION['cart'][$plant_id]);
    } else {
        // Update quantity
        $_SESSION['cart'][$plant_id] = $quantity;
    }
    
    // Calculate total items in cart
    $cart_count = array_sum($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
        'cart_count' => $cart_count,
        'cart_items' => $_SESSION['cart']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}