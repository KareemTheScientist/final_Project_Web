<?php
// order_details.php
require_once __DIR__ . '/config/init.php';
require_auth();

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = (int)$_GET['id'];
$order = null;
$order_items = [];
$error_message = '';

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            u.username,
            u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $error_message = "Order not found or you don't have permission to view it.";
    } else {
        // Get order items
        $items_stmt = $pdo->prepare("
            SELECT 
                oi.*,
                p.name as plant_name,
                p.image_url,
                p.short_description
            FROM order_items oi
            JOIN plants p ON oi.plant_id = p.id
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    error_log("Order Details Error: " . $e->getMessage());
    $error_message = "We're experiencing technical difficulties. Please try again later.";
}

$page_title = "Order Details #" . ($order['order_number'] ?? $order_id);
include __DIR__ . '/includes/navbar.php';
?>

<div class="order-details-container">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
        </div>
    <?php elseif ($order): ?>
        <div class="order-header">
            <div>
                <h1>
                    <i class="fas fa-receipt"></i> 
                    Order #<?= $order['order_number'] ? htmlspecialchars($order['order_number']) : 'ORD-' . $order['id'] ?>
                </h1>
                <p class="text-muted">
                    Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                </p>
            </div>
            <div class="order-status">
                <span class="status-badge status-<?= $order['status'] ?>">
                    <?= ucfirst($order['status']) ?>
                </span>
            </div>
        </div>

        <div class="order-content">
            <div class="order-section">
                <h2><i class="fas fa-box-open"></i> Order Summary</h2>
                <div class="order-items">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($item['plant_name']) ?>" 
                                             class="product-image">
                                        <div>
                                            <h4><?= htmlspecialchars($item['plant_name']) ?></h4>
                                            <p class="text-muted"><?= htmlspecialchars($item['short_description']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="order-columns">
                <div class="order-section">
                    <h2><i class="fas fa-user"></i> Customer Details</h2>
                    <div class="details-card">
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?= htmlspecialchars($order['username']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($order['email']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-section">
                    <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                    <div class="details-card">
                        <div class="detail-row">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">
                                <?= $order['payment_method'] === 'credit_card' ? 'Credit Card' : ucfirst($order['payment_method']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-actions">
            <a href="<?= url('/dashboard.php') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
.order-details-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 5%;
}

.order-status {
    margin-left: auto;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 1rem;
    font-weight: 500;
    text-transform: capitalize;
}

.status-pending {
    background: #fff3e0;
    color: #e65100;
}

.status-completed {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-processing {
    background: #e3f2fd;
    color: #1565c0;
}

.status-cancelled {
    background: #ffebee;
    color: #c62828;
}

.order-content {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.order-section {
    margin-bottom: 2.5rem;
}

.order-section h2 {
    margin-bottom: 1.5rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-section h2 i {
    color: var(--primary);
}

.order-items table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
}

.order-items th, 
.order-items td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.order-items th {
    font-weight: 600;
    background: #f9f9f9;
}

.order-items tfoot td {
    font-weight: bold;
    border-top: 2px solid #f0f0f0;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #f0f0f0;
}

.order-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.details-card {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
}

.detail-row {
    display: flex;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    min-width: 120px;
    color: var(--dark);
}

.detail-value {
    flex: 1;
}

.order-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

@media (max-width: 768px) {
    .order-columns {
        grid-template-columns: 1fr;
    }
    
    .order-items th, 
    .order-items td {
        padding: 0.75rem;
    }
    
    .product-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .product-image {
        width: 50px;
        height: 50px;
    }
}

@media print {
    .order-details-container {
        padding: 0;
    }
    
    .order-header, 
    .order-content {
        box-shadow: none;
        padding: 0;
    }
    
    .order-actions {
        display: none;
    }
    
    .order-section {
        page-break-inside: avoid;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>