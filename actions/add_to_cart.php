<?php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plant_id'])) {
    $plant_id = (int)$_POST['plant_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if (addToCart($plant_id, $quantity)) {
        $_SESSION['success'] = "Item added to cart!";
    } else {
        $_SESSION['error'] = "Failed to add item to cart";
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit();
?>