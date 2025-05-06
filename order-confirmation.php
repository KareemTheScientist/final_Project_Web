<?php
require_once __DIR__ . '/config/init.php';
require_auth();

// Redirect if checkout info is not set
// if (empty($_SESSION['checkout_info']) || empty($_SESSION['checkout_info']['cart_items'])) {
//     header('Location: cart.php');
//     exit;
// }

$page_title = "Order Confirmation | Nabta";
include __DIR__ . '/includes/navbar.php';

// Get checkout info from session
$checkout_info = $_SESSION['checkout_info'];
$shipping = $checkout_info['shipping'];
$cart_items = $checkout_info['cart_items'];

// Calculate order totals
$subtotal = calculateCartSubtotal($cart_items);
$shipping_cost = 5.99;
$tax_rate = 0.08;
$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping_cost + $tax;
$order_number = 'NAB-' . strtoupper(uniqid());

// Process order (save to database)
try {
    $pdo->beginTransaction();

    // Save order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method, status)
        VALUES (?, ?, ?, ?, ?, 'processing')
    ");

    $shipping_address = implode(', ', [
        $shipping['address'],
        $shipping['address2'],
        $shipping['city'],
        $shipping['state'],
        $shipping['zip'],
        $shipping['country']
    ]);

    $stmt->execute([
        $_SESSION['user_id'],
        $order_number,
        $total,
        $shipping_address,
        $checkout_info['payment_method']
    ]);

    $order_id = $pdo->lastInsertId();

    // Save order items (both plants and products)
    $plantStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, plant_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    
    $productStmt = $pdo->prepare("
        INSERT INTO order_products (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        if ($item['type'] === 'plant') {
            $plantStmt->execute([
                $order_id,
                $item['item_id'],
                $item['quantity'],
                $item['price']
            ]);
        } elseif ($item['type'] === 'product') {
            $productStmt->execute([
                $order_id,
                $item['item_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
    }

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);

    $pdo->commit();
    
    // Clear session checkout data
    unset($_SESSION['checkout_info']);
    
    // Send confirmation email (would implement this function)
    // sendOrderConfirmationEmail($_SESSION['user_id'], $order_id);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Order processing error: " . $e->getMessage());
    $_SESSION['error'] = "There was an error processing your order. Please try again.";
    header('Location: checkout.php');
    exit;
}
?>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. A confirmation has been sent to your email.</p>
        </div>

        <div class="confirmation-section">
            <div class="info-block">
                <h3>Order Info</h3>
                <p><strong>Order Number:</strong> <?= htmlspecialchars($order_number) ?></p>
                <p><strong>Date:</strong> <?= date('F j, Y') ?></p>
                <p><strong>Payment:</strong> <?= ucfirst(str_replace('_', ' ', $checkout_info['payment_method'])) ?>
                    <?php if ($checkout_info['payment_method'] === 'credit_card'): ?>
                        (****4242)
                    <?php endif; ?>
                </p>
                <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
            </div>
            <div class="info-block">
                <h3>Shipping Info</h3>
                <address>
                    <?= htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name']) ?><br>
                    <?= htmlspecialchars($shipping['address']) ?><br>
                    <?php if (!empty($shipping['address2'])): ?>
                        <?= htmlspecialchars($shipping['address2']) ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($shipping['city'] . ', ' . $shipping['state'] . ' ' . $shipping['zip']) ?><br>
                    <?= htmlspecialchars($shipping['country']) ?>
                </address>
            </div>
        </div>

        <div class="order-items">
            <h3>Your Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Item Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>" width="50">
                                <div>
                                    <?= htmlspecialchars($item['name']) ?>
                                    <span class="item-type"><?= ucfirst($item['type']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <p><strong>Subtotal:</strong> $<?= number_format($subtotal, 2) ?></p>
            <p><strong>Shipping:</strong> $<?= number_format($shipping_cost, 2) ?></p>
            <p><strong>Tax:</strong> $<?= number_format($tax, 2) ?></p>
            <p class="grand-total"><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
        </div>

        <div class="confirmation-actions">
            <a href="plants.php" class="btn btn-outline">
                <i class="fas fa-leaf"></i> Shop More Plants
            </a>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-seedling"></i> Browse Products
            </a>
            <a href="dashboard.php?order=<?= $order_id ?>" class="btn btn-primary">
                <i class="fas fa-user-circle"></i> View My Order
            </a>
        </div>
    </div>
</div>

<!-- CSS remains the same as before -->
<!-- ... -->

<?php include __DIR__ . '/includes/footer.php'; ?>

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
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.confirmation-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 2rem;
}

.confirmation-header i {
    font-size: 3.5rem;
    color: var(--primary);
}

.confirmation-header h1 {
    margin: 0.5rem 0;
    color: var(--primary);
    font-size: 2rem;
}

.confirmation-header p {
    font-size: 1.1rem;
    color: var(--secondary);
}

.confirmation-section {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.info-block {
    flex: 1;
    min-width: 250px;
}

.info-block h3 {
    margin-bottom: 1rem;
    color: var(--dark);
}

.info-block p, .info-block address {
    margin: 0.5rem 0;
    color: var(--secondary);
}

.order-items {
    margin-bottom: 2rem;
}

.order-items h3 {
    margin-bottom: 1rem;
    color: var(--dark);
}

.order-items table {
    width: 100%;
    border-collapse: collapse;
}

.order-items th, .order-items td {
    padding: 0.75rem;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.order-items th {
    font-weight: 500;
    color: var(--dark);
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-info img {
    border-radius: 4px;
}

.item-type {
    display: inline-block;
    font-size: 0.75rem;
    background: #e0e0e0;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    color: #424242;
    margin-top: 0.25rem;
}

.totals {
    text-align: right;
    margin-bottom: 2rem;
}

.totals p {
    margin: 0.5rem 0;
}

.grand-total {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary);
}

.confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-outline {
    border: 1px solid var(--primary);
    color: var(--primary);
    background: transparent;
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

@media (max-width: 768px) {
    .confirmation-section {
        flex-direction: column;
    }
    
    .confirmation-actions {
        flex-direction: column;
    }
    
    .confirmation-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .product-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .product-info img {
        width: 40px;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>