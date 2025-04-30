<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cart data
$cart_count = $_SESSION['cart']['count'] ?? 0;
$current_user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nabta - Smart Indoor Gardens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Navbar Styles */
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --primary-dark: #1b5e20;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            padding-top: 80px;
        }

        /* Navbar Container */
        .navbar {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 0 20px;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        /* Brand/Logo */
        .nav-brand {
            display: flex;
            align-items: center;
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

        /* Navigation Links */
        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 30px;
            position: relative;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a.active {
            color: var(--primary);
            font-weight: 600;
        }

        /* Dropdown Menus */
        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--white);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 4px;
            z-index: 1;
            display: none;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            padding: 12px 16px;
            display: block;
            color: var(--dark);
            transition: all 0.3s;
        }

        .dropdown-content a:hover {
            background-color: var(--light);
            color: var(--primary);
            padding-left: 20px;
        }

        /* User/Cart Section */
        .nav-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
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
            font-weight: bold;
        }

        /* Auth Buttons */
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

        /* User Dropdown */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark);
            font-weight: 500;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .nav-links {
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

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                margin-left: 0;
                width: 100%;
            }

            .dropdown-content {
                position: static;
                box-shadow: none;
                width: 100%;
                display: none;
                padding-left: 20px;
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
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Nabta Logo">
                    Nabta
                </a>
            </div>

            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>

            <ul class="nav-links">
                <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Home</a></li>
                
                <li class="dropdown">
                    <a href="plants.php" class="<?= basename($_SERVER['PHP_SELF']) === 'plants.php' ? 'active' : '' ?>">
                        Plants <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="plants.php?category=herbs"><i class="fas fa-leaf"></i> Herbs</a>
                        <a href="plants.php?category=vegetables"><i class="fas fa-carrot"></i> Vegetables</a>
                        <a href="plants.php?category=flowers"><i class="fas fa-spa"></i> Flowers</a>
                        <div class="dropdown-divider"></div>
                        <a href="plants.php"><i class="fas fa-store"></i> All Plants</a>
                    </div>
                </li>
                
                <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">Products</a></li>
                <li><a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : '' ?>">About</a></li>
                <li><a href="contact.php" class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            </ul>

            <div class="nav-user-section">
                <?php if (isset($current_user)): ?>
                    <div class="nav-cart">
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <div class="user-avatar">
                                <?= strtoupper(substr($current_user['username'], 0, 1)) ?>
                            </div>
                            <span><?= htmlspecialchars($current_user['username']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content" style="right: 0; left: auto;">
                            <a href="account.php"><i class="fas fa-user"></i> My Account</a>
                            <a href="orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a>
                            <div class="dropdown-divider"></div>
                            <a href="../actions/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-signup">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
            this.innerHTML = this.innerHTML.includes('times') ? 
                '<i class="fas fa-bars"></i>' : 
                '<i class="fas fa-times"></i>';
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                if (!e.target.closest('.nav-container')) {
                    document.querySelector('.nav-links').classList.remove('active');
                    document.querySelector('.mobile-menu-btn').innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        });

        // Dropdown toggle for user menu
        document.querySelector('.user-dropdown-toggle')?.addEventListener('click', function() {
            this.nextElementSibling.style.display = 
                this.nextElementSibling.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdowns when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.matches('.user-dropdown-toggle')) {
                const dropdowns = document.querySelectorAll('.dropdown-content');
                dropdowns.forEach(dropdown => {
                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>