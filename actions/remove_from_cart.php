<?php
require_once __DIR__ . '/../config/init.php';
require_auth();

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate input
if (!isset($_POST['item_id']) || !isset($_POST['item_type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$item_id = (int)$_POST['item_id'];
$item_type = $_POST['item_type'];

if (!in_array($item_type, ['plant', 'product'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit;
}

try {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart) {
        throw new Exception('Cart not found');
    }

    // Delete the item from cart
    if ($item_type === 'plant') {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND plant_id = :item_id AND item_type = 'plant'");
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :item_id AND item_type = 'product'");
    }
    
    $stmt->execute([
        'cart_id' => $cart['id'],
        'item_id' => $item_id
    ]);

    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = :cart_id");
    $stmt->execute(['cart_id' => $cart['id']]);
    $cart_count = $stmt->fetchColumn() ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => (int)$cart_count
    ]);

} catch (Exception $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove item from cart: ' . $e->getMessage()
    ]);
}