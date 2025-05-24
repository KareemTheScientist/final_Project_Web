<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/init.php';
require_auth();

// Debug log
error_log("Session contents: " . print_r($_SESSION, true));

// Verify database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

try {
    // Test connection
    $pdo->query("SELECT 1");
    error_log("Database connection successful");

    // Pagination
    $orders_per_page = 10;
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($current_page - 1) * $orders_per_page;

    // Initialize variables
    $orders = [];
    $total_orders = 0;
    $total_pages = 1;
    $error_message = '';

    // Base query for orders
    $query = "SELECT o.id, o.created_at, o.total_amount, o.payment_method,
                     COUNT(oi.id) as item_count,
                     GROUP_CONCAT(
                         CASE 
                             WHEN oi.item_type = 'plant' THEN CONCAT(p.name, ' (', oi.quantity, ')')
                             WHEN oi.item_type = 'product' THEN CONCAT(pr.name, ' (', oi.quantity, ')')
                         END
                         SEPARATOR ', '
                     ) as items
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN plants p ON oi.plant_id = p.id AND oi.item_type = 'plant'
              LEFT JOIN products pr ON oi.product_id = pr.id AND oi.item_type = 'product'
              WHERE o.user_id = :user_id
              GROUP BY o.id 
              ORDER BY o.created_at DESC";
    
    $params = [':user_id' => $_SESSION['user_id']];
    
    // Debug log the query
    error_log("Orders query: " . $query);
    error_log("Query parameters: " . print_r($params, true));
    
    // Count query for pagination
    $count_query = "SELECT COUNT(*) as total FROM ($query) as subquery";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $result = $count_stmt->fetch();
    
    if ($result) {
        $total_orders = $result['total'];
        $total_pages = ceil($total_orders / $orders_per_page);
    }
    
    // Main query with pagination
    $query .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $orders_per_page;
    $params[':offset'] = $offset;
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters with proper types
    foreach ($params as $key => $value) {
        $param_type = PDO::PARAM_STR;
        if (is_int($value)) $param_type = PDO::PARAM_INT;
        $stmt->bindValue($key, $value, $param_type);
    }
    
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug log the orders
    error_log("Fetched orders: " . print_r($orders, true));
    
} catch (Exception $e) {
    error_log("Orders Page Error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    $error_message = "An error occurred while fetching your orders. Please try again later.";
}

// Rest of your HTML/PHP code...

$page_title = "My Orders";
include __DIR__ . '/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="orders-container">
    <div class="orders-header">
        <h1><i class="fas fa-history"></i> My Orders</h1>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag fa-3x"></i>
            <h3>No orders found</h3>
            <p>You haven't placed any orders yet.</p>
            <a href="<?= url('/plants.php') ?>" class="btn btn-primary">
                <i class="fas fa-seedling"></i> Browse Plants
            </a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?= $order['id'] ?></h3>
                            <span class="order-date">
                                <i class="far fa-calendar-alt"></i>
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-items">
                            <h4>Items (<?= $order['item_count'] ?>)</h4>
                            <p class="items-list"><?= htmlspecialchars($order['items']) ?></p>
                        </div>
                        
                        <div class="order-summary">
                            <div class="summary-item">
                                <span>Total Amount:</span>
                                <span class="amount">$<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Payment Method:</span>
                                <span class="payment-method">
                                    <?php
                                    switch($order['payment_method']) {
                                        case 'credit_card':
                                            echo '<i class="fas fa-credit-card"></i> Credit Card';
                                            break;
                                        case 'paypal':
                                            echo '<i class="fab fa-paypal"></i> PayPal';
                                            break;
                                        case 'cash_on_delivery':
                                            echo '<i class="fas fa-money-bill-wave"></i> Cash on Delivery';
                                            break;
                                        default:
                                            echo ucfirst($order['payment_method']);
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="<?= url('/order_details.php?id=' . $order['id']) ?>" class="btn btn-outline">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?= url('/orders.php?page=' . ($current_page - 1)) ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php 
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="' . url('/orders.php?page=1') . '" class="page-link">1</a>';
                        if ($start_page > 2) echo '<span class="page-dots">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i == $current_page ? 'active' : '';
                        echo '<a href="' . url('/orders.php?page=' . $i) . '" class="page-link ' . $active . '">' . $i . '</a>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<span class="page-dots">...</span>';
                        echo '<a href="' . url('/orders.php?page=' . $total_pages) . '" class="page-link">' . $total_pages . '</a>';
                    }
                    ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= url('/orders.php?page=' . ($current_page + 1)) ?>" 
                       class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
:root {
    --primary: #28a745;
    --primary-dark: #218838;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --danger: #dc3545;
    --border-radius: 0.375rem;
    --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.orders-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 5%;
}

.orders-header h1 {
    color: var(--dark);
    font-size: 1.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.orders-header h1 i {
    color: var(--primary);
}

.orders-filter {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-filter-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-filter-form label {
    font-weight: 500;
    color: var(--dark);
}

.status-filter-form select {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    font-size: 0.9rem;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.order-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-info h3 {
    margin: 0;
    color: var(--dark);
    font-size: 1.25rem;
}

.order-date {
    color: var(--secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
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

.order-details {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.order-items h4 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
}

.items-list {
    color: var(--secondary);
    font-size: 0.9rem;
    line-height: 1.5;
}

.order-summary {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: var(--border-radius);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.amount {
    font-weight: 600;
    color: var(--primary);
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--secondary);
}

.order-actions {
    padding: 1.5rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-outline {
    border: 1px solid var(--primary);
    color: var(--primary);
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.empty-state i {
    color: var(--primary);
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 1rem 0;
    color: var(--dark);
}

.empty-state p {
    color: var(--secondary);
    margin-bottom: 1.5rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.page-link {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    background: white;
    border: 1px solid #ddd;
    color: var(--dark);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.page-link:hover {
    background: #f5f5f5;
}

.page-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.page-numbers {
    display: flex;
    gap: 0.5rem;
}

.page-dots {
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .orders-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .status-filter-form {
        width: 100%;
    }
    
    .order-details {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .pagination {
        flex-direction: column;
    }
    
    .page-numbers {
        order: -1;
        margin-bottom: 1rem;
    }
}

.alert {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert i {
    font-size: 1.25rem;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>