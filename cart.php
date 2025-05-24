<?php
require_once __DIR__ . '/config/init.php';
require_auth();

$page_title = "Shopping Cart";
include __DIR__ . '/includes/navbar.php';

// Initialize variables
$cart_items = [];
$total = 0;
$error = '';

try {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart) {
        // Create a new cart if one doesn't exist
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (:user_id)");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $cart = ['id' => $pdo->lastInsertId()];
    }
    
    if ($cart) {
        // Get cart items with plant and product details
        $stmt = $pdo->prepare("
            SELECT 
                ci.id,
                ci.quantity,
                ci.item_type,
                CASE 
                    WHEN ci.item_type = 'plant' THEN p.name
                    WHEN ci.item_type = 'product' THEN pr.name
                END as name,
                CASE 
                    WHEN ci.item_type = 'plant' THEN p.price
                    WHEN ci.item_type = 'product' THEN pr.price
                END as price,
                CASE 
                    WHEN ci.item_type = 'plant' THEN p.image_url
                    WHEN ci.item_type = 'product' THEN pr.image_url
                END as image_url,
                CASE 
                    WHEN ci.item_type = 'plant' THEN p.id
                    WHEN ci.item_type = 'product' THEN pr.id
                END as item_id,
                CASE 
                    WHEN ci.item_type = 'plant' THEN 10
                    WHEN ci.item_type = 'product' THEN pr.stock
                END as stock
            FROM cart_items ci
            LEFT JOIN plants p ON ci.plant_id = p.id AND ci.item_type = 'plant'
            LEFT JOIN products pr ON ci.product_id = pr.id AND ci.item_type = 'product'
            WHERE ci.cart_id = :cart_id
            ORDER BY ci.id ASC
        ");
        
        if (!$stmt->execute(['cart_id' => $cart['id']])) {
            throw new PDOException("Failed to execute cart items query");
        }
        
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals
        foreach ($cart_items as &$item) {
            $item['subtotal'] = $item['price'] * $item['quantity'];
            $total += $item['subtotal'];
        }
    }
} catch (PDOException $e) {
    error_log("Cart error: " . $e->getMessage());
    $error = "Could not load cart items. Please try again. Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <h1 class="cart-title mb-4">Shopping Cart</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (empty($cart_items)): ?>
                <div class="empty-cart-container">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h3 class="mb-4">Your cart is empty</h3>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?= url('/plants.php') ?>" class="btn btn-primary">
                                <i class="fas fa-leaf browse-icon"></i> Browse Plants
                            </a>
                            <a href="<?= url('/products.php') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-seedling browse-icon"></i> Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                    <tr data-item-id="<?= $item['id'] ?>" data-item-type="<?= $item['item_type'] ?>" data-stock="<?= $item['stock'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     class="cart-item-image">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                    <span class="badge bg-secondary"><?= $item['item_type'] === 'plant' ? 'Plant' : 'Product' ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <div class="quantity-controls">
                                                <button class="quantity-btn minus" type="button" title="Decrease quantity">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="quantity-input" 
                                                       value="<?= $item['quantity'] ?>" min="1" max="10">
                                                <button class="quantity-btn plus" type="button" title="Increase quantity">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="fw-bold">$<?= number_format($item['subtotal'], 2) ?></td>
                                        <td>
                                            <button class="btn btn-link text-danger remove-item" title="Remove item">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <h5 class="mb-3">Total: <span class="text-primary">$<?= number_format($total, 2) ?></span></h5>
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-outline-danger" id="removeAllBtn">
                                    <i class="fas fa-trash-alt"></i> Remove All Items
                                </button>
                                <a href="<?= url('/checkout.php') ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Cart Container Styling */
.container-fluid {
    padding: 2rem;
}

/* Card Styling */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 2rem;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Table Styling */
.table {
    margin-bottom: 0;
    width: 100%;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    padding: 1.5rem;
}

.table tbody tr {
    transition: background-color 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table tbody td {
    padding: 1.2rem;
    vertical-align: middle;
}

/* Product Image Styling */
.cart-item-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.cart-item-image:hover {
    transform: scale(1.05);
}

/* Product Name Styling */
.product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

/* Badge Styling */
.badge {
    padding: 0.5em 1em;
    font-weight: 500;
    border-radius: 6px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Quantity Controls Styling */
.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 10px;
    width: fit-content;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background-color: white;
    color: #2E7D32;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    cursor: pointer;
}

.quantity-btn:hover {
    background-color: #2E7D32;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 125, 50, 0.2);
}

.quantity-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(46, 125, 50, 0.2);
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.4rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
    background: white;
}

.quantity-input:focus {
    border-color: #2E7D32;
    box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.1);
    outline: none;
}

/* Price Styling */
.price, .subtotal {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.subtotal {
    font-size: 1.2rem;
}

/* Remove Item Button Styling */
.remove-item {
    color: #dc3545;
    transition: all 0.3s ease;
    padding: 0.8rem;
    border-radius: 8px;
    font-size: 1.1rem;
    background: none;
    border: none;
    cursor: pointer;
}

.remove-item:hover {
    background-color: #dc3545;
    color: white;
    transform: scale(1.1);
}

/* Remove All Button Styling */
#removeAllBtn {
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid #dc3545;
    background-color: transparent;
    color: #dc3545;
    font-size: 1.1rem;
    cursor: pointer;
    padding-left: 10%;
}

#removeAllBtn:hover {
    background-color: #dc3545;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

