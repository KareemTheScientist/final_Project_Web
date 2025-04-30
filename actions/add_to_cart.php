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
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

    if (!$plant_id) {
        throw new Exception('Invalid plant ID');
    }

    // Get plant details from database
    $stmt = $pdo->prepare("SELECT id, name, price, image_url, stock FROM plants WHERE id = ? AND active = 1");
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch();

    if (!$plant) {
        throw new Exception('Plant not found or unavailable');
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
            'count' => 0,
            'total' => 0.00
        ];
    }

    // Check if item already exists in cart
    if (isset($_SESSION['cart']['items'][$plant_id])) {
        // Update quantity if enough stock available
        $new_quantity = $_SESSION['cart']['items'][$plant_id]['quantity'] + $quantity;
        if ($new_quantity > $plant['stock']) {
            throw new Exception('Not enough stock available');
        }
        $_SESSION['cart']['items'][$plant_id]['quantity'] = $new_quantity;
    } else {
        // Add new item to cart
        if ($quantity > $plant['stock']) {
            throw new Exception('Not enough stock available');
        }
        $_SESSION['cart']['items'][$plant_id] = [
            'id' => $plant['id'],
            'name' => $plant['name'],
            'price' => $plant['price'],
            'quantity' => $quantity,
            'image' => 'assets/images/' . basename($plant['image_url']),
            'max_stock' => $plant['stock']
        ];
    }

    // Update cart totals
    updateCartTotals();

    $response = [
        'success' => true,
        'message' => 'Item added to cart',
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
    
    foreach ($_SESSION['cart']['items'] as $item) {
        $count += $item['quantity'];
        $total += $item['price'] * $item['quantity'];
    }
    
    $_SESSION['cart']['count'] = $count;
    $_SESSION['cart']['total'] = $total;
}
?>