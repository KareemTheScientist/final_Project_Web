<?php
require_once __DIR__ . '/../config/init.php';
require_auth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['item_id']) || !isset($_POST['item_type']) || !isset($_POST['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$item_id = (int)$_POST['item_id'];
$item_type = $_POST['item_type'];
$quantity = (int)$_POST['quantity'];

if (!in_array($item_type, ['plant', 'product'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit;
}

try {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart = $stmt->fetch();

    if (!$cart) {
        throw new Exception('Cart not found');
    }

    // Check stock if it's a product
    if ($item_type === 'product') {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$item_id]);
        $stock = $stmt->fetchColumn();
        
        if ($quantity > $stock) {
            echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock']);
            exit;
        }
    }

    if ($quantity <= 0) {
        // Remove item from cart
        if ($item_type === 'plant') {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND plant_id = ? AND item_type = 'plant'");
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ? AND item_type = 'product'");
        }
        $stmt->execute([$cart['id'], $item_id]);
    } else {
        // Update quantity
        if ($item_type === 'plant') {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND plant_id = ? AND item_type = 'plant'");
        } else {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ? AND item_type = 'product'");
        }
        $stmt->execute([$quantity, $cart['id'], $item_id]);
    }

    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart['id']]);
    $cart_count = $stmt->fetchColumn() ?? 0;

    // Get updated cart items
    $stmt = $pdo->prepare("
        SELECT 
            ci.*,
            COALESCE(p.name, pr.name) as name,
            COALESCE(p.price, pr.price) as price,
            COALESCE(p.image_url, pr.image_url) as image_url
        FROM cart_items ci
        LEFT JOIN plants p ON ci.plant_id = p.id AND ci.item_type = 'plant'
        LEFT JOIN products pr ON ci.product_id = pr.id AND ci.item_type = 'product'
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart['id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
        'cart_count' => (int)$cart_count,
        'cart_items' => $cart_items
    ]);

} catch (Exception $e) {
    error_log("Update cart error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}