/* Checkout Button Styling */
.btn-primary {
    padding: 1.2rem 2.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: linear-gradient(45deg, #2E7D32, #43A047);
    border: none;
    box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
    font-size: 1.1rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
    background: linear-gradient(45deg, #43A047, #2E7D32);
}

/* Empty Cart Container Styling */
.empty-cart-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    margin-top: 2rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem;
    background: #f8f9fa;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 600px;
}

.empty-cart i {
    font-size: 5rem;
    color: #6c757d;
    margin-bottom: 2rem;
    opacity: 0.5;
}

.empty-cart h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.empty-cart .btn {
    padding: 0.8rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.empty-cart .btn-primary {
    background: linear-gradient(45deg, #2E7D32, #43A047);
    border: none;
    box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
}

.empty-cart .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
    background: linear-gradient(45deg, #43A047, #2E7D32);
}

.empty-cart .btn-outline-primary {
    border: 2px solid #2E7D32;
    color: #2E7D32;
}

.empty-cart .btn-outline-primary:hover {
    background-color: #2E7D32;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}

.empty-cart .browse-icon {
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

/* Loading Overlay Styling */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.loading-overlay i {
    color: #2E7D32;
    font-size: 3.5rem;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .cart-item-image {
        width: 90px;
        height: 90px;
    }
    
    .table thead {
        display: none;
    }
    
    .table tbody tr {
        display: block;
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
    }
    
    .table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border: none;
    }
    
    .table tbody td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #495057;
    }
    
    .quantity-controls {
        justify-content: flex-end;
    }
    
    .remove-item {
        margin-left: auto;
    }
    
    .btn-primary, #removeAllBtn {
        width: 100%;
        margin-bottom: 1rem;
    }
}

/* Button Container Styling */
.btn-container {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

#removeAllBtn {
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid #dc3545;
    background-color: transparent;
    color: #dc3545;
    font-size: 1.1rem;
    cursor: pointer;
    min-width: 200px;
}

#removeAllBtn:hover {
    background-color: #dc3545;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

.btn-primary {
    padding: 1.2rem 2.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: linear-gradient(45deg, #2E7D32, #43A047);
    border: none;
    box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
    font-size: 1.1rem;
    min-width: 200px;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
    background: linear-gradient(45deg, #43A047, #2E7D32);
}

/* Stock Warning Styling */
.stock-warning {
    color: #dc3545;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

/* Cart Title Styling */
.cart-title {
    text-align: center;
    padding-top: 2rem;
    margin-bottom: 3rem;
    color: #2c3e50;
    font-weight: 600;
    position: relative;
}

.cart-title:after {
    content: '';
    display: block;
    width: 60px;
    height: 3px;
    background: linear-gradient(45deg, #2E7D32, #43A047);
    margin: 1rem auto 0;
    border-radius: 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartTable = document.querySelector('table');
    if (!cartTable) return;

    // Quantity controls
    cartTable.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('tr');
            const input = row.querySelector('.quantity-input');
            let quantity = parseInt(input.value);
            const maxStock = parseInt(row.dataset.stock) || 10; // Use stock limit for products, default 10 for plants
            
            if (this.classList.contains('minus')) {
                quantity = Math.max(1, quantity - 1);
            } else {
                quantity = Math.min(maxStock, quantity + 1);
            }
            
            input.value = quantity;
            await updateCartItem(row.dataset.itemId, row.dataset.itemType, quantity);
        });
    });
    
    // Remove item
    cartTable.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('tr');
            if (confirm('Are you sure you want to remove this item?')) {
                const loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'loading-overlay';
                loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
                document.body.appendChild(loadingIndicator);

                try {
                    const response = await fetch('<?= url("/actions/remove_from_cart.php") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `item_id=${row.dataset.itemId}&item_type=${row.dataset.itemType}`
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to remove item');
                    }
                    
                    // Update global cart count
                    if (data.cart_count !== undefined) {
                        const event = new CustomEvent('cartUpdated', {
                            detail: { count: data.cart_count }
                        });
                        document.dispatchEvent(event);
                    }
                    
                    row.remove();
                    checkEmptyCart();
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Failed to remove item');
                } finally {
                    loadingIndicator.remove();
                }
            }
        });
    });
    
    // Remove all items
    document.getElementById('removeAllBtn').addEventListener('click', async function() {
        if (confirm('Are you sure you want to remove all items from your cart?')) {
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'loading-overlay';
            loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
            document.body.appendChild(loadingIndicator);

            try {
                const response = await fetch('<?= url("/actions/remove_all_cart.php") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to remove all items');
                }
                
                // Update global cart count
                const event = new CustomEvent('cartUpdated', {
                    detail: { count: 0 }
                });
                document.dispatchEvent(event);
                
                // Reload the page
                location.reload();
                
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to remove all items. Please try again.');
            } finally {
                loadingIndicator.remove();
            }
        }
    });
    
    // Quantity input changes
    cartTable.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async function() {
            const row = this.closest('tr');
            const maxStock = parseInt(row.dataset.stock) || 10;
            const quantity = Math.min(maxStock, Math.max(1, parseInt(this.value) || 1));
            this.value = quantity;
            
            await updateCartItem(row.dataset.itemId, row.dataset.itemType, quantity);
        });
    });
    
    // Update cart item function
    async function updateCartItem(itemId, itemType, quantity) {
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-overlay';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
        document.body.appendChild(loadingIndicator);
        
        try {
            const response = await fetch('<?= url("/actions/update_cart.php") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&item_type=${itemType}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to update cart');
            }
            
            // Update global cart count
            if (data.cart_count !== undefined) {
                const event = new CustomEvent('cartUpdated', {
                    detail: { count: data.cart_count }
                });
                document.dispatchEvent(event);
            }
            
            // Reload to update totals
            if (data.success) {
                location.reload();
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to update cart');
        } finally {
            loadingIndicator.remove();
        }
    }
    
    // Check if cart is empty
    function checkEmptyCart() {
        if (cartTable.querySelectorAll('tbody tr').length === 0) {
            location.reload();
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?> 