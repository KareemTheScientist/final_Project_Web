<?php
require_once __DIR__ . './config/init.php';

// Get current category filter
$category = isset($_GET['category']) ? strtolower($_GET['category']) : null;
$valid_categories = ['herbs', 'vegetables', 'flowers'];

// Pagination setup
$per_page = 12;
$current_page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$offset = ($current_page - 1) * $per_page;

// Initialize variables
$plants = [];
$total_plants = 0;
$error = '';

try {
    // Base query
    $query = "SELECT * FROM plants WHERE active = 1";
    $count_query = "SELECT COUNT(*) FROM plants WHERE active = 1";
    
    // Add category filter if valid
    if ($category && in_array($category, $valid_categories)) {
        $query .= " AND category = :category";
        $count_query .= " AND category = :category";
    }
    
    // Add sorting
    $query .= " ORDER BY is_featured DESC, date_added DESC LIMIT :limit OFFSET :offset";
    
    // Get total count
    $stmt = $pdo->prepare($count_query);
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    $stmt->execute();
    $total_plants = $stmt->fetchColumn();
    
    // Get plants for current page
    $stmt = $pdo->prepare($query);
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Plants page error: " . $e->getMessage());
    $error = "We're having trouble loading plants. Please try again later.";
}

// Set page title
$page_title = "Our Plants" . ($category ? " - " . ucfirst($category) : "");
include __DIR__ . './includes/header.php';
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
        
        .plants-container {
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
            border: none;
            cursor: pointer;
        }
        
        .category-btn:hover, .category-btn.active {
            background: var(--primary);
            color: var(--white);
        }
        
        .plants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .plant-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .plant-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .plant-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .plant-card:hover .plant-image {
            transform: scale(1.05);
        }
        
        .plant-badge {
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
        
        .new-badge {
            right: auto;
            left: 10px;
            background: var(--primary-light);
        }
        
        .plant-info {
            padding: 15px;
        }
        
        .plant-name {
            margin: 0 0 5px;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        
        .plant-price {
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .plant-desc {
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
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a {
            padding: 8px 12px;
            border-radius: 4px;
            background: var(--light);
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .pagination a:hover, .pagination a.active {
            background: var(--primary);
            color: white;
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
    <?php include __DIR__ . './includes/navbar.php'; ?>
    
    <!-- Cart notification element -->
    <div id="cart-notification" class="cart-notification"></div>
    
    <div class="plants-container">
        <div class="page-header">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <p>Discover our collection of beautiful indoor plants</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php else: ?>
            <div class="category-filters">
                <a href="<?= url('plants.php') ?>" class="category-btn <?= !$category ? 'active' : '' ?>">All Plants</a>
                <a href="<?= url('plants.php?category=herbs') ?>" class="category-btn <?= $category === 'herbs' ? 'active' : '' ?>">Herbs</a>
                <a href="<?= url('plants.php?category=vegetables') ?>" class="category-btn <?= $category === 'vegetables' ? 'active' : '' ?>">Vegetables</a>
                <a href="<?= url('plants.php?category=flowers') ?>" class="category-btn <?= $category === 'flowers' ? 'active' : '' ?>">Flowers</a>
            </div>
            
            <div class="plants-grid">
                <?php foreach ($plants as $plant): ?>
                <div class="plant-card" data-plant-id="<?= $plant['id'] ?>">
                    <div class="plant-image-container">
                        <img src="<?= htmlspecialchars($plant['image_url']) ?>" 
                             alt="<?= htmlspecialchars($plant['name']) ?>" 
                             class="plant-image">
                        
                        <?php if ($plant['is_featured']): ?>
                            <span class="plant-badge">Featured</span>
                        <?php endif; ?>
                        
                        <?php if ($plant['is_new']): ?>
                            <span class="plant-badge new-badge">New</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plant-info">
                        <h3 class="plant-name"><?= htmlspecialchars($plant['name']) ?></h3>
                        <div class="plant-price">$<?= number_format($plant['price'], 2) ?></div>
                        <p class="plant-desc"><?= htmlspecialchars($plant['short_description']) ?></p>
                        
                        <div class="quantity-controls">
                            <button class="quantity-btn minus" data-plant-id="<?= $plant['id'] ?>">-</button>
                            <input type="number" class="quantity-input" value="1" min="1" max="10" data-plant-id="<?= $plant['id'] ?>">
                            <button class="quantity-btn plus" data-plant-id="<?= $plant['id'] ?>">+</button>
                        </div>
                        
                        <button class="add-to-cart" data-plant-id="<?= $plant['id'] ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_plants > $per_page): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?= url('plants.php?' . http_build_query(['category' => $category, 'page' => $current_page - 1])) ?>">
                        <i class="fas fa-chevron-left"></i> Prev
                    </a>
                <?php endif; ?>
                
                <?php 
                $total_pages = ceil($total_plants / $per_page);
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="<?= url('plants.php?' . http_build_query(['category' => $category, 'page' => $i])) ?>" 
                       class="<?= $i === $current_page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= url('plants.php?' . http_build_query(['category' => $category, 'page' => $current_page + 1])) ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
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
            const plantId = this.dataset.plantId;
            const input = document.querySelector(`.quantity-input[data-plant-id="${plantId}"]`);
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
            const plantId = this.dataset.plantId;
            const plantCard = this.closest('.plant-card');
            const plantName = plantCard.querySelector('.plant-name').textContent;
            const quantity = parseInt(plantCard.querySelector('.quantity-input').value);
            
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
                    body: `plant_id=${plantId}&quantity=${quantity}`
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to add to cart');
                }
                
                // Show success notification
                showNotification(`${plantName} added to cart (${quantity})`);
                
                // Update cart count
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
                
                // Visual feedback
                plantCard.style.boxShadow = '0 0 0 2px #4CAF50';
                setTimeout(() => {
                    plantCard.style.boxShadow = '';
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