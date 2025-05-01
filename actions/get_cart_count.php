<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

try {
    $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    echo json_encode([
        'success' => true,
        'cart_count' => $cart_count
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not load cart count'
    ]);
}