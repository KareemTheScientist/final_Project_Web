<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Nabta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #388E3C;
            --light-green: #8BC34A;
            --dark-text: #333333;
            --light-text: #666666;
            --white: #FFFFFF;
            --light-bg: #F9F9F9;
            --border-color: #EEEEEE;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            color: var(--dark-text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header styles will be imported from your existing navbar.php */
        
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/FinalProject/final_Project_Web/img/about-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-align: center;
        }
        
        .hero-content {
            max-width: 800px;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 30px;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: var(--light-text);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .mission {
            background-color: var(--light-bg);
        }
        
        .mission-content {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .mission-text {
            flex: 1;
            padding-right: 30px;
        }
        
        .mission-image {
            flex: 1;
            min-width: 300px;
        }
        
        .mission-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .values {
            background-color: var(--white);
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .value-card {
            background-color: var(--light-bg);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .value-card:hover {
            transform: translateY(-10px);
        }
        
        .value-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .value-card h3 {
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .team {
            background-color: var(--light-bg);
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .team-member {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .team-image {
            height: 250px;
            overflow: hidden;
        }
        
        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .team-info {
            padding: 20px;
            text-align: center;
        }
        
        .team-info h3 {
            margin-bottom: 5px;
            color: var(--secondary-color);
        }
        
        .team-info p {
            color: var(--light-text);
            margin-bottom: 15px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-links a {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }
        
        .social-links a:hover {
            color: var(--secondary-color);
        }
        
        .journey {
            background-color: var(--white);
        }
        
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: var(--light-green);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
        }
        
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            background-color: var(--white);
            border: 4px solid var(--primary-color);
            border-radius: 50%;
            top: 15px;
            z-index: 1;
        }
        
        .left {
            left: 0;
        }
        
        .right {
            left: 50%;
        }
        
        .left::after {
            right: -17px;
        }
        
        .right::after {
            left: -17px;
        }
        
        .timeline-content {
            padding: 20px;
            background-color: var(--light-bg);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-content h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .cta {
            text-align: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/FinalProject/final_Project_Web/img/cta-bg.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 100px 0;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        /* Media queries for responsiveness */
        @media screen and (max-width: 992px) {
            .mission-content {
                flex-direction: column;
            }
            
            .mission-text {
                padding-right: 0;
                margin-bottom: 30px;
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item::after {
                left: 15px;
            }
            
            .right {
                left: 0;
            }
        }
        
        @media screen and (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .section {
                padding: 60px 0;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        @media screen and (max-width: 576px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section {
                padding: 40px 0;
            }
            
            .section-title h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Your navbar will be included here -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content container">
            <h1>Bringing Nature into Smart Homes</h1>
            <p>At Nabta, we're on a mission to connect people with nature through innovative smart gardening solutions.</p>
        </div>
    </section>

    <!-- Our Mission Section -->
    <section class="section mission">
        <div class="container">
            <div class="section-title">
                <h2>Our Mission</h2>
                <p>We believe everyone deserves to experience the joy of growing plants, regardless of space or expertise.</p>
            </div>
            <div class="mission-content">
                <div class="mission-text">
                    <p>Nabta was born from a simple observation: in our increasingly digital world, our connection to nature has diminished. Many urban dwellers lack the space, time, or knowledge to cultivate greenery in their living spaces.</p>
                    <p>We set out to solve this problem by developing smart gardening solutions that make growing plants effortless and accessible to everyone. Our technology takes care of the complicated aspects of plant care, allowing you to enjoy the benefits of living with plants without the guesswork.</p>
                    <p>Whether you're looking to grow fresh herbs for cooking, beautiful flowers to brighten your space, or vegetables for healthy eating, our smart systems ensure success by providing the perfect growing conditions for each plant.</p>
                </div>
                <div class="mission-image">
                    <img src="/img/about1.png" alt="Smart garden with various plants">
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="section values">
        <div class="container">
            <div class="section-title">
                <h2>Our Values</h2>
                <p>The principles that guide everything we do at Nabta.</p>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>We continuously explore new technologies and methodologies to make plant care simpler and more efficient.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <h3>Sustainability</h3>
                    <p>We're committed to environmental responsibility in our products, packaging, and business practices.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community</h3>
                    <p>We foster a community of plant enthusiasts sharing knowledge and experiences around growing plants.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Education</h3>
                    <p>We believe in empowering people with knowledge about plants, nutrition, and sustainable living.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team Section -->
    <section class="section team">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Team</h2>
                <p>The passionate individuals behind Nabta's mission.</p>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-image">
                        <img src="/api/placeholder/400/400" alt="Kareem - Founder & CEO">
                    </div>
                    <div class="team-info">
                        <h3>Kareem</h3>
                        <p>Founder & CEO</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="/api/placeholder/400/400" alt="Ahmed - Head of Product">
                    </div>
                    <div class="team-info">
                        <h3>Ahmed</h3>
                        <p>Head of Product</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="/api/placeholder/400/400" alt="Sara - Plant Scientist">
                    </div>
                    <div class="team-info">
                        <h3>Sara</h3>
                        <p>Plant Scientist</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="team-image">
                        <img src="/api/placeholder/400/400" alt="Mohamed - Tech Lead">
                    </div>
                    <div class="team-info">
                        <h3>Mohamed</h3>
                        <p>Tech Lead</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Journey Section -->
    <section class="section journey">
        <div class="container">
            <div class="section-title">
                <h2>Our Journey</h2>
                <p>How Nabta evolved from an idea to a growing smart gardening company.</p>
            </div>
            <div class="timeline">
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <h3>2023</h3>
                        <p>Nabta was founded with a vision to bring nature into modern homes through technology.</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <h3>2024</h3>
                        <p>Launch of our first smart garden product line, introducing innovative self-watering technology.</p>
                    </div>
                </div>
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <h3>2024</h3>
                        <p>Expanded our product range to include smart sensors and specialized plant food solutions.</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <h3>2025</h3>
                        <p>Launched our e-commerce platform with subscription services for ongoing plant care.</p>
                    </div>
                </div>
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <h3>Present</h3>
                        <p>Continuing to innovate with new smart gardening solutions and expanding our plant varieties.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta">
        <div class="container">
            <h2>Join Our Green Revolution</h2>
            <p>Experience the joy of growing plants with smart technology. Start your journey to a greener home today.</p>
            <a href="/FinalProject/final_Project_Web/plants.php" class="btn">Shop Plants</a>
        </div>
    </section>

    <!-- Your footer will be included here -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>