<?php
require_once __DIR__ . './config/init.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$product = null;
$error = '';

try {
    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $error = "Product not found.";
    }
} catch (PDOException $e) {
    error_log("Product details error: " . $e->getMessage());
    $error = "We're having trouble loading the product details. Please try again later.";
}

// Set page title
$page_title = $product ? htmlspecialchars($product['name']) : "Product Not Found";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --primary-dark: #1b5e20;
            --secondary: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
        }
        
        .product-details-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .product-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .product-image-container {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .product-image {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-size: 2rem;
            color: var(--secondary);
            margin: 0 0 20px;
        }
        
        .product-price {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .product-description {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .product-meta {
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--gray);
        }
        
        .meta-item i {
            width: 20px;
            margin-right: 10px;
            color: var(--primary);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .quantity-btn {
            background: var(--light);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            font-size: 1.1rem;
        }
        
        .add-to-cart {
            padding: 15px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 0.3s;
        }
        
        .add-to-cart:hover {
            background: var(--primary-dark);
        }
        
        .add-to-cart:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }
        
        .error-message {
            text-align: center;
            color: #d32f2f;
            padding: 20px;
            background: #ffebee;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .stock-info {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .low-stock {
            color: #ff9800;
        }
        
        .out-of-stock {
            color: #f44336;
        }
        
        /* Cart notification styles */
        .cart-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            max-width: 300px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-notification.show {
            transform: translateX(0);
        }

        .cart-notification.error {
            background: #f44336;
        }

        .cart-notification i {
            font-size: 1.2em;
        }
        
        @media (max-width: 768px) {
            .product-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . './includes/navbar.php'; ?>
    
    <!-- Cart notification element -->
    <div id="cart-notification" class="cart-notification"></div>
    
    <div class="product-details-container">
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php elseif ($product): ?>
            <div class="product-details">
                <div class="product-image-container">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image">
                </div>
                
                <div class="product-info">
                    <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                    
                    <div class="stock-info <?= $product['stock'] < 5 ? 'low-stock' : '' ?>">
                        <?php if ($product['stock'] > 0): ?>
                            <i class="fas fa-check-circle"></i> In stock: <?= $product['stock'] ?> units
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Out of stock
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span>Category: <?= ucfirst(htmlspecialchars($product['category'])) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-box"></i>
                            <span>SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    
                    <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" data-product-id="<?= $product['id'] ?>">-</button>
                        <input type="number" class="quantity-input" value="1" min="1" max="<?= min(10, $product['stock']) ?>" 
                               data-product-id="<?= $product['id'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <button class="quantity-btn plus" data-product-id="<?= $product['id'] ?>">+</button>
                    </div>
                    
                    <button class="add-to-cart" data-product-id="<?= $product['id'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-cart-plus"></i> 
                        <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cart notification element
        const cartNotification = document.getElementById('cart-notification');
        
        // Show notification function
        function showNotification(message, isError = false) {
            cartNotification.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
            cartNotification.className = isError ? 'cart-notification error show' : 'cart-notification show';
            
            setTimeout(() => {
                cartNotification.classList.remove('show');
            }, 3000);
        }
        
        // Update cart count in navbar
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                if (count > 0) {
                    el.textContent = count;
                    if (!el.parentElement.querySelector('.cart-count')) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'cart-count animate-bounce';
                        newBadge.textContent = count;
                        el.parentElement.appendChild(newBadge);
                    }
                    el.classList.add('animate-bounce');
                    setTimeout(() => el.classList.remove('animate-bounce'), 500);
                } else {
                    const badge = el.parentElement.querySelector('.cart-count');
                    if (badge) {
                        badge.remove();
                    }
                }
            });
        }
        
        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                let value = parseInt(input.value);
                const max = parseInt(input.max);
                
                if (this.classList.contains('minus') && value > 1) {
                    input.value = value - 1;
                } else if (this.classList.contains('plus') && value < max) {
                    input.value = value + 1;
                }
            });
        });
        
        // Add to cart functionality
        const addToCartBtn = document.querySelector('.add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', async function() {
                const productId = this.dataset.productId;
                const productName = document.querySelector('.product-name').textContent;
                const quantity = parseInt(document.querySelector('.quantity-input').value);
                
                // Disable button during request
                this.disabled = true;
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                
                try {
                    const response = await fetch('<?= url("actions/add_to_cart.php") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=${quantity}`
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to add to cart');
                    }
                    
                    // Show success notification
                    showNotification(`${productName} added to cart (${quantity})`);
                    
                    // Update cart count
                    if (data.cart_count !== undefined) {
                        updateCartCount(data.cart_count);
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    showNotification(error.message || 'Failed to add to cart', true);
                } finally {
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        }
        
        // Initialize cart count on page load
        async function initializeCartCount() {
            try {
                const response = await fetch('<?= url("actions/get_cart_count.php") ?>');
                const data = await response.json();
                
                if (response.ok && data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
            } catch (error) {
                console.error('Could not load cart count:', error);
            }
        }
        
        // Call the initialization function
        initializeCartCount();
    });
    </script>

    <?php include __DIR__ . './includes/footer.php'; ?>
</body>
</html>