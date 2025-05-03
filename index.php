<?php
require_once 'config/init.php';

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);

// Fetch featured plants
$featuredPlantsQuery = "SELECT * FROM plants WHERE is_featured = 1 AND active = 1 LIMIT 4";
$featuredPlantsStmt = $pdo->query($featuredPlantsQuery);
$featuredPlants = $featuredPlantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch new plants
$newPlantsQuery = "SELECT * FROM plants WHERE is_new = 1 AND active = 1 LIMIT 4";
$newPlantsStmt = $pdo->query($newPlantsQuery);
$newPlants = $newPlantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch smart pots
$smartPotsQuery = "SELECT * FROM products WHERE category = 'pot' LIMIT 4";
$smartPotsStmt = $pdo->query($smartPotsQuery);
$smartPots = $smartPotsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured sensors
$sensorsQuery = "SELECT * FROM products WHERE category = 'sensor' LIMIT 4";
$sensorsStmt = $pdo->query($sensorsQuery);
$sensors = $sensorsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nabta - Smart Gardening Solutions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #81C784;
            --accent-color: #FF8F00;
            --light-green: #E8F5E9;
            --dark-text: #1B5E20;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .hero {
            background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.7)), url('img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 6rem 0;
            position: relative;
        }
        
        .section-title {
            color: var(--dark-text);
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .card-title {
            color: var(--dark-text);
            font-weight: 600;
        }
        
        .price {
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .feature-box {
            text-align: center;
            padding: 2rem;
            background-color: var(--light-green);
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .newsletter {
            background-color: var(--light-green);
            padding: 3rem 0;
        }
        
        .social-icons a {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin: 0 0.5rem;
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
        }
        
        .category-badge {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .add-to-cart-btn {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background-color: var(--dark-text);
        }
        
        .benefits-section {
            padding: 4rem 0;
            background-color: #f9f9f9;
        }
        
        .testimonial {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin: 1rem 0;
        }
        
        .testimonial-quote {
            font-style: italic;
            color: #555;
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .banner {
            background-color: var(--accent-color);
            color: white;
            padding: 0.5rem 0;
            text-align: center;
            font-weight: 500;
        }

        /* Navbar Wrapper Styles */
        .navbar-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 30px;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow: hidden;
            background-color: white;
        }

        .navbar-wrapper:hover {
            height: 80px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Navbar Styles */
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            height: 80px;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand a {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-brand img {
            height: auto;
            max-height: 60px;
            width: auto;
            max-width: 100px;
            object-fit: contain;
            margin-right: 15px;
            transition: all 0.3s ease;
        }

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
            color: var(--dark-text);
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
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
            color: var(--dark-text);
            transition: all 0.3s;
        }

        .dropdown-content a:hover {
            background-color: var(--light-green);
            color: var(--primary-color);
            padding-left: 20px;
        }

        .nav-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary-color);
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

        .btn-login {
            padding: 8px 15px;
            background: transparent;
            color: var(--dark-text);
            border: 1px solid var(--gray);
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--light-green);
        }

        .btn-signup {
            padding: 8px 15px;
            background: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-signup:hover {
            background: var(--dark-text);
            border-color: var(--dark-text);
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-text);
            font-weight: 500;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .mobile-menu-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-text);
            background: none;
            border: none;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .navbar-wrapper {
                height: auto !important;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .navbar {
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
            }
            
            .nav-links {
                position: static;
                flex-direction: column;
                width: 100%;
                padding: 0;
                margin-top: 15px;
                display: none;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links li {
                margin: 10px 0;
            }
            
            .dropdown-content {
                position: static;
                box-shadow: none;
                display: none;
                padding-left: 20px;
            }
            
            .mobile-menu-btn {
                display: block;
                position: absolute;
                right: 20px;
                top: 15px;
            }
            
            .nav-brand {
                width: 100%;
            }
            
            .nav-user-section {
                margin-top: 15px;
                width: 100%;
                justify-content: flex-start;
                display: none;
            }
            
            .nav-links.active + .nav-user-section {
                display: flex;
            }

            .hero {
                padding: 40px 20px;
            }

            .hero h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .nav-user-section {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-login, .btn-signup {
                width: 100%;
                text-align: center;
            }

            .hero {
                padding: 30px 15px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
    <!-- Banner for promotions -->
    

    <!-- Include your custom navbar -->
   

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-4">Smart Gardening for Everyone</h1>
                    <p class="lead mb-4">Bring nature indoors with Nabta's innovative smart gardening solutions. Grow fresh herbs, vegetables, and flowers all year round with minimal effort.</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="plants.php" class="btn btn-primary btn-lg px-4 me-md-2">Shop Plants</a>
                        <a href="#how-it-works" class="btn btn-outline-primary btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <video class="img-fluid rounded shadow-lg" style="max-width: 80%;" autoplay muted loop>
                        <source src="img/smart-garden-video.mp4" type="video/mp4">
                        <!-- Fallback for browsers that don't support video -->
                        <img src="img/smart-garden-3.jpg" alt="Nabta Smart Garden" class="img-fluid rounded shadow-lg">
                    </video>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Plants Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Featured Plants</h2>
            <p class="mb-4">Discover our most popular plant pods, perfect for your smart garden.</p>
            <div class="row">
                <?php foreach ($featuredPlants as $plant): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <span class="category-badge"><?php echo ucfirst($plant['category']); ?></span>
                        <img src="<?php echo $plant['image_url']; ?>" class="card-img-top" alt="<?php echo $plant['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $plant['name']; ?></h5>
                            <p class="card-text"><?php echo $plant['short_description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">$<?php echo number_format($plant['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?id=<?php echo $plant['id']; ?>&type=plant" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-1"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="plants.php" class="btn btn-outline-primary">View All Plants</a>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="py-5 bg-light" id="how-it-works">
        <div class="container">
            <h2 class="section-title text-center">How Nabta Works</h2>
            <p class="text-center mb-5">Growing plants at home has never been easier</p>
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h4>Choose Your Plants</h4>
                        <p>Select from our wide variety of herbs, vegetables, and flowers.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-fill-drip"></i>
                        </div>
                        <h4>Fill With Water</h4>
                        <p>Add water to the reservoir. Our sensors tell you when it's time to refill.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-plug"></i>
                        </div>
                        <h4>Plug In</h4>
                        <p>Connect your garden to power and let the smart system do the rest.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4>Watch Them Grow</h4>
                        <p>Enjoy fresh herbs and vegetables with minimal effort.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Smart Gardens Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Smart Gardens</h2>
            <p class="mb-4">Discover our range of innovative indoor garden systems.</p>
            <div class="row">
                <?php foreach ($smartPots as $pot): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?php echo $pot['image_url']; ?>" class="card-img-top" alt="<?php echo $pot['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $pot['name']; ?></h5>
                            <p class="card-text"><?php echo $pot['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">$<?php echo number_format($pot['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?id=<?php echo $pot['id']; ?>&type=product" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-1"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <p class="mb-4">Check out our latest plant pods just added to our collection.</p>
            <div class="row">
                <?php foreach ($newPlants as $plant): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <span class="category-badge"><?php echo ucfirst($plant['category']); ?></span>
                        <img src="<?php echo $plant['image_url']; ?>" class="card-img-top" alt="<?php echo $plant['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $plant['name']; ?></h5>
                            <p class="card-text"><?php echo $plant['short_description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">$<?php echo number_format($plant['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?id=<?php echo $plant['id']; ?>&type=plant" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-1"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Smart Garden Sensors -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Smart Sensors</h2>
            <p class="mb-4">Enhance your gardening experience with our smart monitoring tools.</p>
            <div class="row">
                <?php foreach ($sensors as $sensor): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?php echo $sensor['image_url']; ?>" class="card-img-top" alt="<?php echo $sensor['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $sensor['name']; ?></h5>
                            <p class="card-text"><?php echo $sensor['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">$<?php echo number_format($sensor['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?id=<?php echo $sensor['id']; ?>&type=product" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-1"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <h2 class="section-title text-center">Why Choose Nabta?</h2>
            <div class="row mb-5">
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-hand-holding-water feature-icon"></i>
                        <h4>Self-Watering System</h4>
                        <p>Our smart pots water your plants automatically, so you don't have to worry about over or under-watering.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-lightbulb feature-icon"></i>
                        <h4>Built-in Grow Lights</h4>
                        <p>Specialized LED grow lights provide the perfect spectrum for plant growth, even in dark corners.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h4>Smart Monitoring</h4>
                        <p>Track your plants' health with our smart sensors and get notifications when they need attention.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-leaf feature-icon"></i>
                        <h4>Organic Nutrients</h4>
                        <p>Our plant pods come with all the organic nutrients your plants need for healthy growth.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-recycle feature-icon"></i>
                        <h4>Sustainable Materials</h4>
                        <p>Our products are made with eco-friendly materials, reducing environmental impact.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-users feature-icon"></i>
                        <h4>Community Support</h4>
                        <p>Join our gardening community to share tips, experiences, and grow together.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title text-center">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial">
                        <p class="testimonial-quote">"I've always killed every plant I've owned until I discovered Nabta. Now I have fresh herbs year-round with zero gardening knowledge!"</p>
                        <p class="testimonial-author">— Sarah K., Cairo</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p class="testimonial-quote">"The smart garden has become the centerpiece of my kitchen. My kids love watching the plants grow, and I love cooking with fresh herbs."</p>
                        <p class="testimonial-author">— Mohammed A., Alexandria</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p class="testimonial-quote">"As someone with a busy schedule, Nabta has been a game-changer. The self-watering system means I never come home to dead plants anymore."</p>
                        <p class="testimonial-author">— Laila T., Giza</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3>Join Our Green Community</h3>
                    <p class="mb-4">Subscribe to our newsletter for gardening tips, new product announcements, and exclusive offers.</p>
                    <form class="row g-3 justify-content-center">
                        <div class="col-auto">
                            <input type="email" class="form-control" placeholder="Your email address">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        $(document).ready(function() {
            // Add to cart functionality
            $('.add-to-cart-btn').click(function(e) {
                e.preventDefault();
                
                const href = $(this).attr('href');
                
                $.ajax({
                    url: href,
                    type: 'GET',
                    success: function(response) {
                        // Update cart count
                        $.get('actions/get_cart_count.php', function(data) {
                            $('#cart-count').text(data);
                        });
                        
                        // Show notification
                        alert('Item added to cart!');
                    },
                    error: function() {
                        alert('Error adding item to cart. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>