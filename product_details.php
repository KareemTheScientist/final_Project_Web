<?php
require_once __DIR__ . '/config/init.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];
$product = null;
$related_products = [];
$error = '';

try {
    // Get the product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header("Location: products.php");
        exit();
    }
    
    // Get related products (same category)
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category = ? AND id != ? AND stock > 0
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->execute([$product['category'], $product_id]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Product page error: " . $e->getMessage());
    $error = "We're having trouble loading this product. Please try again later.";
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
        
        .product-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .product-gallery {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 8px;
            background: var(--light);
        }
        
        .product-info {
            padding: 20px 0;
        }
        
        .product-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .product-category {
            display: inline-block;
            padding: 4px 12px;
            background: var(--primary-light);
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .product-description {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .product-meta {
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .meta-label {
            font-weight: bold;
            width: 120px;
            color: var(--secondary);
        }
        
        .related-products {
            margin-top: 60px;
        }
        
        .related-header {
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-light);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }
        
        /* Reuse product card styles from products.php */
        .product-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Add other reused styles as needed */
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <!-- Cart notification element -->
    <div id="cart-notification" class="cart-notification"></div>
    
    <div class="product-container">
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php elseif ($product): ?>
            <div class="product-detail">
                <div class="product-gallery">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="main-image">
                </div>
                
                <div class="product-info">
                    <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    <span class="product-category"><?= ucfirst($product['category']) ?></span>
                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                    
                    <div class="product-description">
                        <h3>Description</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">Availability:</span>
                            <span><?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span><?= ucfirst($product['category']) ?></span>
                        </div>
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" data-product-id="<?= $product['id'] ?>">-</button>
                        <input type="number" class="quantity-input" value="1" min="1" max="10" data-product-id="<?= $product['id'] ?>">
                        <button class="quantity-btn plus" data-product-id="<?= $product['id'] ?>">+</button>
                    </div>
                    
                    <button class="add-to-cart" data-product-id="<?= $product['id'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-cart-plus"></i> 
                        <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                    </button>
                </div>
            </div>
            
            <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h2 class="related-header">You May Also Like</h2>
                    <div class="related-grid">
                        <?php foreach ($related_products as $related): ?>
                        <div class="product-card" data-product-id="<?= $related['id'] ?>">
                            <div class="product-image-container">
                                <a href="<?= url('product.php?id=' . $related['id']) ?>">
                                    <img src="<?= htmlspecialchars($related['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($related['name']) ?>" 
                                         class="product-image">
                                </a>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="<?= url('product.php?id=' . $related['id']) ?>">
                                        <?= htmlspecialchars($related['name']) ?>
                                    </a>
                                </h3>
                                <div class="product-price">$<?= number_format($related['price'], 2) ?></div>
                                
                                <button class="add-to-cart" data-product-id="<?= $related['id'] ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Reuse your existing JavaScript from products.php
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
                    
                    if (this.classList.contains('minus') && value > 1) {
                        input.value = value - 1;
                    } else if (this.classList.contains('plus') && value < 10) {
                        input.value = value + 1;
                    }
                });
            });
            
            // Add to cart functionality
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    const productCard = this.closest('.product-card') || document.querySelector(`.product-detail`);
                    const productName = productCard.querySelector('.product-name, .product-title').textContent;
                    const quantityInput = productCard.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    
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
                        
                        // Visual feedback
                        if (productCard.classList.contains('product-card')) {
                            productCard.style.boxShadow = '0 0 0 2px #4CAF50';
                            setTimeout(() => {
                                productCard.style.boxShadow = '';
                            }, 1000);
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
            });
            
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
</body>
</html>