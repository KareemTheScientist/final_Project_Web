<?php
session_start();
require_once __DIR__ . '/config/init.php';
require_auth();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("Session contents: " . print_r($_SESSION, true));

// Get cart items
$cartItems = getDBCartItems($pdo, $_SESSION['user_id']);
$cartCount = count($cartItems);

// Redirect if cart is empty
if ($cartCount === 0) {
    $_SESSION['error'] = "Your cart is empty. Please add items before checkout.";
    header('Location: cart.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['general'] = "Invalid form submission";
    }

    // Validate input
    $required_fields = ['first_name', 'last_name', 'address', 'city', 'country', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }

    // Validate email if provided
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address";
    }

    if (empty($errors)) {
        // Verify cart hasn't changed by comparing items
        $currentCart = getDBCartItems($pdo, $_SESSION['user_id']);
        $cartChanged = false;

        // Compare cart items
        if (count($currentCart) !== count($cartItems)) {
            $cartChanged = true;
        } else {
            foreach ($cartItems as $index => $item) {
                $currentItem = $currentCart[$index];
                if ($item['id'] !== $currentItem['id'] || 
                    $item['quantity'] !== $currentItem['quantity'] ||
                    $item['type'] !== $currentItem['type']) {
                    $cartChanged = true;
                    break;
                }
            }
        }

        if ($cartChanged) {
            error_log("Cart changed detected. Original: " . print_r($cartItems, true) . 
                     " Current: " . print_r($currentCart, true));
            $errors['general'] = "Your cart has changed. Please review your items before checkout.";
        } else {
            // Clear any existing checkout info
            unset($_SESSION['checkout_info']);
            
            // Set new checkout info
            $_SESSION['checkout_info'] = [
                'shipping' => [
                    'first_name' => htmlspecialchars(trim($_POST['first_name'])),
                    'last_name'  => htmlspecialchars(trim($_POST['last_name'])),
                    'email'      => htmlspecialchars(trim($_POST['email'])),
                    'address'    => htmlspecialchars(trim($_POST['address'])),
                    'city'       => htmlspecialchars(trim($_POST['city'])),
                    'country'    => htmlspecialchars(trim($_POST['country'])),
                    'payment_method' => htmlspecialchars(trim($_POST['payment_method']))
                ],
                'cart_items' => $currentCart
            ];

            // Debug log
            error_log("Setting checkout info in session: " . print_r($_SESSION['checkout_info'], true));

            // Force session write
            session_write_close();
            session_start();

            // Redirect to order confirmation
            header('Location: order-confirmation.php');
            exit;
        }
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pre-fill form values if set
$checkout_info = $_SESSION['checkout_info'] ?? null;
$shipping = $checkout_info['shipping'] ?? [];

// Calculate cart total
$total = calculateCartSubtotal($cartItems);

$page_title = "Checkout | Nabta";
include __DIR__ . '/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="checkout-container">
    <div class="checkout-main">
        <div class="checkout-card">
            <h1>Checkout</h1>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="checkout.php" class="checkout-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <h2>Shipping Information</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['first_name'] ?? '') ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['last_name'] ?? '') ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($shipping['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" 
                           class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($shipping['address'] ?? '') ?>" required>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['address']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" 
                           class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($shipping['city'] ?? '') ?>" required>
                    <?php if (isset($errors['city'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['city']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <select id="country" name="country" 
                            class="form-control <?= isset($errors['country']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Select a country</option>
                        <option value="EGY" <?= ($shipping['country'] ?? '') === 'EGY' ? 'selected' : '' ?>>egypt</option>
                        <option value="CA" <?= ($shipping['country'] ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                        <option value="UK" <?= ($shipping['country'] ?? '') === 'UK' ? 'selected' : '' ?>>United Kingdom</option>
                    </select>
                    <?php if (isset($errors['country'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['country']) ?></div>
                    <?php endif; ?>
                </div>

                <h2 class="mt-4">Payment Information</h2>
                
                <div class="payment-methods">
                    <div class="payment-method">
                        <input type="radio" id="credit_card" name="payment_method" value="credit_card" 
                               <?= ($shipping['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?> required>
                        <label for="credit_card">
                            <i class="fas fa-credit-card"></i>
                            Credit Card
                        </label>
                    </div>
                    
                    <div class="payment-method">
                        <input type="radio" id="paypal" name="payment_method" value="paypal"
                               <?= ($shipping['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?>>
                        <label for="paypal">
                            <i class="fab fa-paypal"></i>
                            PayPal
                        </label>
                    </div>

                    <div class="payment-method">
                        <input type="radio" id="cash" name="payment_method" value="cash_on_delivery"
                               <?= ($shipping['payment_method'] ?? '') === 'cash_on_delivery' ? 'checked' : '' ?>>
                        <label for="cash">
                            <i class="fas fa-money-bill-wave"></i>
                            Cash on Delivery
                        </label>
                    </div>
                </div>

                <!-- Credit Card Details -->
                <div id="credit-card-details" class="payment-details" style="display: none;">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <div class="card-input-wrapper">
                            <input type="text" id="card_number" name="card_number" class="form-control" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                            <div class="card-type-icon"></div>
                        </div>
                        <div class="card-error" id="card_number_error"></div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" name="expiry" class="form-control" 
                                   placeholder="MM/YY" maxlength="5" autocomplete="off">
                            <div class="card-error" id="expiry_error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <div class="cvv-wrapper">
                                <input type="text" id="cvv" name="cvv" class="form-control" 
                                       placeholder="123" maxlength="3" autocomplete="off">
                                <div class="cvv-info" title="The 3-digit security code on the back of your card">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                            </div>
                            <div class="card-error" id="cvv_error"></div>
                        </div>
                    </div>
                </div>

                <!-- PayPal Details -->
                <div id="paypal-details" class="payment-details" style="display: none;">
                    <div class="paypal-info">
                        <i class="fab fa-paypal"></i>
                        <p>You will be redirected to PayPal to complete your payment securely.</p>
                    </div>
                </div>

                <!-- Cash on Delivery Details -->
                <div id="cash-details" class="payment-details" style="display: none;">
                    <div class="cash-info">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Pay with cash upon delivery. Our delivery person will collect the payment.</p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Place Order</button>
                
                <div class="secure-checkout">
                    <i class="fas fa-lock"></i>
                    <span>Secure Checkout</span>
                </div>
            </form>
        </div>
    </div>
    
    <div class="order-summary">
        <h3>Order Summary</h3>
        
        <div class="order-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    <div class="item-details">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <span class="item-type"><?= ucfirst($item['type']) ?></span>
                        <span class="item-quantity">Qty: <?= $item['quantity'] ?></span>
                    </div>
                    <div class="item-price">
                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="order-totals">
            <div class="order-total grand-total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        </div>
        
        <a href="cart.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Cart
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
:root {
    --primary: #28a745;
    --primary-dark: #218838;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --danger: #dc3545;
    --border-radius: 0.375rem;
    --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.checkout-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.checkout-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
}

.checkout-card h1 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: var(--primary);
}

.checkout-card h2 {
    font-size: 1.25rem;
    margin: 1.5rem 0 1rem;
    color: var(--dark);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    font-size: 1rem;
}

.form-control:focus {
    border-color: var(--primary);
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
}

.is-invalid {
    border-color: var(--danger);
}

.invalid-feedback {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1.5rem 0;
}

.payment-method {
    position: relative;
    padding: 1rem;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    cursor: pointer;
}

.payment-method:hover {
    border-color: var(--primary);
    background-color: #f8f9fa;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-method label {
    display: flex;
    align-items: center;
    margin: 0;
    cursor: pointer;
    width: 100%;
}

.payment-method i {
    margin-right: 0.75rem;
    font-size: 1.25rem;
    color: var(--secondary);
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    border: none;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    width: 100%;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-outline:hover {
    background-color: var(--primary);
    color: white;
}

.order-summary {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    position: sticky;
    top: 1rem;
}

.order-summary h3 {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.order-items {
    margin-bottom: 1.5rem;
}

.order-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.item-type {
    display: inline-block;
    font-size: 0.75rem;
    background: #e0e0e0;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    color: #424242;
    margin-right: 0.5rem;
}

.item-quantity {
    font-size: 0.875rem;
    color: var(--secondary);
}

.item-price {
    font-weight: 500;
}

.order-totals {
    border-top: 1px solid #eee;
    padding-top: 1rem;
}

.order-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-weight: 500;
}

.grand-total {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary);
}

.secure-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1.5rem;
    color: var(--secondary);
    font-size: 0.875rem;
}

.secure-checkout i {
    margin-right: 0.5rem;
    color: var(--primary);
}

.alert {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius);
    background-color: #f8d7da;
    color: #721c24;
}

.payment-details {
    margin-top: 1.5rem;
    padding: 1.5rem;
    border: 1px solid #e0e0e0;
    border-radius: var(--border-radius);
    background-color: #f8f9fa;
}

.paypal-info, .cash-info {
    text-align: center;
    padding: 1rem;
}

.paypal-info i, .cash-info i {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.paypal-info p, .cash-info p {
    color: var(--secondary);
    margin: 0;
}

.card-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.card-type-icon {
    position: absolute;
    right: 10px;
    width: 40px;
    height: 25px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}

.card-type-icon.visa {
    background-image: url('https://cdn.jsdelivr.net/gh/muhammederdem/credit-card-form@master/src/assets/images/visa.png');
}

.card-type-icon.mastercard {
    background-image: url('https://cdn.jsdelivr.net/gh/muhammederdem/credit-card-form@master/src/assets/images/mastercard.png');
}

.card-type-icon.amex {
    background-image: url('https://cdn.jsdelivr.net/gh/muhammederdem/credit-card-form@master/src/assets/images/amex.png');
}

.card-error {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    min-height: 1.25rem;
}

.cvv-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.cvv-info {
    position: absolute;
    right: 10px;
    color: var(--secondary);
    cursor: help;
}

.cvv-info i {
    font-size: 1rem;
}

/* Add focus styles */
.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Add error styles */
.form-control.is-invalid {
    border-color: var(--danger);
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
    }

    .payment-details {
        padding: 1rem;
    }
}

/* Add styles for better input formatting */
#card_number {
    letter-spacing: 1px;
    font-family: monospace;
}

#expiry {
    letter-spacing: 1px;
    font-family: monospace;
}

#cvv {
    letter-spacing: 1px;
    font-family: monospace;
}

/* Add placeholder styles */
.form-control::placeholder {
    color: #adb5bd;
    opacity: 0.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardDetails = document.getElementById('credit-card-details');
    const paypalDetails = document.getElementById('paypal-details');
    const cashDetails = document.getElementById('cash-details');

    function showPaymentDetails() {
        // Hide all payment details first
        creditCardDetails.style.display = 'none';
        paypalDetails.style.display = 'none';
        cashDetails.style.display = 'none';

        // Show selected payment details
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        switch(selectedMethod) {
            case 'credit_card':
                creditCardDetails.style.display = 'block';
                break;
            case 'paypal':
                paypalDetails.style.display = 'block';
                break;
            case 'cash_on_delivery':
                cashDetails.style.display = 'block';
                break;
        }
    }

    // Add change event listener to all payment methods
    paymentMethods.forEach(method => {
        method.addEventListener('change', showPaymentDetails);
    });

    // Show initial payment details
    showPaymentDetails();

    // Credit Card Input Formatting
    const cardNumber = document.getElementById('card_number');
    const expiry = document.getElementById('expiry');
    const cvv = document.getElementById('cvv');

    // Card number formatting with automatic spacing
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            // Remove all non-digit characters
            let value = e.target.value.replace(/\D/g, '');
            
            // Add space after every 4 digits
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            // Update input value with formatting
            e.target.value = formattedValue;
        });

        // Prevent non-numeric input
        cardNumber.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key)) {
                e.preventDefault();
            }
        });
    }

    // Expiry date formatting
    if (expiry) {
        expiry.addEventListener('input', function(e) {
            // Remove all non-digit characters
            let value = e.target.value.replace(/\D/g, '');
            
            // Format as MM/YY
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            
            // Update input value with formatting
            e.target.value = value;
        });

        // Prevent non-numeric input
        expiry.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key)) {
                e.preventDefault();
            }
        });
    }

    // CVV formatting - only allow 3 digits
    if (cvv) {
        cvv.addEventListener('input', function(e) {
            // Remove all non-digit characters and limit to 3 digits
            let value = e.target.value.replace(/\D/g, '').slice(0, 3);
            e.target.value = value;
        });

        // Prevent non-numeric input
        cvv.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key)) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php
