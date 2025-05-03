<?php
require_once __DIR__ . '/config/init.php';

// Get current category filter
$category = isset($_GET['category']) ? strtolower($_GET['category']) : null;
$valid_categories = ['pot', 'sensor', 'utility'];

// Initialize variables
$products = [];
$error = '';

try {
    // Build the base query
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
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            margin: 0 0 5px;
            font-size: 1.1rem;
            color: var(--secondary);
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
        }
        
        .add-to-cart:hover {
            background: var(--primary-dark);
        }
        
        .error-message {
            text-align: center;
            color: #d32f2f;
            padding: 20px;
            background: #ffebee;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 20px;
        }
        
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
        }

        .cart-notification.show {
            transform: translateX(0);
        }

        .cart-notification.error {
            background: #f44336;
        }
        
        .cart-count {
            display: none;
            background: var(--primary-dark);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            position: absolute;
            top: -5px;
            right: -5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
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
                <a href="products.php" class="category-btn <?= !$category ? 'active' : '' ?>">All Products</a>
                <a href="products.php?category=pot" class="category-btn <?= $category === 'pot' ? 'active' : '' ?>">Smart Pots</a>
                <a href="products.php?category=sensor" class="category-btn <?= $category === 'sensor' ? 'active' : '' ?>">Sensors</a>
                <a href="products.php?category=utility" class="category-btn <?= $category === 'utility' ? 'active' : '' ?>">Accessories</a>
            </div>
            
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image-container">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image">
                            <span class="product-badge"><?= ucfirst($product['category']) ?></span>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            
                            <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No products found</h3>
                    <p>We couldn't find any products in this category.</p>
                    <a href="products.php" class="category-btn active">View All Products</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartNotification = document.getElementById('cart-notification');
        
        function showNotification(message, isError = false) {
            cartNotification.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
            cartNotification.className = isError ? 'cart-notification error show' : 'cart-notification show';
            
            setTimeout(() => {
                cartNotification.classList.remove('show');
            }, 3000);
        }
        
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = count;
                el.style.display = count > 0 ? 'block' : 'none';
            });
            
            // If no cart count elements exist, create one
            if (cartCountElements.length === 0) {
                const cartLink = document.querySelector('a[href="cart.php"]');
                if (cartLink) {
                    const badge = document.createElement('span');
                    badge.className = 'cart-count';
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'block' : 'none';
                    cartLink.appendChild(badge);
                }
            }
        }
        
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', async function() {
                const productId = this.dataset.productId;
                const productCard = this.closest('.product-card');
                const productName = productCard.querySelector('.product-name').textContent;
                
                this.disabled = true;
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                
                try {
                    const response = await fetch('actions/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=1&item_type=product`
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to add to cart');
                    }
                    
                    showNotification(`${productName} added to cart`);
                    updateCartCount(data.cart_count);
                    
                    // Visual feedback
                    productCard.style.boxShadow = '0 0 0 2px #4CAF50';
                    setTimeout(() => {
                        productCard.style.boxShadow = '';
                    }, 1000);
                    
                } catch (error) {
                    console.error('Error:', error);
                    showNotification(error.message || 'Failed to add to cart', true);
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        });
        
        // Initialize cart count
        async function initializeCartCount() {
            try {
                const response = await fetch('actions/get_cart_count.php');
                const data = await response.json();
                
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
            } catch (error) {
                console.error('Could not load cart count:', error);
            }
        }
        
        initializeCartCount();
    });
    </script>
</body>
</html>