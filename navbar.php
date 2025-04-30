<?php
// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

// Calculate cart totals if cart exists
$cart_count = 0;
$cart_total = 0;

if (isset($_SESSION['cart'])) {
    $cart_count = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + $item['quantity'];
    }, 0);
    $cart_total = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Navbar Styles */
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --primary-dark: #1b5e20;
            --secondary: #ff9800;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
            --black: #212121;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding-top: 80px;
        }

        /* Header & Navbar */
        header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            position: relative;
        }

        .nav-brand a {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .nav-main {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-main > a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s;
            position: relative;
        }

        .nav-main > a:hover {
            color: var(--primary);
        }

        /* Dropdown Menus */
        .dropdown {
            position: relative;
        }

        .dropdown > a {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--white);
            width: 220px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 15px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .dropdown:hover .dropdown-content {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-content a {
            display: block;
            padding: 10px 20px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s;
        }

        .dropdown-content a:hover {
            background: var(--light);
            color: var(--primary);
            padding-left: 25px;
        }

        .dropdown-content .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 10px 0;
        }

        /* User & Cart Section */
        .nav-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* For logged in users */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-name {
            font-weight: 500;
        }

        /* Cart */
        .nav-cart {
            position: relative;
        }

        .cart-link {
            position: relative;
            display: flex;
            align-items: center;
            color: var(--dark);
            text-decoration: none;
            gap: 5px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .cart-preview {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            background: var(--white);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 15px;
            display: none;
            z-index: 100;
        }

        .nav-cart:hover .cart-preview {
            display: block;
        }

        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
        }

        .cart-item {
            display: flex;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .cart-item-info h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .cart-item-info p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            color: var(--gray);
        }

        .cart-summary {
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .cart-summary p {
            display: flex;
            justify-content: space-between;
            margin: 0 0 15px;
            font-weight: 500;
        }

        .cart-summary .btn {
            display: block;
            text-align: center;
            padding: 10px;
            background: var(--primary);
            color: var(--white);
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cart-summary .btn:hover {
            background: var(--primary-dark);
        }

        /* Auth Buttons (when not logged in) */
        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-login {
            padding: 8px 15px;
            background: transparent;
            color: var(--dark);
            border: 1px solid var(--gray);
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--light);
        }

        .btn-signup {
            padding: 8px 15px;
            background: var(--primary);
            color: var(--white);
            border: 1px solid var(--primary);
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-signup:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .nav-main {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 80%;
                height: calc(100vh - 80px);
                background: var(--white);
                flex-direction: column;
                align-items: flex-start;
                padding: 30px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                transition: left 0.3s;
                z-index: 99;
                gap: 20px;
            }

            .nav-main.active {
                left: 0;
            }

            .dropdown-content {
                position: static;
                box-shadow: none;
                padding: 10px 0 0 20px;
                width: 100%;
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
            }

            .dropdown:hover .dropdown-content {
                display: block;
            }

            .mobile-menu-btn {
                display: block;
            }

            .nav-user-section {
                margin-left: auto;
                margin-right: 15px;
            }

            .cart-preview {
                width: 280px;
                right: -20px;
            }
        }

        @media (max-width: 576px) {
            .auth-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn-login, .btn-signup {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <nav>
                <div class="nav-brand">
                    <a href="index.php">
                        <img src="images/logo.png" alt="Nabta Logo">
                        Nabta
                    </a>
                </div>

                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>

                <div class="nav-main">
                    <a href="index.php">Home</a>
                    
                    <div class="dropdown">
                        <a href="products.php">Shop <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-content">
                            <a href="plants.php">
                                <i class="fas fa-leaf"></i> Plants
                            </a>
                            <a href="pots.php">
                                <i class="fas fa-mug-hot"></i> Pots & Planters
                            </a>
                            <a href="kits.php">
                                <i class="fas fa-box-open"></i> Growing Kits
                            </a>
                            <a href="herbs.php">
                                <i class="fas fa-seedling"></i> Herbs
                            </a>
                            <a href="accessories.php">
                                <i class="fas fa-tools"></i> Accessories
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="products.php">
                                <i class="fas fa-store"></i> Shop All
                            </a>
                        </div>
                    </div>
                    
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                </div>

                <div class="nav-user-section">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php 
                                    // Display first letter of username if name doesn't exist
                                    echo strtoupper(substr($_SESSION['user']['username'], 0, 1)); 
                                    ?>
                                </div>
                                <span class="user-name">
                                    <?php 
                                    // Display username as name if name doesn't exist
                                    echo htmlspecialchars($_SESSION['user']['username']); 
                                    ?>
                                </span>
                            </div>
                            <div class="dropdown-content">
                                <a href="account.php">
                                    <i class="fas fa-user"></i> My Account
                                </a>
                                <a href="orders.php">
                                    <i class="fas fa-clipboard-list"></i> My Orders
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>

                        <div class="nav-cart">
                            <a href="cart.php" class="cart-link">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            </a>
                            
                            <?php if ($cart_count > 0): ?>
                                <div class="cart-preview">
                                    <div class="cart-items">
                                        <?php foreach ($_SESSION['cart']['items'] as $item): ?>
                                        <div class="cart-item">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <div class="cart-item-info">
                                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <p><?php echo $item['quantity']; ?> × EGP <?php echo number_format($item['price'], 2); ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="cart-summary">
                                        <p>
                                            <span>Subtotal:</span>
                                            <span>EGP <?php echo number_format($cart_total, 2); ?></span>
                                        </p>
                                        <a href="cart.php" class="btn">View Cart</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn-login">Login</a>
                            <a href="register.php" class="btn-signup">Sign Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-main').classList.toggle('active');
        });

        // Dropdown toggle for mobile
        document.querySelectorAll('.dropdown > a').forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    const content = this.nextElementSibling;
                    content.style.display = content.style.display === 'block' ? 'none' : 'block';
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                const dropdowns = document.querySelectorAll('.dropdown-content');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.parentNode.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            }
        });
    </script>