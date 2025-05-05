<?php
require_once __DIR__ . '/../config/init.php';

if (!defined('BASE_URL')) {
    die('Direct access not allowed');
}

$current_page = basename($_SERVER['PHP_SELF']);
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$is_index_page = $current_page === 'index.php';
?>

<nav class="navbar">
    <div class="nav-container <?= $is_index_page ? 'index-layout' : '' ?>">
        <!-- Brand Logo (Far left on index, centered on other pages) -->
        <div class="nav-brand">
            <a href="<?= url('/index.php') ?>">
                <img src="<?= url('/img/NABTA.png') ?>" alt="Nabta Logo">
                <span>Nabta</span>
            </a>
        </div>

        <!-- Navigation Links -->
        <button class="mobile-menu-btn" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="nav-links">
            <li>
                <a href="<?= url('/index.php') ?>" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>

            <li class="dropdown">
                <a href="<?= url('/plants.php') ?>" class="<?= $current_page === 'plants.php' ? 'active' : '' ?>">
                    <i class="fas fa-leaf"></i> Plants <i class="fas fa-chevron-down"></i>
                </a>
                <div class="dropdown-content">
                    <a href="<?= url('/plants.php?category=herbs') ?>"><i class="fas fa-leaf"></i> Herbs</a>
                    <a href="<?= url('/plants.php?category=vegetables') ?>"><i class="fas fa-carrot"></i> Vegetables</a>
                    <a href="<?= url('/plants.php?category=flowers') ?>"><i class="fas fa-spa"></i> Flowers</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= url('/plants.php') ?>"><i class="fas fa-store"></i> All Plants</a>
                </div>
            </li>

            <li>
                <a href="<?= url('/products.php') ?>" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">
                    <i class="fas fa-box-open"></i> Products
                </a>
            </li>

            <li>
                <a href="<?= url('/about.php') ?>" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">
                    <i class="fas fa-info-circle"></i> About Us
                </a>
            </li>

            <li>
                <a href="<?= url('/contact.php') ?>" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </li>

            <li>
                <a href="<?= url('/cart.php') ?>" class="<?= $current_page === 'cart.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count animate-bounce"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <!-- User Section -->
        <div class="nav-user-section">
            <?php if (is_logged_in()): ?>
                <div class="user-dropdown">
                    <button class="user-dropdown-toggle">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['user']['username'], 0, 1)) ?>
                        </div>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content user-dropdown-menu">
                        <a href="<?= url('/dashboard.php') ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="<?= url('/account.php') ?>"><i class="fas fa-user-cog"></i> Account Settings</a>
                        <a href="<?= url('/subscriptions.php') ?>"><i class="fas fa-calendar-check"></i> Subscriptions</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= url('/logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?= url('/login.php') ?>" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= url('/register.php') ?>" class="btn-signup">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('active');
        this.innerHTML = navLinks.classList.contains('active') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992) {
            if (!e.target.closest('.nav-container')) {
                document.querySelector('.nav-links')?.classList.remove('active');
                document.querySelector('.mobile-menu-btn').innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
    });

    // Dropdown toggle for user menu
    document.querySelector('.user-dropdown-toggle')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = this.closest('.user-dropdown').querySelector('.user-dropdown-menu');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    });

    // Cart count animation
    function animateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.classList.add('animate-bounce');
            setTimeout(() => cartCount.classList.remove('animate-bounce'), 1000);
        }
    }

    // Listen for cart updates from other pages
    document.addEventListener('cartUpdated', function(e) {
        const newCount = e.detail.count;
        const cartLinks = document.querySelectorAll('a[href*="cart.php"]');
        
        cartLinks.forEach(link => {
            let countBadge = link.querySelector('.cart-count');
            
            if (newCount > 0) {
                if (!countBadge) {
                    countBadge = document.createElement('span');
                    countBadge.className = 'cart-count animate-bounce';
                    link.appendChild(countBadge);
                }
                countBadge.textContent = newCount;
                countBadge.classList.add('animate-bounce');
                setTimeout(() => countBadge.classList.remove('animate-bounce'), 1000);
            } else if (countBadge) {
                link.removeChild(countBadge);
            }
        });
    });

    // Function to trigger cart update (can be called from any page)
    function updateGlobalCartCount(count) {
        const event = new CustomEvent('cartUpdated', {
            detail: { count: count }
        });
        document.dispatchEvent(event);
    }
