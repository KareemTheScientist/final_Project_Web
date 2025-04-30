<?php
require_once '../config/init.php';
require_once '../db.php';

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
    $plants = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Plants page error: " . $e->getMessage());
    $error = "We're having trouble loading plants. Please try again later.";
}

// Set page title
$page_title = "Our Plants" . ($category ? " - " . ucfirst($category) : "");
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
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
            transition: transform 0.3s;
        }
        
        .plant-card:hover {
            transform: translateY(-5px);
        }
        
        .plant-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
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
        }
        
        .pagination a.active {
            background: var(--primary);
            color: white;
        }
        
        .error-message {
            text-align: center;
            color: #d32f2f;
            padding: 20px;
            background: #ffebee;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .plants-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
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
                <a href="plants.php" class="category-btn <?= !$category ? 'active' : '' ?>">All Plants</a>
                <a href="plants.php?category=herbs" class="category-btn <?= $category === 'herbs' ? 'active' : '' ?>">Herbs</a>
                <a href="plants.php?category=vegetables" class="category-btn <?= $category === 'vegetables' ? 'active' : '' ?>">Vegetables</a>
                <a href="plants.php?category=flowers" class="category-btn <?= $category === 'flowers' ? 'active' : '' ?>">Flowers</a>
            </div>
            
            <div class="plants-grid">
    <?php foreach ($plants as $plant): ?>
    <div class="plant-card">
        <div style="position: relative;">
            <!-- Using the exact image_url from database -->
            <img src="<?= htmlspecialchars($plant['image_url']) ?>" 
                 alt="<?= htmlspecialchars($plant['name']) ?>" 
                 class="plant-image">
            
            <!-- Featured badge -->
            <?php if ($plant['is_featured']): ?>
                <span class="plant-badge">Featured</span>
            <?php endif; ?>
            
            <!-- New badge -->
            <?php if ($plant['is_new']): ?>
                <span class="plant-badge" style="right: auto; left: 10px; background: var(--primary-light);">New</span>
            <?php endif; ?>
        </div>
        
        <div class="plant-info">
            <h3 class="plant-name"><?= htmlspecialchars($plant['name']) ?></h3>
            <div class="plant-price">$<?= number_format($plant['price'], 2) ?></div>
            <p class="plant-desc"><?= htmlspecialchars($plant['short_description']) ?></p>
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
                    <a href="plants.php?<?= http_build_query(['category' => $category, 'page' => $current_page - 1]) ?>">
                        <i class="fas fa-chevron-left"></i> Prev
                    </a>
                <?php endif; ?>
                
                <?php 
                $total_pages = ceil($total_plants / $per_page);
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="plants.php?<?= http_build_query(['category' => $category, 'page' => $i]) ?>" 
                       class="<?= $i === $current_page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="plants.php?<?= http_build_query(['category' => $category, 'page' => $current_page + 1]) ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const plantId = this.getAttribute('data-plant-id');
                
                fetch('../actions/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `plant_id=${plantId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in navbar
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(el => {
                            el.textContent = data.cart_count;
                        });
                        
                        // Show success message
                        alert('Item added to cart!');
                    } else {
                        alert(data.message || 'Failed to add to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            });
        });
    </script>
</body>
</html>