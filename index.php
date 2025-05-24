<?php
require_once __DIR__ . '/config/init.php';

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
    <link rel="icon" type="image/png" href="img/NABTA.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
         body.index-page .nav-brand {
            position: static !important;
            transform: none !important;
            margin-right: auto;
        }
        
        body.index-page .nav-links {
            margin-left: 0;
        }
        
        /* Add this to make sure the body has the index-page class */
        body {
            padding-top: 80px;
        }
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #81C784;
            --accent-color: #FF8F00;
            --light-green: #E8F5E9;
            --dark-text: #1B5E20;
            --text-muted: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            padding-top: 80px; /* Account for fixed navbar */
            background-color: #f9f9f9;
        }
        
        /* Product Card Styling */
        .product-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 25px;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-img-container {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .product-body {
            padding: 20px;
        }
        
        .product-title {
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .product-description {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .product-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .add-to-cart-btn {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .add-to-cart-btn:hover {
            background-color: var(--dark-text);
            transform: translateY(-2px);
        }
        
        .add-to-cart-btn i {
            margin-right: 5px;
        }
        
        /* Section Styling */
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            position: relative;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .section-subtitle {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 50px;
            font-size: 1.1rem;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.8)), url('img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            position: relative;
        }
        
        .hero-content {
            max-width: 600px;
        }
        
        .hero-title {
            font-weight: 800;
            color: var(--dark-text);
            margin-bottom: 20px;
            font-size: 2.8rem;
        }
        
        .hero-text {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: var(--dark-text);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .hero {
                padding: 80px 0;
            }
            
            .hero-title {
                font-size: 2.2rem;
            }
            
            .section {
                padding: 60px 0;
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                text-align: center;
                padding: 60px 0;
            }
            
            .hero-content {
                margin: 0 auto;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .product-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="index-page"> <!-- Add index-page class to body -->
    <!-- Include your navbar -->
    <?php include __DIR__ . '/includes/navbar.php'; ?>


    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Smart Gardening for Everyone</h1>
                        <p class="hero-text">Bring nature indoors with Nabta's innovative smart gardening solutions. Grow fresh herbs, vegetables, and flowers all year round with minimal effort.</p>
                        <div class="d-flex gap-3">
                            <a href="plants.php" class="btn btn-primary btn-lg px-4">Shop Plants</a>
                            <a href="#how-it-works" class="btn btn-outline-primary btn-lg px-4">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <video class="img-fluid rounded shadow-lg" style="max-width: 80%;" autoplay muted loop>
                        <source src="img/smart-garden-video.mp4" type="video/mp4">
                        <img src="img/smart-garden-3.jpg" alt="Nabta Smart Garden" class="img-fluid rounded shadow-lg">
                    </video>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Plants Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Plants</h2>
            <p class="section-subtitle">Discover our most popular plant pods, perfect for your smart garden.</p>
            <div class="row">
                <?php foreach ($featuredPlants as $plant): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-container">
                            <img src="<?php echo $plant['image_url']; ?>" class="product-img" alt="<?php echo $plant['name']; ?>">
                            <span class="product-badge"><?php echo ucfirst($plant['category']); ?></span>
                        </div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo $plant['name']; ?></h3>
                            <p class="product-description"><?php echo $plant['short_description']; ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($plant['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?plant_id=<?php echo $plant['id']; ?>" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="plants.php" class="btn btn-outline-primary btn-lg">View All Plants</a>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="section bg-light" id="how-it-works">
        <div class="container">
            <h2 class="section-title">How Nabta Works</h2>
            <p class="section-subtitle">Growing plants at home has never been easier</p>
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="text-center p-4 h-100 bg-white rounded shadow-sm">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-seedling fa-3x text-primary"></i>
                        </div>
                        <h4 class="mb-3">Choose Your Plants</h4>
                        <p class="mb-0">Select from our wide variety of herbs, vegetables, and flowers.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="text-center p-4 h-100 bg-white rounded shadow-sm">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-fill-drip fa-3x text-primary"></i>
                        </div>
                        <h4 class="mb-3">Fill With Water</h4>
                        <p class="mb-0">Add water to the reservoir. Our sensors tell you when it's time to refill.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="text-center p-4 h-100 bg-white rounded shadow-sm">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-plug fa-3x text-primary"></i>
                        </div>
                        <h4 class="mb-3">Plug In</h4>
                        <p class="mb-0">Connect your garden to power and let the smart system do the rest.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="text-center p-4 h-100 bg-white rounded shadow-sm">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-leaf fa-3x text-primary"></i>
                        </div>
                        <h4 class="mb-3">Watch Them Grow</h4>
                        <p class="mb-0">Enjoy fresh herbs and vegetables with minimal effort.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Smart Gardens Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Smart Gardens</h2>
            <p class="section-subtitle">Discover our range of innovative indoor garden systems</p>
            <div class="row">
                <?php foreach ($smartPots as $pot): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-container">
                            <img src="<?php echo $pot['image_url']; ?>" class="product-img" alt="<?php echo $pot['name']; ?>">
                            <span class="product-badge">Smart Pot</span>
                        </div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo $pot['name']; ?></h3>
                            <p class="product-description"><?php echo $pot['description']; ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($pot['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?product_id=<?php echo $pot['id']; ?>" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart"></i> Add
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
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-subtitle">Check out our latest plant pods just added to our collection</p>
            <div class="row">
                <?php foreach ($newPlants as $plant): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-container">
                            <img src="<?php echo $plant['image_url']; ?>" class="product-img" alt="<?php echo $plant['name']; ?>">
                            <span class="product-badge">New</span>
                        </div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo $plant['name']; ?></h3>
                            <p class="product-description"><?php echo $plant['short_description']; ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($plant['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?plant_id=<?php echo $plant['id']; ?>" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart"></i> Add
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
    <section class="section">
        <div class="container">
            <h2 class="section-title">Smart Sensors</h2>
            <p class="section-subtitle">Enhance your gardening experience with our smart monitoring tools</p>
            <div class="row">
                <?php foreach ($sensors as $sensor): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-container">
                            <img src="<?php echo $sensor['image_url']; ?>" class="product-img" alt="<?php echo $sensor['name']; ?>">
                            <span class="product-badge">Sensor</span>
                        </div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo $sensor['name']; ?></h3>
                            <p class="product-description"><?php echo $sensor['description']; ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($sensor['price'], 2); ?></span>
                                <a href="actions/add_to_cart.php?product_id=<?php echo $sensor['id']; ?>" class="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
                
                const button = $(this);
                const href = button.attr('href');
                const urlParams = new URLSearchParams(href.split('?')[1]);
                const plantId = urlParams.get('plant_id');
                const productId = urlParams.get('product_id');
                const quantity = 1; // Default quantity
                
                // Add loading state
                button.html('<i class="fas fa-spinner fa-spin"></i> Adding');
                button.prop('disabled', true);
                
                // Prepare data for POST request
                const formData = new FormData();
                if (plantId) formData.append('plant_id', plantId);
                if (productId) formData.append('product_id', productId);
                formData.append('quantity', quantity);
                
                $.ajax({
                    url: 'actions/add_to_cart.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Update cart count
                            $('.cart-count').text(response.cart_count).addClass('animate-bounce');
                            setTimeout(() => {
                                $('.cart-count').removeClass('animate-bounce');
                            }, 500);
                            
                            // Show success state
                            button.html('<i class="fas fa-check"></i> Added');
                            setTimeout(() => {
                                button.html('<i class="fas fa-shopping-cart"></i> Add');
                                button.prop('disabled', false);
                            }, 1500);
                        } else {
                            throw new Error(response.message || 'Failed to add to cart');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        button.html('<i class="fas fa-shopping-cart"></i> Add');
                        button.prop('disabled', false);
                        alert(response.message || 'Error adding item to cart. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>