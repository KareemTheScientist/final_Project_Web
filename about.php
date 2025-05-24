<?php
require_once __DIR__ . '/config/init.php';

$page_title = "About Us";
include __DIR__ . '/includes/navbar.php';

// Fetch about us content from database
try {
    $stmt = $pdo->query("SELECT content FROM about_us WHERE id = 1");
    $about_content = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error fetching about content: " . $e->getMessage());
    $about_content = "Welcome to Nabta! We are passionate about plant care, smart gardening, and sustainable indoor environments.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="about-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="animate-on-scroll">Welcome to Nabta</h1>
            <p class="animate-on-scroll">Your Journey to Greener Living Starts Here</p>
        </div>
        <div class="hero-image">
            <img src="<?= url('/img/about1.png') ?>" alt="Nabta Plants" class="animate-on-scroll">
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="mission-content">
            <h2 class="animate-on-scroll">Our Mission</h2>
            <p class="animate-on-scroll"><?= htmlspecialchars($about_content) ?></p>
        </div>
        <div class="mission-stats">
            <div class="stat-card animate-on-scroll">
                <i class="fas fa-leaf"></i>
                <h3>1000+</h3>
                <p>Plants Delivered</p>
            </div>
            <div class="stat-card animate-on-scroll">
                <i class="fas fa-users"></i>
                <h3>500+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-card animate-on-scroll">
                <i class="fas fa-seedling"></i>
                <h3>50+</h3>
                <p>Plant Varieties</p>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <h2 class="section-title animate-on-scroll">Our Values</h2>
        <div class="values-grid">
            <div class="value-card animate-on-scroll">
                <i class="fas fa-leaf"></i>
                <h3>Sustainability</h3>
                <p>Committed to eco-friendly practices and sustainable gardening solutions.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <i class="fas fa-heart"></i>
                <h3>Passion</h3>
                <p>Dedicated to sharing our love for plants and gardening with everyone.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <i class="fas fa-lightbulb"></i>
                <h3>Innovation</h3>
                <p>Constantly exploring new ways to make plant care easier and smarter.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <i class="fas fa-hands-helping"></i>
                <h3>Community</h3>
                <p>Building a supportive community of plant enthusiasts and gardeners.</p>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-section">
        <h2 class="section-title animate-on-scroll">Why Choose Nabta</h2>
        <div class="features-grid">
            <div class="feature-card animate-on-scroll">
                <i class="fas fa-truck"></i>
                <h3>Fast Delivery</h3>
                <p>Get your plants delivered within 2-3 business days, carefully packaged to ensure they arrive in perfect condition.</p>
            </div>
            <div class="feature-card animate-on-scroll">
                <i class="fas fa-shield-alt"></i>
                <h3>Plant Guarantee</h3>
                <p>30-day plant health guarantee. If your plant doesn't thrive, we'll replace it or refund your purchase.</p>
            </div>
            <div class="feature-card animate-on-scroll">
                <i class="fas fa-book"></i>
                <h3>Expert Care Guides</h3>
                <p>Access detailed care instructions and tips from our plant experts to help your plants flourish.</p>
            </div>
            <div class="feature-card animate-on-scroll">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Our plant care experts are always available to answer your questions and provide guidance.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content animate-on-scroll">
            <h2>Join Our Green Community</h2>
            <p>Subscribe to our newsletter for plant care tips and exclusive offers</p>
            <a href="<?= url('/subscriptions.php') ?>" class="btn btn-primary">
                <i class="fas fa-crown"></i> View Subscription Plans
            </a>
        </div>
    </section>
</div>

<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* Hero Section */
.hero-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: center;
    margin-bottom: 4rem;
    padding-top: 2rem;
}

.hero-content h1 {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.2rem;
    color: var(--gray);
}

.hero-image img {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

/* Mission Section */
.mission-section {
    text-align: center;
    margin-bottom: 4rem;
}

.mission-content {
    max-width: 800px;
    margin: 0 auto 3rem;
}

.mission-content h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.mission-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card i {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.stat-card h3 {
    font-size: 2rem;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

/* Values Section */
.values-section {
    margin-bottom: 4rem;
}

.section-title {
    text-align: center;
    color: var(--primary);
    margin-bottom: 3rem;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.value-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    text-align: center;
    transition: transform 0.3s;
}

.value-card:hover {
    transform: translateY(-5px);
}

.value-card i {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.value-card h3 {
    color: var(--dark);
    margin-bottom: 1rem;
}

/* Why Choose Us Section */
.why-choose-section {
    margin-bottom: 4rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    text-align: center;
    transition: transform 0.3s;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-card i {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.feature-card h3 {
    color: var(--dark);
    margin-bottom: 1rem;
}

.feature-card p {
    color: var(--gray);
    line-height: 1.6;
}

/* CTA Section */
.cta-section {
    background: var(--primary);
    color: white;
    padding: 4rem 2rem;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 2rem;
}

.cta-content h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.cta-content p {
    margin-bottom: 2rem;
    opacity: 0.9;
}

.btn-primary {
    background: white;
    color: var(--primary);
    padding: 1rem 2rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Animation Classes */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.6s ease-out;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .hero-content h1 {
        font-size: 2.5rem;
    }

    .mission-stats,
    .values-grid,
    .features-grid {
        grid-template-columns: 1fr;
    }

    .cta-section {
        padding: 3rem 1rem;
    }
}
</style>

<script>
// Intersection Observer for scroll animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, {
    threshold: 0.1
});

// Observe all elements with animate-on-scroll class
document.querySelectorAll('.animate-on-scroll').forEach((element) => {
    observer.observe(element);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>