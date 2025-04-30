<?php
session_start();
include 'db.php';

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $plant_id = $_POST['plant_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) $quantity = 1;
    
    // Fetch plant
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM plants WHERE id = ?");
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($plant) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],
                'total' => 0,
                'count' => 0
            ];
        }
        
        // Check if item exists
        $item_exists = false;
        foreach ($_SESSION['cart']['items'] as &$item) {
            if ($item['id'] == $plant_id) {
                $item['quantity'] += $quantity;
                $item_exists = true;
                break;
            }
        }
        
        // Add new item if not exists
        if (!$item_exists) {
            $_SESSION['cart']['items'][] = [
                'id' => $plant['id'],
                'name' => $plant['name'],
                'price' => $plant['price'],
                'quantity' => $quantity,
                'image' => $plant['image_url']
            ];
        }
        
        // Update cart totals
        $_SESSION['cart']['total'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
        
        $_SESSION['cart']['count'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
            return $sum + $item['quantity'];
        }, 0);
        
        $_SESSION['success'] = "{$plant['name']} (×{$quantity}) added to cart!";
    } else {
        $_SESSION['error'] = "Product not found!";
    }
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Fetch featured plants
$featured_plants = [];
try {
    $stmt = $conn->prepare("SELECT * FROM plants WHERE is_featured = 1 LIMIT 8");
    $stmt->execute();
    $featured_plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch new arrivals
$new_plants = [];
try {
    $stmt = $conn->prepare("SELECT * FROM plants WHERE is_new = 1 ORDER BY date_added DESC LIMIT 8");
    $stmt->execute();
    $new_plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plants Collection | Nabta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32; /* Nabta green */
            --primary-light: #4CAF50;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
        }
        
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark);
            line-height: 1.6;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary);
            font-size: 2rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--primary);
            margin: 15px auto 0;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px;
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 4px;
            text-align: center;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Cart Link */
        .cart-link {
            position: relative;
            text-decoration: none;
            color: var(--dark);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }
        
        /* Hero Section */
        .plants-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/plants-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 120px 20px;
            margin-bottom: 40px;
        }
        
        .plants-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .plants-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Plants Grid */
        .plants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .plant-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .plant-img {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .plant-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .plant-card:hover .plant-img img {
            transform: scale(1.05);
        }
        
        .new-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .plant-info {
            padding: 20px;
        }
        
        .plant-info h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .plant-desc {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .plant-price {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .plant-price::before {
            content: "EGP ";
        }
        
        /* Add to Cart Form */
        .add-to-cart-form {
            margin-top: 15px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        .quantity-btn:hover {
            background: var(--primary-light);
        }
        
        .quantity-input {
            width: 60px;
            height: 35px;
            text-align: center;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn-cart {
            width: 100%;
            padding: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-cart:hover {
            background: var(--primary-light);
        }
        
        .btn-cart.added {
            background: #4CAF50 !important;
        }
        
        /* Benefits Section */
        .benefits-section {
            background: var(--light);
        }
        
        .benefits-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .benefits-text {
            flex: 1;
        }
        
        .benefits-text h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .benefits-list {
            list-style-type: none;
            padding: 0;
        }
        
        .benefits-list li {
            margin-bottom: 15px;
            position: relative;
            padding-left: 30px;
        }
        
        .benefits-list li::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: var(--primary);
            position: absolute;
            left: 0;
        }
        
        .benefits-image {
            flex: 1;
        }
        
        .benefits-image img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Animations */
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .bounce {
            animation: bounce 1s;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .plants-hero h1 {
                font-size: 2.2rem;
            }
            
            .benefits-content {
                flex-direction: column;
            }
            
            .section {
                padding: 40px 0;
            }
        }
    </style>
</head>
<body>
    <header>
    <?php include 'navbar.php'; ?>
    </header>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="plants-hero">
        <div class="container">
            <h1>Nabta Plant Collection</h1>
            <p>Discover our premium selection of plants cultivated with sustainable agricultural practices</p>
        </div>
    </section>

    <!-- Featured Plants -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Plants</h2>
            <div class="plants-grid">
                <?php foreach ($featured_plants as $plant): ?>
                <div class="plant-card">
                    <div class="plant-img">
                        <img src="<?php echo htmlspecialchars($plant['image_url']); ?>" alt="<?php echo htmlspecialchars($plant['name']); ?>">
                        <?php if ($plant['is_new']): ?>
                            <span class="new-badge">NEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="plant-info">
                        <h3><?php echo htmlspecialchars($plant['name']); ?></h3>
                        <p class="plant-desc"><?php echo htmlspecialchars($plant['short_description']); ?></p>
                        <p class="plant-price"><?php echo number_format($plant['price'], 2); ?></p>
                        
                        <form method="post" class="add-to-cart-form">
                            <input type="hidden" name="plant_id" value="<?php echo $plant['id']; ?>">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn minus">-</button>
                                <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                                <button type="button" class="quantity-btn plus">+</button>
                            </div>
                            <button type="submit" name="add_to_cart" class="btn-cart">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="section benefits-section">
        <div class="container">
            <div class="benefits-content">
                <div class="benefits-text">
                    <h2>Why Choose Nabta Plants?</h2>
                    <ul class="benefits-list">
                        <li>Grown using sustainable agricultural methods</li>
                        <li>Higher survival rate than conventional plants</li>
                        <li>Optimized for Egyptian climate conditions</li>
                        <li>Regular quality control checks</li>
                        <li>Expert cultivation guidance available</li>
                    </ul>
                </div>
                <div class="benefits-image">
                    <img src="./img/cultMethods.jpg" alt="Nabta cultivation methods">
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="plants-grid">
                <?php foreach ($new_plants as $plant): ?>
                <div class="plant-card">
                    <div class="plant-img">
                        <img src="<?php echo htmlspecialchars($plant['image_url']); ?>" alt="<?php echo htmlspecialchars($plant['name']); ?>">
                        <span class="new-badge">NEW</span>
                    </div>
                    <div class="plant-info">
                        <h3><?php echo htmlspecialchars($plant['name']); ?></h3>
                        <p class="plant-desc"><?php echo htmlspecialchars($plant['short_description']); ?></p>
                        <p class="plant-price"><?php echo number_format($plant['price'], 2); ?></p>
                        
                        <form method="post" class="add-to-cart-form">
                            <input type="hidden" name="plant_id" value="<?php echo $plant['id']; ?>">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn minus">-</button>
                                <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                                <button type="button" class="quantity-btn plus">+</button>
                            </div>
                            <button type="submit" name="add_to_cart" class="btn-cart">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script>
        // Quantity buttons
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('.quantity-input');
                let value = parseInt(input.value);
                
                if (this.classList.contains('minus') && value > 1) {
                    input.value = value - 1;
                } else if (this.classList.contains('plus')) {
                    input.value = value + 1;
                }
            });
        });

        // Add to cart animation
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = this.querySelector('.btn-cart');
                btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                btn.classList.add('added');
                
                // Update cart count in navbar
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    const current = parseInt(cartCount.textContent);
                    const quantity = parseInt(this.querySelector('.quantity-input').value);
                    cartCount.textContent = current + quantity;
                    
                    // Add bounce animation
                    cartCount.classList.add('bounce');
                    setTimeout(() => {
                        cartCount.classList.remove('bounce');
                    }, 1000);
                }
                
                // Prevent double submission
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    btn.classList.remove('added');
                }, 2000);
            });
        });
    </script>

    <footer>
    <?php include 'footer.php'; ?>

    </footer>
</body>
</html>