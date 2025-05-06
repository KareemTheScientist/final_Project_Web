<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$required_fields = ['quantity'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "{$field} is required";
    }
}

if (!isset($_POST['plant_id']) && !isset($_POST['product_id'])) {
    $errors[] = "Either plant_id or product_id is required";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$quantity = (int)$_POST['quantity'];
$plant_id = isset($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$user_id = $_SESSION['user_id'];

try {
    // Check if item exists and is available
    if ($plant_id) {
        $stmt = $pdo->prepare("SELECT id, price FROM plants WHERE id = ? AND active = 1");
        $stmt->execute([$plant_id]);
        $item = $stmt->fetch();
        $item_type = 'plant';
    } else {
        $stmt = $pdo->prepare("SELECT id, price, stock FROM products WHERE id = ? AND stock > 0");
        $stmt->execute([$product_id]);
        $item = $stmt->fetch();
        $item_type = 'product';
        
        // Check stock availability
        if ($item && $quantity > $item['stock']) {
            echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock']);
            exit;
        }
    }

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found or unavailable']);
        exit;
    }

    // Get or create user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart['id'];
    }

    // Check if item already exists in cart
    if ($plant_id) {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND plant_id = ?");
        $stmt->execute([$cart_id, $plant_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
    }
    
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
    } else {
        // Add new item to cart
        if ($plant_id) {
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, plant_id, quantity, item_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cart_id, $plant_id, $quantity, $item_type]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, item_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cart_id, $product_id, $quantity, $item_type]);
        }
    }

    // Update product stock if it's a product
    if ($product_id) {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    }

    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    $cart_count = $stmt->fetchColumn() ?? 0;

    echo json_encode([
        'success' => true,
        'cart_count' => (int)$cart_count,
        'message' => 'Item added to cart successfully'
    ]);

} catch (PDOException $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}