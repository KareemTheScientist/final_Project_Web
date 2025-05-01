<?php
require_once __DIR__ . '/config/init.php';
require_auth();

$page_title = "Your Shopping Cart";
include __DIR__ . '/includes/navbar.php';

// Initialize variables
$cart_items = [];
$total = 0;
$error = '';

// Check if cart exists and has items
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $plant_ids = array_keys($_SESSION['cart']);
    
    try {
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($plant_ids), '?'));
        
        // Get plant details for items in cart
        $stmt = $pdo->prepare("SELECT * FROM plants WHERE id IN ($placeholders)");
        $stmt->execute($plant_ids);
        $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare cart items with quantities and subtotals
        foreach ($plants as $plant) {
            $quantity = $_SESSION['cart'][$plant['id']];
            $subtotal = $plant['price'] * $quantity;
            $total += $subtotal;
            
            $cart_items[] = [
                'plant' => $plant,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        $error = "Could not load cart items. Please try again.";
    }
}
?>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-3x"></i>
            <p>Your cart is empty</p>
            <a href="<?= url('/plants.php') ?>" class="btn btn-primary">
                <i class="fas fa-leaf"></i> Browse Plants
            </a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item" data-plant-id="<?= $item['plant']['id'] ?>">
                <div class="item-image">
                    <img src="<?= htmlspecialchars($item['plant']['image_url']) ?>" 
                         alt="<?= htmlspecialchars($item['plant']['name']) ?>">
                </div>
                
                <div class="item-details">
                    <h3><?= htmlspecialchars($item['plant']['name']) ?></h3>
                    <p class="price">$<?= number_format($item['plant']['price'], 2) ?></p>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" title="Decrease quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="quantity-input" 
                               value="<?= $item['quantity'] ?>" min="1" max="10">
                        <button class="quantity-btn plus" title="Increase quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-subtotal">
                    $<?= number_format($item['subtotal'], 2) ?>
                </div>
                
                <button class="remove-item" title="Remove from cart">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
    <div class="total">
        <span>Total:</span>
        <span>$<?= number_format($total, 2) ?></span>
    </div>
    <a href="./checkout.php" class="checkout-btn">
    <i class="fas fa-credit-card"></i> Proceed to Checkout
</a>

</div>

    <?php endif; ?>
</div>

<style>
    .cart-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .empty-cart {
        text-align: center;
        padding: 3rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 2rem 0;
    }
    
    .empty-cart i {
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .empty-cart p {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
    }
    
    .cart-items {
        display: grid;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .cart-item {
        display: grid;
        grid-template-columns: 100px 1fr auto auto;
        gap: 1.5rem;
        align-items: center;
        padding: 1rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .item-image {
        width: 100px;
        height: 100px;
        overflow: hidden;
        border-radius: 4px;
    }
    
    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        border: none;
        background: #f1f1f1;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quantity-btn:hover {
        background: #e2e2e2;
    }
    
    .quantity-input {
        width: 50px;
        text-align: center;
        padding: 0.3rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .remove-item {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0.5rem;
    }
    
    .remove-item:hover {
        color: #c82333;
    }
    
    .cart-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .total {
        font-size: 1.2rem;
        font-weight: bold;
    }
    
    .total span:last-child {
        color: var(--primary);
        margin-left: 1rem;
    }
    
    .checkout-btn {
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .checkout-btn:hover {
        background: var(--primary-dark);
        color: white;
    }
    
    @media (max-width: 768px) {
        .cart-item {
            grid-template-columns: 80px 1fr;
            grid-template-areas: 
                "image details"
                "image quantity"
                "subtotal remove";
        }
        
        .item-image {
            grid-area: image;
            width: 80px;
            height: 80px;
        }
        
        .item-details {
            grid-area: details;
        }
        
        .quantity-controls {
            grid-area: quantity;
        }
        
        .item-subtotal {
            grid-area: subtotal;
            justify-self: start;
        }
        
        .remove-item {
            grid-area: remove;
            justify-self: end;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartItem = this.closest('.cart-item');
            const plantId = cartItem.dataset.plantId;
            const input = cartItem.querySelector('.quantity-input');
            let quantity = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                quantity = Math.max(1, quantity - 1);
            } else {
                quantity = Math.min(10, quantity + 1);
            }
            
            input.value = quantity;
            await updateCartItem(plantId, quantity);
        });
    });
    
    // Remove item
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartItem = this.closest('.cart-item');
            const plantId = cartItem.dataset.plantId;
            
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                await updateCartItem(plantId, 0);
                cartItem.remove();
                
                // Reload if cart is now empty
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
            }
        });
    });
    
    // Quantity input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async function() {
            const quantity = Math.min(10, Math.max(1, parseInt(this.value) || 1));
            this.value = quantity;
            
            const plantId = this.closest('.cart-item').dataset.plantId;
            await updateCartItem(plantId, quantity);
        });
    });
    
    // Update cart item function
    async function updateCartItem(plantId, quantity) {
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
                body: `plant_id=${plantId}&quantity=${quantity}`
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
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>