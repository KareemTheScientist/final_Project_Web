<?php
require_once __DIR__ . '/config/init.php';
require_auth();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("Session contents: " . print_r($_SESSION, true));

// Check if checkout info exists
if (!isset($_SESSION['checkout_info']) || empty($_SESSION['checkout_info'])) {
    error_log("No checkout info found in session");
    $_SESSION['error'] = "Please complete the checkout process first.";
    header('Location: checkout.php');
    exit;
}

$checkout_info = $_SESSION['checkout_info'];
error_log("Checkout info: " . print_r($checkout_info, true));

// Validate checkout info
if (!isset($checkout_info['shipping']) || !isset($checkout_info['cart_items'])) {
    error_log("Invalid checkout info structure");
    $_SESSION['error'] = "Invalid checkout information. Please try again.";
    header('Location: checkout.php');
    exit;
}

// Function to calculate cart subtotal
function calculateCartSubtotal($items) {
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            first_name, 
            last_name, 
            email, 
            address, 
            city, 
            country, 
            payment_method, 
            total_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total = calculateCartSubtotal($checkout_info['cart_items']);
    
    $stmt->execute([
        $_SESSION['user_id'],
        $checkout_info['shipping']['first_name'],
        $checkout_info['shipping']['last_name'],
        $checkout_info['shipping']['email'],
        $checkout_info['shipping']['address'],
        $checkout_info['shipping']['city'],
        $checkout_info['shipping']['country'],
        $checkout_info['shipping']['payment_method'],
        $total
    ]);

    $orderId = $pdo->lastInsertId();
    error_log("Order created with ID: " . $orderId);

    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, 
            item_type, 
            plant_id, 
            product_id, 
            quantity, 
            price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($checkout_info['cart_items'] as $item) {
        $plantId = $item['type'] === 'plant' ? $item['item_id'] : null;
        $productId = $item['type'] === 'product' ? $item['item_id'] : null;
        
        $stmt->execute([
            $orderId,
            $item['type'],
            $plantId,
            $productId,
            $item['quantity'],
            $item['price']
        ]);
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);
    error_log("Cart cleared for user: " . $_SESSION['user_id']);

    // Clear checkout session
    unset($_SESSION['checkout_info']);

    $pdo->commit();
    error_log("Order process completed successfully");

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error processing order: " . $e->getMessage());
    $_SESSION['error'] = "There was an error processing your order. Please try again.";
    header('Location: checkout.php');
    exit;
}

$page_title = "Order Confirmation | Nabta";
include __DIR__ . '/includes/navbar.php';
?>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Order Confirmed!</h1>
        <p class="order-number">Order #<?= $orderId ?></p>
        
        <div class="confirmation-details">
            <div class="detail-section">
                <h2>Order Summary</h2>
                <div class="order-items">
                    <?php foreach ($checkout_info['cart_items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <span class="item-type"><?= ucfirst($item['type']) ?></span>
                                <span class="item-quantity">Qty: <?= $item['quantity'] ?></span>
                            </div>
                            <div class="item-price">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <span>Total</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Shipping Information</h2>
                <div class="shipping-info">
                    <p>
                        <strong><?= htmlspecialchars($checkout_info['shipping']['first_name']) ?> 
                        <?= htmlspecialchars($checkout_info['shipping']['last_name']) ?></strong>
                    </p>
                    <p><?= htmlspecialchars($checkout_info['shipping']['address']) ?></p>
                    <p>
                        <?= htmlspecialchars($checkout_info['shipping']['city']) ?>, 
                        <?= htmlspecialchars($checkout_info['shipping']['country']) ?>
                    </p>
                    <p><?= htmlspecialchars($checkout_info['shipping']['email']) ?></p>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Payment Method</h2>
                <p class="payment-method">
                    <?= ucfirst(str_replace('_', ' ', $checkout_info['shipping']['payment_method'])) ?>
                </p>
            </div>
        </div>
        
        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #28a745;
    --primary-dark: #218838;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --border-radius: 0.375rem;
    --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.confirmation-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.confirmation-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
    text-align: center;
}

.success-icon {
    font-size: 4rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.confirmation-card h1 {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.order-number {
    color: var(--secondary);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.confirmation-details {
    text-align: left;
    margin: 2rem 0;
}

.detail-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-section h2 {
    font-size: 1.25rem;
    color: var(--dark);
    margin-bottom: 1rem;
}

.order-items {
    margin-bottom: 1.5rem;
}

.order-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.item-type {
    display: inline-block;
    font-size: 0.75rem;
    background: #e0e0e0;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    color: #424242;
    margin-right: 0.5rem;
}

.item-quantity {
    font-size: 0.875rem;
    color: var(--secondary);
}

.item-price {
    font-weight: 500;
}

.order-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary);
    margin-top: 1rem;
}

.shipping-info p {
    margin: 0.5rem 0;
    color: var(--dark);
}

.payment-method {
    font-size: 1.1rem;
    color: var(--primary);
    font-weight: 500;
}

.confirmation-actions {
    margin-top: 2rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    border: none;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

@media (max-width: 768px) {
    .confirmation-card {
        padding: 1.5rem;
    }
    
    .success-icon {
        font-size: 3rem;
    }
    
    .confirmation-card h1 {
        font-size: 1.75rem;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?> 