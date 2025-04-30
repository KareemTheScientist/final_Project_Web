<?php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plant_id'], $_POST['quantity'])) {
    $plant_id = (int)$_POST['plant_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    
    if (isset($_SESSION['cart']['items'][$plant_id])) {
        $_SESSION['cart']['items'][$plant_id]['quantity'] = $quantity;
        updateCartTotals();
        $_SESSION['success'] = "Cart updated!";
    }
}

header("Location: ../pages/cart.php");
exit();
?>