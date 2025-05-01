<?php
require_once __DIR__ . './config/init.php';

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
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
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
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

try {
    // Verify plant exists and is active
    $stmt = $pdo->prepare("SELECT id, name, price FROM plants WHERE id = ? AND active = 1");
    $stmt->execute([$plantId]);
    $plant = $stmt->fetch();

    if (!$plant) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Plant not found or unavailable']);
        exit;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update item in cart
    if (isset($_SESSION['cart'][$plantId])) {
        $_SESSION['cart'][$plantId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$plantId] = [
            'id' => $plantId,
            'name' => $plant['name'],
            'price' => $plant['price'],
            'quantity' => $quantity
        ];
    }

    // Calculate total items in cart
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart',
        'cart_count' => $cartCount,
        'plant_name' => $plant['name']
    ]);

} catch (PDOException $e) {
    error_log("Cart Error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}