<?php
$current_year = date('Y');
?>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Footer Columns -->
        <div class="footer-grid">
            <!-- Company Info -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="./img/NABTA.png" alt="nabta" width="150">
                </div>
                <p class="footer-about">Smart indoor gardens that make growing fresh plants effortless year-round.</p>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="plants.php">Shop Plants</a></li>
                    <li><a href="subscription.php">Subscriptions</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-column">
                <h3 class="footer-title">Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="faq.php">FAQs</a></li>
                    <li><a href="shipping.php">Shipping Policy</a></li>
                    <li><a href="returns.php">Returns & Refunds</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="footer-column">
                <h3 class="footer-title">Stay Updated</h3>
                <p class="newsletter-desc">Subscribe for tips and exclusive offers</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>
                <div class="payment-methods">
                    <img src="./img/visa.png" alt="Visa">
                    <img src="./img/card.png" alt="Mastercard">
                    <img src="./img/paypal.png" alt="PayPal">
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo $current_year; ?>Nabta Corp Egypt. All rights reserved.</p>
            <div class="footer-legal">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="cookies.php">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<style>
    /* ===== Footer Styles ===== */
    :root {
        --primary: #2e7d32;
        --primary-light: #4CAF50;
        --dark: #263238;
        --light: #f5f5f6;
        --white: #ffffff;
        --gray: #607d8b;
    }

    .site-footer {
        background-color: var(--dark);
        color: var(--white);
        padding: 60px 0 0;
        font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-column {
        padding: 0 15px;
    }

    .footer-logo img {
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
    }

    .footer-about {
        color: #cfd8dc;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .footer-title {
        color: var(--white);
        font-size: 1.2rem;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background-color: var(--primary);
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: #cfd8dc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-links a:hover {
        color: var(--primary-light);
        padding-left: 5px;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--white);
        transition: all 0.3s;
    }

    .social-icon:hover {
        background-color: var(--primary);
        transform: translateY(-3px);
    }

    .newsletter-form {
        display: flex;
        margin-top: 20px;
    }

    .newsletter-form input {
        flex: 1;
        padding: 10px 15px;
        border: none;
        border-radius: 4px 0 0 4px;
        font-size: 0.9rem;
    }

    .newsletter-btn {
        background-color: var(--primary);
        color: var(--white);
        border: none;
        padding: 0 20px;
        border-radius: 0 4px 4px 0;
        cursor: pointer;
        transition: background 0.3s;
    }

    .newsletter-btn:hover {
        background-color: var(--primary-light);
    }

    .newsletter-desc {
        color: #cfd8dc;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .payment-methods {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .payment-methods img {
        height: 25px;
        width: auto;
    }

    .footer-bottom {
        border-top: 1px solid #37474f;
        padding: 20px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        color: #90a4ae;
        font-size: 0.9rem;
    }

    .footer-legal {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .footer-legal a {
        color: #90a4ae;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-legal a:hover {
        color: var(--primary-light);
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .footer-column {
            padding: 0;
        }
        
        .newsletter-form {
            flex-direction: column;
        }
        
        .newsletter-form input {
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .newsletter-btn {
            border-radius: 4px;
            padding: 10px;
        }
        
        .footer-bottom {
            flex-direction: column;
        }
        
        .footer-legal {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>

<!-- Font Awesome for icons (optional) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">