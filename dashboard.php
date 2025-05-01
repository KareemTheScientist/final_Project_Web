<?php
// dashboard.php at root
require_once __DIR__ . '/config/init.php';
require_auth();

// Initialize variables with default values
$user = null;
$recent_orders = [];
$order_stats = [
    'total_orders' => 0,
    'completed_orders' => 0,
    'pending_orders' => 0
];
$error_message = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Get current user data
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Get recent orders (5 most recent) - Updated query to include order_number
    $orders_stmt = $pdo->prepare("
        SELECT o.id, o.order_number, o.order_date, o.status, o.total_amount, 
               COUNT(oi.id) as item_count 
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? 
        GROUP BY o.id
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $orders_stmt->execute([$_SESSION['user_id']]);
    $recent_orders = $orders_stmt->fetchAll();
    
    // Get order statistics - Added more status types
    $stats_stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders
        FROM orders 
        WHERE user_id = ?
    ");
    $stats_stmt->execute([$_SESSION['user_id']]);
    $order_stats = $stats_stmt->fetch() ?: $order_stats;
    
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "We're experiencing technical difficulties. Please refresh the page.";
}

$page_title = "Dashboard - " . htmlspecialchars($user['username'] ?? 'User');

include __DIR__ . '/includes/navbar.php';
?>

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
                <p class="stat-value"><?= $order_stats['total_orders'] ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff8e1;">
                <i class="fas fa-clock" style="color: #ff8f00;"></i>
            </div>
            <div>
                <h3>Pending</h3>
                <p class="stat-value"><?= $order_stats['pending_orders'] ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;">
                <i class="fas fa-check-circle" style="color: #2e7d32;"></i>
            </div>
            <div>
                <h3>Completed</h3>
                <p class="stat-value"><?= $order_stats['completed_orders'] ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e5f5;">
                <i class="fas fa-sync-alt" style="color: #8e24aa;"></i>
            </div>
            <div>
                <h3>Processing</h3>
                <p class="stat-value"><?= $order_stats['processing_orders'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Recent Orders</h2>
            <a href="<?= url('/orders.php') ?>" class="btn-link">View All Orders</a>
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td><?= $order['item_count'] ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
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
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    th, td {
        padding: 0.75rem;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>