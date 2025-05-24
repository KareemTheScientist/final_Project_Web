<?php
// dashboard.php at root
require_once __DIR__ . '/config/init.php';
require_auth();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("Session contents: " . print_r($_SESSION, true));

// Initialize variables with default values
$user = null;
$recent_orders = [];
$total_orders = 0;
$error_message = '';

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }

try {
    // Verify database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Test connection
    $pdo->query("SELECT 1");
    error_log("Database connection successful");

    // Check if orders table exists and get its structure
    $tables_query = "SHOW TABLES LIKE 'orders'";
    $tables_result = $pdo->query($tables_query);
    $orders_table_exists = $tables_result->rowCount() > 0;
    error_log("Orders table exists: " . ($orders_table_exists ? 'Yes' : 'No'));

    if ($orders_table_exists) {
        $structure_query = "DESCRIBE orders";
        $structure_result = $pdo->query($structure_query);
        $table_structure = $structure_result->fetchAll(PDO::FETCH_ASSOC);
        error_log("Orders table structure: " . print_r($table_structure, true));
    }

    // Get current user data
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    error_log("User data: " . print_r($user, true));
    
    if (!$user) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Get total orders count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $count_stmt->execute([$_SESSION['user_id']]);
    $total_orders = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    error_log("Total orders for user: " . $total_orders);
    
    if ($total_orders > 0) {
        // Get recent orders
        $orders_query = "
            SELECT 
                o.id, 
                o.created_at as order_date, 
                o.total_amount,
                o.payment_method,
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
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC 
            LIMIT 5";
        
        error_log("Orders query: " . $orders_query);
        error_log("User ID for orders: " . $_SESSION['user_id']);
        
        $orders_stmt = $pdo->prepare($orders_query);
        $orders_stmt->execute([$_SESSION['user_id']]);
        $recent_orders = $orders_stmt->fetchAll();
        
        error_log("Recent orders: " . print_r($recent_orders, true));
    } else {
        error_log("No orders found for user");
    }
    
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    $error_message = "We're experiencing technical difficulties. Please refresh the page.";
}

$page_title = "Dashboard - " . htmlspecialchars($user['username'] ?? 'User');

include __DIR__ . '/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="dashboard-container">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-header">
        <div>
            <h1><i class="fas fa-leaf"></i> Welcome back, <?= htmlspecialchars($user['username']) ?>!</h1>
            <p class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
        </div>
        <a href="<?= url('/cart.php') ?>" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i> 
            View Cart 
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="badge"><?= array_sum($_SESSION['cart']) ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;">
                <i class="fas fa-clipboard-list" style="color: #1976d2;"></i>
            </div>
            <div>
                <h3>Total Orders</h3>
                <p class="stat-value"><?= $total_orders ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Recent Orders</h2>
            <?php if (!empty($recent_orders)): ?>
                <a href="<?= url('/orders.php') ?>" class="btn-link">View All Orders</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($recent_orders)): ?>
            <div class="orders-table">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($order['items']) ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="<?= url('/order_details.php?id=' . $order['id']) ?>" class="btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag fa-3x"></i>
                <h3>No orders yet</h3>
                <p>You haven't placed any orders. Start shopping to see them here!</p>
                <a href="<?= url('/plants.php') ?>" class="btn btn-primary">
                    <i class="fas fa-seedling"></i> Browse Plants
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 5%;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0.2rem 0 0;
    color: var(--dark);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.orders-table {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

th {
    font-weight: 600;
    color: var(--dark);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.empty-state i {
    color: var(--primary);
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    th, td {
        padding: 0.75rem;
    }
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    background: var(--primary);
    color: white;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-sm:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
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