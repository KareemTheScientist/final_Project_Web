<?php
require_once '../config/init.php';

if (isset($_GET['plant_id'])) {
    $plant_id = (int)$_GET['plant_id'];
    
    if (isset($_SESSION['cart']['items'][$plant_id])) {
        unset($_SESSION['cart']['items'][$plant_id]);
        updateCartTotals();
        $_SESSION['success'] = "Item removed from cart";
    }
}

header("Location: ../pages/cart.php");
exit();
?>