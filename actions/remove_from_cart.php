<?php
require_once __DIR__ . '/../config/init.php';


// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to modify cart']);
    exit;
}

// Validate input
if (!isset($_POST['plant_id']) || !is_numeric($_POST['plant_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid plant ID']);
    exit;
}

$plantId = (int)$_POST['plant_id'];

try {
    // Check if item exists in cart
    if (!isset($_SESSION['cart'][$plantId])) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        exit;
    }

    // Remove item from cart
    unset($_SESSION['cart'][$plantId]);

    // Calculate total items in cart
    $cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => $cartCount
    ]);

} catch (Exception $e) {
    error_log("Cart Error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error removing item from cart']);
}