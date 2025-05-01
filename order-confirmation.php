<?php
require_once __DIR__ . './config/init.php';
require_auth();

// Redirect if checkout info is not set
if (empty($_SESSION['checkout_info']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$page_title = "Order Confirmation";
include __DIR__ . '/includes/navbar.php';

// Get checkout info from session
$checkout_info = $_SESSION['checkout_info'];
$shipping = $checkout_info['shipping'];

// Calculate order totals
$cart_items = [];
$subtotal = 0;
$shipping_cost = 5.99;
$tax_rate = 0.08;

$plant_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($plant_ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM plants WHERE id IN ($placeholders)");
$stmt->execute($plant_ids);
$plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($plants as $plant) {
    $quantity = $_SESSION['cart'][$plant['id']];
    $item_total = $plant['price'] * $quantity;
    $subtotal += $item_total;

    $cart_items[] = [
        'plant' => $plant,
        'quantity' => $quantity,
        'item_total' => $item_total
    ];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping_cost + $tax;
$order_number = 'NAB-' . strtoupper(uniqid());

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status)
        VALUES (?, ?, ?, ?, 'completed')
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
        $total,
        $shipping_address,
        $checkout_info['payment_method']
    ]);

    $order_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, plant_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['plant']['id'],
            $item['quantity'],
            $item['plant']['price']
        ]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_info']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Order error: " . $e->getMessage());
}
?>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase</p>
        </div>

        <div class="confirmation-section">
            <div class="info-block">
                <h3>Order Info</h3>
                <p><strong>Order Number:</strong> <?= $order_number ?></p>
                <p><strong>Date:</strong> <?= date('F j, Y') ?></p>
                <p><strong>Payment:</strong> <?= ucfirst(str_replace('_', ' ', $checkout_info['payment_method'])) ?><?php if ($checkout_info['payment_method'] === 'credit_card'): ?> (****4242)<?php endif; ?></p>
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
                    <tr><th>Product</th><th>Qty</th><th>Item Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($item['plant']['image_url']) ?>" alt="<?= htmlspecialchars($item['plant']['name']) ?>" width="50">
                                <?= htmlspecialchars($item['plant']['name']) ?>
                            </div>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['item_total'], 2) ?></td>
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
            <a href="plants.php" class="btn btn-outline"><i class="fas fa-seedling"></i> Shop More Plants</a>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-user-circle"></i> View My Orders</a>
        </div>
    </div>
</div>

<style>
.confirmation-container { max-width: 1000px; margin: 2rem auto; padding: 1rem; }
.confirmation-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 2rem; }
.confirmation-header { text-align: center; margin-bottom: 2rem; }
.confirmation-header i { font-size: 3.5rem; color: #28a745; }
.confirmation-header h1 { margin: 0.5rem 0; color: #28a745; font-size: 2rem; }
.confirmation-header p { font-size: 1.1rem; color: #555; }
.confirmation-section { display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
.info-block { flex: 1; min-width: 250px; }
.info-block h3 { margin-bottom: 0.5rem; color: #333; }
.info-block p, .info-block address { margin: 0.4rem 0; color: #444; }
.order-items { margin-bottom: 2rem; }
.order-items table { width: 100%; border-collapse: collapse; }
.order-items th, .order-items td { padding: 0.75rem; border-bottom: 1px solid #eee; }
.product-info { display: flex; align-items: center; gap: 0.75rem; }
.product-info img { border-radius: 4px; }
.totals { text-align: right; margin-bottom: 2rem; }
.totals p { margin: 0.3rem 0; }
.grand-total { font-size: 1.2rem; font-weight: bold; color: #333; }
.confirmation-actions { display: flex; justify-content: center; gap: 1rem; margin-top: 2rem; }
.btn { padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 500; }
.btn-outline { border: 1px solid #28a745; color: #28a745; background: #fff; }
.btn-outline:hover { background: #e8f5e9; }
.btn-primary { background: #28a745; color: white; }
.btn-primary:hover { background: #218838; }
@media (max-width: 768px) { .confirmation-section { flex-direction: column; } .confirmation-actions { flex-direction: column; } .confirmation-actions .btn { width: 100%; text-align: center; } }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
