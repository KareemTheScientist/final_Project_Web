<?php
// Start session and include configuration
require_once '../config/init.php';
require_once '../db.php';

// Redirect to login if not authenticated
requireLogin('../pages/login.php');

// Initialize variables with default values
$user = null;
$recent_orders = [];
$order_stats = [
    'total_orders' => 0,
    'completed_orders' => 0,
    'pending_orders' => 0
];
$error_message = '';

try {
    // Get current user data
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User doesn't exist in database (session inconsistency)
        session_unset();
        session_destroy();
        header('Location: ../pages/login.php');
        exit();
    }
    
    // Get recent orders (5 most recent)
    $orders_stmt = $pdo->prepare("
        SELECT o.id, o.order_date, o.status, o.total_amount, 
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
    
    // Get order statistics
    $stats_stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders
        FROM orders 
        WHERE user_id = ?
    ");
    $stats_stmt->execute([$_SESSION['user_id']]);
    $order_stats = $stats_stmt->fetch() ?: $order_stats;
    
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "We're experiencing technical difficulties. Some data may not be available.";
}

// Set page title
$page_title = "Dashboard - " . htmlspecialchars($user['username'] ?? 'User');

// Include header
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card h3 {
            color: var(--gray);
            font-size: 1rem;
            margin-bottom: 15px;
        }
        
        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #c62828;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-table th, .order-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <main class="dashboard">
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div class="welcome-message">
                <h1><i class="fas fa-leaf"></i> Welcome back, <?= htmlspecialchars($user['username'] ?? 'User') ?>!</h1>
                <p>Here's your personalized gardening dashboard</p>
            </div>
            <a href="../pages/cart.php" class="btn" style="background: var(--primary); color: white; padding: 10px 15px; border-radius: 4px;">
                <i class="fas fa-shopping-cart"></i> View Cart (<?= $_SESSION['cart']['count'] ?? 0 ?>)
            </a>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><i class="fas fa-clipboard-list"></i> Total Orders</h3>
                <div class="value"><?= $order_stats['total_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-clock"></i> Pending Orders</h3>
                <div class="value"><?= $order_stats['pending_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-check-circle"></i> Completed</h3>
                <div class="value"><?= $order_stats['completed_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-calendar-alt"></i> Member Since</h3>
                <div class="value"><?= date('M Y', strtotime($user['created_at'])) ?></div>
            </div>
        </div>

        <div class="dashboard-section">
            <h2><i class="fas fa-clock"></i> Recent Orders</h2>
            
            <?php if (!empty($recent_orders)): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                            <td><?= $order['item_count'] ?></td>
                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span style="padding: 6px 12px; border-radius: 20px; background: <?= 
                                    $order['status'] === 'completed' ? '#E8F5E9' : 
                                    ($order['status'] === 'pending' ? '#FFF3E0' : '#E3F2FD') 
                                ?>; color: <?= 
                                    $order['status'] === 'completed' ? '#2E7D32' : 
                                    ($order['status'] === 'pending' ? '#E65100' : '#1565C0') 
                                ?>;">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't placed any orders yet. <a href="../pages/plants.php" style="color: var(--primary);">Start shopping!</a></p>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>