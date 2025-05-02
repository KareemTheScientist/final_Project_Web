<?php
require_once __DIR__ . '/config/init.php';

// Get current category filter
$category = isset($_GET['category']) ? strtolower($_GET['category']) : null;
$valid_categories = ['pot', 'sensor', 'utility'];

// Initialize variables
$featured_products = [];
$regular_products = [];
$error = '';

try {
    // Get featured products (Smart Gardens & Premium Sensor Kits)
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category IN ('pot', 'sensor') AND stock > 0
        ORDER BY created_at DESC
        LIMIT 6
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get regular products (other accessories)
    $query = "SELECT * FROM products WHERE stock > 0";
    $params = [];
    
    // Add category filter if valid
    if ($category && in_array($category, $valid_categories)) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $regular_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Products page error: " . $e->getMessage());
    $error = "We're having trouble loading products. Please try again later.";
}

// Set page title
$page_title = "Our Products" . ($category ? " - " . ucfirst($category) : "");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Nabta</title>
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
        
        .products-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .section-header {
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-filters {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .category-btn {
            padding: 8px 16px;
            border-radius: 20px;
            background: var(--light);
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .category-btn:hover, .category-btn.active {
            background: var(--primary);
            color: var(--white);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
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
        
        .product-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            z-index: 1;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            margin: 0 0 5px;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        
        .product-category {
            font-size: 0.8rem;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .product-price {
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .product-desc {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
            min-height: 40px;
        }
        
        .add-to-cart {
            display: block;
            width: 100%;
            padding: 8px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.9rem;
        }
        
        .add-to-cart:hover {
            background: var(--primary-dark);
        }
        
        .add-to-cart:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }
        
        .quantity-controls {
            display: flex;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .quantity-btn {
            background: var(--light);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .quantity-input {
            width: 40px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        
        .error-message {
            text-align: center;
            color: #d32f2f;
            padding: 20px;
            background: #ffebee;
            border-radius: 8px;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <!-- Cart notification element -->
    <div id="cart-notification" class="cart-notification"></div>
    
    <div class="products-container">
        <div class="page-header">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <p>Enhance your gardening experience with our premium products</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php else: ?>
            <div class="category-filters">
                <a href="<?= url('products.php') ?>" class="category-btn <?= !$category ? 'active' : '' ?>">All Products</a>
                <a href="<?= url('products.php?category=pot') ?>" class="category-btn <?= $category === 'pot' ? 'active' : '' ?>">Smart Pots</a>
                <a href="<?= url('products.php?category=sensor') ?>" class="category-btn <?= $category === 'sensor' ? 'active' : '' ?>">Sensors</a>
                <a href="<?= url('products.php?category=utility') ?>" class="category-btn <?= $category === 'utility' ? 'active' : '' ?>">Accessories</a>
            </div>
            
            <!-- Featured Products Section (Smart Gardens & Sensors) -->
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Smart Gardens & Sensor Kits</h2>
            </div>
            
            <?php if (!empty($featured_products)): ?>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image-container">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                            <span class="product-badge">
                                <?= $product['category'] === 'pot' ? 'Smart Garden' : 'Sensor Kit' ?>
                            </span>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-category"><?= ucfirst($product['category']) ?></div>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn minus" data-product-id="<?= $product['id'] ?>">-</button>
                                <input type="number" class="quantity-input" value="1" min="1" max="10" data-product-id="<?= $product['id'] ?>">
                                <button class="quantity-btn plus" data-product-id="<?= $product['id'] ?>">+</button>
                            </div>
                            
                            <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No featured products available at the moment.</p>
            <?php endif; ?>
            
            <!-- Regular Products Section (Accessories) -->
            <div class="section-header">
                <h2><i class="fas fa-tools"></i> Plant Accessories</h2>
            </div>
            
            <?php if (!empty($regular_products)): ?>
                <div class="products-grid">
                    <?php foreach ($regular_products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image-container">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                            <span class="product-badge"><?= ucfirst($product['category']) ?></span>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-category"><?= ucfirst($product['category']) ?></div>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn minus" data-product-id="<?= $product['id'] ?>">-</button>
                                <input type="number" class="quantity-input" value="1" min="1" max="10" data-product-id="<?= $product['id'] ?>">
                                <button class="quantity-btn plus" data-product-id="<?= $product['id'] ?>">+</button>
                            </div>
                            
                            <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No products available in this category.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

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
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('.product-name').textContent;
            const quantity = parseInt(productCard.querySelector('.quantity-input').value);
            
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
                productCard.style.boxShadow = '0 0 0 2px #4CAF50';
                setTimeout(() => {
                    productCard.style.boxShadow = '';
                }, 1000);
                
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