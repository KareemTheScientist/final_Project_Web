<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['cart_count' => 0]);
    exit;
}

try {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart = $stmt->fetch();

    if ($cart) {
        // Get total quantity of items in cart
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart['id']]);
        $cart_count = $stmt->fetchColumn() ?? 0;
    } else {
        $cart_count = 0;
    }

    echo json_encode(['cart_count' => (int)$cart_count]);

} catch (PDOException $e) {
    error_log("Get cart count error: " . $e->getMessage());
    echo json_encode(['cart_count' => 0]);
}