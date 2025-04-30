<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Set to true in production with HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Database connection with PDO
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nabta;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'count' => 0,
        'total' => 0
    ];
}

// Cart functions
function updateCartTotals() {
    $count = 0;
    $total = 0;
    
    foreach ($_SESSION['cart']['items'] as $item) {
        $count += $item['quantity'];
        $total += $item['price'] * $item['quantity'];
    }
    
    $_SESSION['cart']['count'] = $count;
    $_SESSION['cart']['total'] = $total;
}

function addToCart($plant_id, $quantity = 1) {
    global $pdo;
    
    try {
        // Get plant data
        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM plants WHERE id = ?");
        $stmt->execute([$plant_id]);
        $plant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plant) return false;
        
        // Initialize cart items if needed
        if (!isset($_SESSION['cart']['items'])) {
            $_SESSION['cart']['items'] = [];
        }
        
        // Add or update item in cart
        if (isset($_SESSION['cart']['items'][$plant_id])) {
            $_SESSION['cart']['items'][$plant_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart']['items'][$plant_id] = [
                'id' => $plant['id'],
                'name' => $plant['name'],
                'price' => $plant['price'],
                'quantity' => $quantity,
                'image' => 'assets/images/' . basename($plant['image_url'])
            ];
        }
        
        updateCartTotals();
        return true;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
?>