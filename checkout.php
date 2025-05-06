<?php
session_start();
require_once __DIR__ . '/config/init.php';
require_auth();

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $required_fields = ['first_name', 'last_name', 'address', 'city', 'state', 'zip', 'country', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }

    // Validate ZIP code format
    if (!empty($_POST['zip']) && !preg_match('/^\d{5}(-\d{4})?$/', $_POST['zip'])) {
        $errors['zip'] = "Invalid ZIP code format";
    }

    if (empty($errors)) {
        $_SESSION['checkout_info'] = [
            'shipping' => [
                'first_name' => htmlspecialchars(trim($_POST['first_name'])),
                'last_name'  => htmlspecialchars(trim($_POST['last_name'])),
                'address'    => htmlspecialchars(trim($_POST['address'])),
                'address2'   => htmlspecialchars(trim($_POST['address2'] ?? '')),
                'city'       => htmlspecialchars(trim($_POST['city'])),
                'state'      => htmlspecialchars(trim($_POST['state'])),
                'zip'        => htmlspecialchars(trim($_POST['zip'])),
                'country'    => htmlspecialchars(trim($_POST['country']))
            ],
            'payment_method' => htmlspecialchars(trim($_POST['payment_method']))
        ];

        header('Location: order-confirmation.php');
        exit;
    }
}

// Pre-fill form values if set
$checkout_info = $_SESSION['checkout_info'] ?? null;
$shipping = $checkout_info['shipping'] ?? [];

// Calculate cart totals
$subtotal = 0;
$shipping_cost = 5.99;
$tax_rate = 0.08;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping_cost + $tax;

$page_title = "Checkout | Nabta";
include __DIR__ . '/includes/navbar.php';
?>

<div class="checkout-container">
    <div class="checkout-main">
        <div class="checkout-card">
            <h1>Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <p><strong>Please fix the following errors:</strong></p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="checkout.php" class="checkout-form">
                <h2>Shipping Information</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['first_name'] ?? '') ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['last_name'] ?? '') ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($shipping['address'] ?? '') ?>" required>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['address']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="address2">Address 2 (Optional)</label>
                    <input type="text" id="address2" name="address2" class="form-control" 
                           value="<?= htmlspecialchars($shipping['address2'] ?? '') ?>">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['city'] ?? '') ?>" required>
                        <?php if (isset($errors['city'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['city']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" class="form-control <?= isset($errors['state']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['state'] ?? '') ?>" required>
                        <?php if (isset($errors['state'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['state']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="zip">ZIP/Postal Code</label>
                        <input type="text" id="zip" name="zip" class="form-control <?= isset($errors['zip']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['zip'] ?? '') ?>" required>
                        <?php if (isset($errors['zip'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['zip']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control <?= isset($errors['country']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($shipping['country'] ?? '') ?>" required>
                        <?php if (isset($errors['country'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['country']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h2>Payment Method</h2>
                
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="credit_card" <?= (isset($checkout_info['payment_method']) && $checkout_info['payment_method'] == 'credit_card') ? 'checked' : '' ?> required>
                        <i class="far fa-credit-card"></i>
                        <div>
                            <strong>Credit Card</strong>
                            <p>Pay with Visa, Mastercard, or American Express</p>
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="paypal" <?= (isset($checkout_info['payment_method']) && $checkout_info['payment_method'] == 'paypal') ? 'checked' : '' ?>>
                        <i class="fab fa-paypal"></i>
                        <div>
                            <strong>PayPal</strong>
                            <p>Pay with your PayPal account</p>
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="cash" <?= (isset($checkout_info['payment_method']) && $checkout_info['payment_method'] == 'cash') ? 'checked' : '' ?>>
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <strong>Cash on Delivery</strong>
                            <p>Pay with cash when your order arrives</p>
                        </div>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Place Order</button>
                
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
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
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
            <div class="order-total">
                <span>Subtotal</span>
                <span>$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="order-total">
                <span>Shipping</span>
                <span>$<?= number_format($shipping_cost, 2) ?></span>
            </div>
            <div class="order-total">
                <span>Tax</span>
                <span>$<?= number_format($tax, 2) ?></span>
            </div>
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
    --transition: all 0.3s ease;
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
    margin-bottom: 2rem;
}

.checkout-card h1 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: var(--primary);
    font-weight: 600;
}

.checkout-card h2 {
    font-size: 1.25rem;
    margin: 1.5rem 0 1rem;
    color: var(--dark);
    font-weight: 500;
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
    transition: var(--transition);
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
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.payment-method:hover {
    border-color: var(--primary);
}

.payment-method input {
    margin-right: 1rem;
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
    transition: var(--transition);
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
    color: var(--dark);
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

.order-item:last-child {
    border-bottom: none;
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
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>