// Helper functions
function getCartCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items 
                          WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getDBCartItems($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT 
            ci.id,
            ci.quantity,
            ci.item_type,
            p.id AS plant_id,
            p.name AS plant_name,
            p.price AS plant_price,
            p.image_url AS plant_image,
            pr.id AS product_id,
            pr.name AS product_name,
            pr.price AS product_price,
            pr.image_url AS product_image
        FROM cart_items ci
        LEFT JOIN plants p ON ci.plant_id = p.id AND ci.item_type = 'plant'
        LEFT JOIN products pr ON ci.product_id = pr.id AND ci.item_type = 'product'
        WHERE ci.cart_id = (SELECT id FROM carts WHERE user_id = ?)
        ORDER BY ci.id ASC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format items consistently
    $result = [];
    foreach ($items as $item) {
        $result[] = [
            'id' => $item['id'],
            'type' => $item['item_type'],
            'name' => $item['item_type'] === 'plant' ? $item['plant_name'] : $item['product_name'],
            'price' => $item['item_type'] === 'plant' ? $item['plant_price'] : $item['product_price'],
            'image_url' => $item['item_type'] === 'plant' ? $item['plant_image'] : $item['product_image'],
            'quantity' => $item['quantity'],
            'item_id' => $item['item_type'] === 'plant' ? $item['plant_id'] : $item['product_id']
        ];
    }
    
    return $result;
}

function calculateCartSubtotal($cartItems) {
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}
?>