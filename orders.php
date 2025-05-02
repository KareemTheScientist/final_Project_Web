<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/init.php';
require_auth();

// Verify database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

try {
    // Test connection
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Pagination and filtering
$orders_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $orders_per_page;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Initialize variables
$orders = [];
$total_orders = 0;
$total_pages = 1;

try {
    // Base query
    $query = "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, 
                     COUNT(oi.id) as item_count
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              WHERE o.user_id = :user_id";
    
    $params = [':user_id' => $_SESSION['user_id']];
    
    // Add status filter
    if ($status_filter !== 'all') {
        $query .= " AND o.status = :status";
        $params[':status'] = $status_filter;
    }
    
    $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
    
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
    
} catch (PDOException $e) {
    error_log("Orders Page Error: " . $e->getMessage());
    die("Database error occurred. Please check the error logs.");
}

// Rest of your HTML/PHP code...

$page_title = "My Orders";
include __DIR__ . '/includes/navbar.php';
?>

<div class="orders-container">
    <div class="orders-header">
        <h1><i class="fas fa-history"></i> My Orders</h1>
        <div class="orders-filter">
            <form method="get" class="status-filter-form">
                <label for="status">Filter by status:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </form>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag fa-3x"></i>
            <h3>No orders found</h3>
            <p>You haven't placed any orders matching your criteria.</p>
            <a href="<?= url('/plants.php') ?>" class="btn btn-primary">
                <i class="fas fa-seedling"></i> Browse Plants
            </a>
        </div>
    <?php else: ?>
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
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_number'] ? htmlspecialchars($order['order_number']) : 'ORD-' . $order['id'] ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
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

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?= url('/orders.php?page=' . ($current_page - 1) . ($status_filter !== 'all' ? '&status=' . $status_filter : '')) ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php 
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="' . url('/orders.php?page=1' . ($status_filter !== 'all' ? '&status=' . $status_filter : '')) . '" class="page-link">1</a>';
                        if ($start_page > 2) echo '<span class="page-dots">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i == $current_page ? 'active' : '';
                        echo '<a href="' . url('/orders.php?page=' . $i . ($status_filter !== 'all' ? '&status=' . $status_filter : '')) . '" class="page-link ' . $active . '">' . $i . '</a>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<span class="page-dots">...</span>';
                        echo '<a href="' . url('/orders.php?page=' . $total_pages . ($status_filter !== 'all' ? '&status=' . $status_filter : '')) . '" class="page-link">' . $total_pages . '</a>';
                    }
                    ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= url('/orders.php?page=' . ($current_page + 1) . ($status_filter !== 'all' ? '&status=' . $status_filter : '')) ?>" 
                       class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
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
    border-radius: 5px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
}

.orders-table {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
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
    background: #f9f9f9;
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

.status-cancelled {
    background: #ffebee;
    color: #c62828;
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
    border-radius: 5px;
    background: white;
    border: 1px solid #ddd;
    color: var(--dark);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

.btn-sm {
    padding: 0.4rem 0.8rem;
    border-radius: 5px;
    background: var(--primary-light);
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.btn-sm:hover {
    background: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .orders-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .status-filter-form {
        width: 100%;
    }
    
    th, td {
        padding: 0.75rem;
    }
    
    .pagination {
        flex-direction: column;
    }
    
    .page-numbers {
        order: -1;
        margin-bottom: 1rem;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>