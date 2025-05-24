<?php
require_once __DIR__ . '/../config/init.php';
require_auth();

header('Content-Type: application/json');

try {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart) {
        // Delete all items from the cart
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $stmt->execute(['cart_id' => $cart['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'All items removed from cart',
            'cart_count' => 0
        ]);
    } else {
        throw new Exception('Cart not found');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove items from cart: ' . $e->getMessage()
    ]);
} 