</script>

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
        --shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar {
        background-color: var(--white);
        box-shadow: var(--shadow);
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
        position: relative;
    }

    /* Index page specific layout */
    .nav-container.index-layout {
        justify-content: flex-start;
    }
    
    .nav-container.index-layout .nav-brand {
        order: 1;
        margin-right: auto;
    }
    
    .nav-container.index-layout .nav-links {
        order: 2;
        margin-left: 20px;
    }
    
    .nav-container.index-layout .nav-user-section {
        order: 3;
        margin-left: auto;
    }

    /* Brand Logo Styling */
    .nav-brand {
        position: absolute;
        left: 10%;
        transform: translateX(-50%);
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
        height: auto;
        max-height: 50px;
        width: auto;
        max-width: 100px;
        object-fit: contain;
        margin-right: 10px;
    }

    /* Navigation Links Styling */
    .nav-links {
        display: flex;
        list-style: none;
        margin-left: auto;
    }

    .nav-links li {
        margin-left: 20px;
        position: relative;
    }

    .nav-links a {
        text-decoration: none;
        color: var(--dark);
        font-weight: 500;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 4px;
    }

    .nav-links a:hover {
        color: var(--primary);
        background-color: var(--light);
    }

    .nav-links a.active {
        color: var(--primary);
        font-weight: 600;
        background-color: rgba(46, 125, 50, 0.1);
    }

    /* Dropdown Styling */
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
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dropdown-content a:hover {
        background-color: var(--light);
        color: var(--primary);
        padding-left: 20px;
    }

    .dropdown-divider {
        height: 1px;
        background-color: #eee;
        margin: 5px 0;
    }

    /* User Section Styling */
    .nav-user-section {
        display: flex;
        align-items: center;
    }

    .user-dropdown {
        position: relative;
        left: 30%;
    }

    .user-dropdown-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 4px;
        transition: all 0.3s;
    }

    .user-dropdown-toggle:hover {
        background-color: var(--light);
    }

    .user-name {
        font-weight: 500;
        color: var(--dark);
        display: none;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
        
    }

    .user-dropdown-menu {
        right: 0;
        left: auto;
        min-width: 220px;
    }

    .user-dropdown-menu a {
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .auth-buttons {
        display: flex;
        gap: 10px;
    }

    /* Cart Styling */
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

    .animate-bounce {
        animation: bounce 0.5s;
    }

    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.3); }
    }

    /* Button Styling */
    .btn-login {
        padding: 8px 15px;
        background: transparent;
        color: var(--dark);
        border: 1px solid var(--gray);
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
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
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-signup:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--dark);
        background: none;
        border: none;
        padding: 10px;
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .nav-container {
            justify-content: space-between;
        }
        
        .nav-container.index-layout {
            justify-content: space-between;
        }
        
        .nav-container.index-layout .nav-brand,
        .nav-brand {
            position: static;
            transform: none;
            order: 2;
            margin: 0 auto;
        }
        
        .nav-container.index-layout .nav-links,
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
            gap: 15px;
            margin-left: 0;
        }

        .nav-links.active {
            left: 0;
        }

        .nav-links li {
            margin-left: 0;
            width: 100%;
        }

        .nav-links a {
            padding: 12px 15px;
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
            order: 3;
        }
        
        .nav-container.index-layout .nav-user-section,
        .nav-user-section {
            order: 1;
            margin-left: 0;
        }

        .user-name {
            display: inline;
        }
    }

    @media (max-width: 576px) {
        .nav-brand img {
            max-height: 40px;
        }

        .nav-brand span {
            font-size: 1.5rem;
        }

        .auth-buttons {
            gap: 5px;
        }
        
        .btn-login, .btn-signup {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }
</style>

</html>