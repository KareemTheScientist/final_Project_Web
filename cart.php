<?php
require_once './config/init.php';
require_once './includes/header.php';
?>

<div class="container">
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($_SESSION['cart']['items'])): ?>
        <p>Your cart is empty</p>
        <a href="plants.php" class="btn">Browse Plants</a>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($_SESSION['cart']['items'] as $item): ?>
            <div class="cart-item">
                <img src="../<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="item-info">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p>$<?= number_format($item['price'], 2) ?> each</p>
                    
                    <form action="../actions/update_cart.php" method="post" class="quantity-form">
                        <input type="hidden" name="plant_id" value="<?= $item['id'] ?>">
                        <label>Qty: 
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                        </label>
                        <button type="submit" class="btn-update">Update</button>
                    </form>
                    
                    <a href="../actions/remove_from_cart.php?plant_id=<?= $item['id'] ?>" class="btn-remove">Remove</a>
                </div>
                <div class="item-total">
                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-total">
            <h3>Total: $<?= number_format($_SESSION['cart']['total'], 2) ?></h3>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<style>
.cart-items {
    margin: 30px 0;
}
.cart-item {
    display: flex;
    gap: 20px;
    padding: 15px;
    border-bottom: 1px solid #eee;
    align-items: center;
}
.cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
}
.item-info {
    flex-grow: 1;
}
.quantity-form {
    display: inline-block;
    margin: 10px 0;
}
.quantity-form input {
    width: 50px;
    padding: 5px;
}
.btn-update {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
}
.btn-remove {
    color: #f44336;
    text-decoration: none;
    margin-left: 10px;
}
.item-total {
    font-weight: bold;
    min-width: 80px;
    text-align: right;
}
.cart-total {
    text-align: right;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #333;
}
.btn-checkout {
    background: #2e7d32;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
    margin-top: 10px;
}
</style>

<?php require_once './includes/footer.php'; ?>