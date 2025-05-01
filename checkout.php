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
    // Validate input (basic example)
    $required_fields = ['first_name', 'last_name', 'address', 'city', 'state', 'zip', 'country', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Missing field: $field";
        }
    }

    if (empty($errors)) {
        $_SESSION['checkout_info'] = [
            'shipping' => [
                'first_name' => $_POST['first_name'],
                'last_name'  => $_POST['last_name'],
                'address'    => $_POST['address'],
                'address2'   => $_POST['address2'] ?? '',
                'city'       => $_POST['city'],
                'state'      => $_POST['state'],
                'zip'        => $_POST['zip'],
                'country'    => $_POST['country']
            ],
            'payment_method' => $_POST['payment_method']
        ];

        header('Location: order-confirmation.php');
        exit;
    }
}

// Pre-fill form values if set
$checkout_info = $_SESSION['checkout_info'] ?? null;
$shipping = $checkout_info['shipping'] ?? [];
?>

<!-- Checkout Form -->
<div class="checkout-container">
    <div class="checkout-card">
        <h1>Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="checkout-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="checkout.php" class="checkout-form">
            <h3>Shipping Information</h3>
            <div class="form-grid">
                <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($shipping['first_name'] ?? '') ?>" required>
                <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($shipping['last_name'] ?? '') ?>" required>
                <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($shipping['address'] ?? '') ?>" required>
                <input type="text" name="address2" placeholder="Address 2 (Optional)" value="<?= htmlspecialchars($shipping['address2'] ?? '') ?>">
                <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($shipping['city'] ?? '') ?>" required>
                <input type="text" name="state" placeholder="State" value="<?= htmlspecialchars($shipping['state'] ?? '') ?>" required>
                <input type="text" name="zip" placeholder="ZIP Code" value="<?= htmlspecialchars($shipping['zip'] ?? '') ?>" required>
                <input type="text" name="country" placeholder="Country" value="<?= htmlspecialchars($shipping['country'] ?? '') ?>" required>
            </div>

            <h3>Payment Method</h3>
            <select name="payment_method" required>
                <option value="">Select</option>
                <option value="credit_card" <?= (isset($checkout_info['payment_method']) && $checkout_info['payment_method'] == 'credit_card') ? 'selected' : '' ?>>Credit Card</option>
                <option value="paypal" <?= (isset($checkout_info['payment_method']) && $checkout_info['payment_method'] == 'paypal') ? 'selected' : '' ?>>PayPal</option>
            </select>

            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>
</div>

<style>
.checkout-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.checkout-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    padding: 2rem;
}

.checkout-card h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #28a745;
}

.checkout-form h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    color: #343a40;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.checkout-form input,
.checkout-form select {
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
}

.checkout-form select {
    margin-top: 0.5rem;
    margin-bottom: 1rem;
}

.checkout-error {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.checkout-error ul {
    margin: 0;
    padding-left: 1.25rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border: none;
    border-radius: 4px;
    text-align: center;
    cursor: pointer;
}

.btn-primary {
    background-color: #28a745;
    color: white;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #218838;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>