<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nabta | Smart Indoor Gardens</title>
    <style>
        /* ===== Global Styles ===== */
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            text-align: center;
        }
        
        /* ===== Hero Section ===== */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1598880940080-ff9a29891b85');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            padding-top: 80px;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        /* ===== Products Section ===== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
        }
        
        .product-img {
            height: 200px;
            overflow: hidden;
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.1);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .product-info p {
            color: var(--gray);
            margin-bottom: 15px;
        }
        
        .price {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        /* ===== Benefits Section ===== */
        .benefits {
            background: var(--light);
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }
        
        .benefit-card {
            text-align: center;
            padding: 30px;
        }
        
        .benefit-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .benefit-card h3 {
            margin-bottom: 15px;
        }
        
        /* ===== Footer ===== */
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--primary-light);
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col li {
            margin-bottom: 10px;
        }
        
        .footer-col a {
            color: #cfd8dc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-col a:hover {
            color: white;
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #37474f;
            color: #cfd8dc;
        }
    </style>
</head>
<body>
    <!-- Navbar will be included from navbar.php -->
    <?php include 'navbar.php'; ?>

    <!-- ===== Hero Section ===== -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Grow Fresh Plants Effortlessly</h1>
                <p>Smart indoor gardens that take care of your plants automatically</p>
                <a href="#" class="btn">Shop Now</a>
            </div>
        </div>
    </section>

    <!-- ===== Smart Gardens Section ===== -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Smart Gardens</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/smartgarden3.jpg" alt="Smart Garden 3">
                    </div>
                    <div class="product-info">
                        <h3>Smart Garden 3</h3>
                        <p>Compact indoor garden for growing 3 plants</p>
                        <p class="price">$99.95</p>
                        <a href="#" class="btn">View Details</a>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/martgarden9.jpg" alt="Smart Garden 9">
                    </div>
                    <div class="product-info">
                        <h3>Smart Garden 9</h3>
                        <p>Larger garden for growing 9 plants at once</p>
                        <p class="price">$149.95</p>
                        <a href="#" class="btn">View Details</a>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/wallfarm.jpg" alt="Wall Farm">
                    </div>
                    <div class="product-info">
                        <h3>Wall Farm</h3>
                        <p>Vertical garden for growing 24 plants</p>
                        <p class="price">$299.95</p>
                        <a href="#" class="btn">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== Plant Pods Section ===== -->
    <section class="section" style="background: var(--light);">
        <div class="container">
            <h2 class="section-title">Plant Pods</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/basil.jpg" alt="Basil">
                    </div>
                    <div class="product-info">
                        <h3>Basil</h3>
                        <p>Fresh Italian basil for your kitchen</p>
                        <p class="price">$4.95</p>
                        <a href="./plants.php" class="btn">View Details</a>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/tomato.jpg" alt="Tomato">
                    </div>
                    <div class="product-info">
                        <h3>Cherry Tomato</h3>
                        <p>Sweet mini tomatoes for salads</p>
                        <p class="price">$5.95</p>
                        <a href="./plants.php" class="btn">View Details</a>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="./img/lettuce.jpg" alt="Lettuce">
                    </div>
                    <div class="product-info">
                        <h3>Butterhead Lettuce</h3>
                        <p>Tender lettuce for fresh salads</p>
                        <p class="price">$4.95</p>
                        <a href="./plants.php" class="btn">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== Benefits Section ===== -->
    <section class="section benefits">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">🌱</div>
                    <h3>No Green Thumb Needed</h3>
                    <p>Our smart technology handles watering and nutrients automatically</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">💧</div>
                    <h3>Save Water</h3>
                    <p>Uses 95% less water than traditional gardening</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">🌞</div>
                    <h3>Year-Round Growth</h3>
                    <p>Grow fresh herbs and veggies any time of year</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">🚚</div>
                    <h3>Free Shipping</h3>
                    <p>On all orders over $50 in the continental US</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== Footer ===== -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Smart Gardens</a></li>
                        <li><a href="#">Plant Pods</a></li>
                        <li><a href="#">Accessories</a></li>
                        <li><a href="#">Gift Cards</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Sustainability</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Shipping</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Connect</h3>
                    <ul>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">YouTube</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 nabta corporation. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>