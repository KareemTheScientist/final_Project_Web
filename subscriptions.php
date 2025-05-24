<?php
require_once __DIR__ . '/config/init.php';
require_auth();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$user = null;
$current_subscription = null;
$error_message = '';
$success_message = '';

try {
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get current subscription if exists
    $sub_stmt = $pdo->prepare("
        SELECT * FROM subscriptions 
        WHERE user_id = ? AND end_date >= CURDATE()
        ORDER BY end_date DESC 
        LIMIT 1
    ");
    $sub_stmt->execute([$_SESSION['user_id']]);
    $current_subscription = $sub_stmt->fetch();

    // Handle subscription form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
        $plan_name = $_POST['plan_name'];
        $duration = (int)$_POST['duration'];
        
        // Calculate subscription dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$duration} months"));
        
        // Set prices based on plan
        $price = 0;
        switch($plan_name) {
            case 'basic':
                $price = 9.99;
                break;
            case 'premium':
                $price = 19.99;
                break;
            case 'pro':
                $price = 29.99;
                break;
        }
        
        // Insert new subscription
        $insert_stmt = $pdo->prepare("
            INSERT INTO subscriptions (user_id, plan_name, price, duration_months, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $insert_stmt->execute([
            $_SESSION['user_id'],
            $plan_name,
            $price,
            $duration,
            $start_date,
            $end_date
        ]);
        
        $success_message = "Congratulations! You've successfully subscribed to the " . ucfirst($plan_name) . " Plan. Your subscription will be active until " . date('F d, Y', strtotime($end_date));
        
        // Refresh current subscription
        $sub_stmt->execute([$_SESSION['user_id']]);
        $current_subscription = $sub_stmt->fetch();
    }

} catch (Exception $e) {
    error_log("Subscription Error: " . $e->getMessage());
    $error_message = "An error occurred. Please try again later.";
}

$page_title = "Subscriptions";
include __DIR__ . '/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="subscriptions-container">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
        </div>
    <?php endif; ?>

    <div class="subscriptions-header">
        <h1><i class="fas fa-crown"></i> Subscription Plans</h1>
        <p class="text-muted">Choose a plan that best fits your needs</p>
    </div>

    <?php if ($current_subscription): ?>
        <div class="current-subscription">
            <div class="subscription-card active">
                <div class="subscription-header">
                    <h3>Current Plan</h3>
                    <span class="plan-badge"><?= ucfirst($current_subscription['plan_name']) ?></span>
                </div>
                <div class="subscription-details">
                    <p><i class="fas fa-calendar-alt"></i> Started: <?= date('M d, Y', strtotime($current_subscription['start_date'])) ?></p>
                    <p><i class="fas fa-calendar-check"></i> Expires: <?= date('M d, Y', strtotime($current_subscription['end_date'])) ?></p>
                    <p><i class="fas fa-dollar-sign"></i> Price: $<?= number_format($current_subscription['price'], 2) ?>/month</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="subscription-plans">
        <div class="plan-card">
            <div class="plan-header">
                <h3>Basic Plan</h3>
                <div class="plan-price">
                    <span class="price">$9.99</span>
                    <span class="period">/month</span>
                </div>
            </div>
            <div class="plan-features">
                <ul>
                    <li><i class="fas fa-check"></i> Access to basic plant care guides</li>
                    <li><i class="fas fa-check"></i> Monthly newsletter</li>
                    <li><i class="fas fa-check"></i> Community forum access</li>
                </ul>
            </div>
            <form method="POST" class="plan-form" onsubmit="return showPaymentModal('basic', 9.99)">
                <input type="hidden" name="plan_name" value="basic">
                <input type="hidden" name="duration" value="1">
                <button type="submit" name="subscribe" class="btn btn-primary">
                    Subscribe Now
                </button>
            </form>
        </div>

        <div class="plan-card featured">
            <div class="plan-header">
                <h3>Premium Plan</h3>
                <div class="plan-price">
                    <span class="price">$19.99</span>
                    <span class="period">/month</span>
                </div>
            </div>
            <div class="plan-features">
                <ul>
                    <li><i class="fas fa-check"></i> All Basic Plan features</li>
                    <li><i class="fas fa-check"></i> Expert consultation</li>
                    <li><i class="fas fa-check"></i> Exclusive plant care tips</li>
                    <li><i class="fas fa-check"></i> 10% discount on all products</li>
                </ul>
            </div>
            <form method="POST" class="plan-form" onsubmit="return showPaymentModal('premium', 19.99)">
                <input type="hidden" name="plan_name" value="premium">
                <input type="hidden" name="duration" value="1">
                <button type="submit" name="subscribe" class="btn btn-primary">
                    Subscribe Now
                </button>
            </form>
        </div>

        <div class="plan-card">
            <div class="plan-header">
                <h3>Pro Plan</h3>
                <div class="plan-price">
                    <span class="price">$29.99</span>
                    <span class="period">/month</span>
                </div>
            </div>
            <div class="plan-features">
                <ul>
                    <li><i class="fas fa-check"></i> All Premium Plan features</li>
                    <li><i class="fas fa-check"></i> Priority support</li>
                    <li><i class="fas fa-check"></i> Monthly plant delivery</li>
                    <li><i class="fas fa-check"></i> 20% discount on all products</li>
                    <li><i class="fas fa-check"></i> Access to exclusive workshops</li>
                </ul>
            </div>
            <form method="POST" class="plan-form" onsubmit="return showPaymentModal('pro', 29.99)">
                <input type="hidden" name="plan_name" value="pro">
                <input type="hidden" name="duration" value="1">
                <button type="submit" name="subscribe" class="btn btn-primary">
                    Subscribe Now
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="subscription-summary">
                <h3>Subscription Summary</h3>
                <p>Plan: <span id="selectedPlan"></span></p>
                <p>Price: $<span id="selectedPrice"></span>/month</p>
            </div>
            <form id="paymentForm" class="payment-form">
                <div class="form-group">
                    <label for="cardName">Cardholder Name</label>
                    <input type="text" id="cardName" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="cardNumber">Card Number</label>
                    <input type="text" id="cardNumber" required placeholder="1234 5678 9012 3456" maxlength="19">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="text" id="expiryDate" required placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" required placeholder="123" maxlength="3">
                    </div>
                </div>
                <div class="form-group">
                    <label for="billingAddress">Billing Address</label>
                    <input type="text" id="billingAddress" required placeholder="123 Main St">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" required placeholder="City">
                    </div>
                    <div class="form-group">
                        <label for="zipCode">ZIP Code</label>
                        <input type="text" id="zipCode" required placeholder="12345" maxlength="5">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Complete Payment
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.subscriptions-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.subscriptions-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-top: 5%;
}

.subscriptions-header h1 {
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.current-subscription {
    margin-bottom: 3rem;
}

.subscription-card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    max-width: 600px;
    margin: 0 auto;
}

.subscription-card.active {
    border: 2px solid var(--primary);
}

.subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.plan-badge {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
}

.subscription-details p {
    margin: 0.5rem 0;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.subscription-plans {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.plan-card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.plan-card:hover {
    transform: translateY(-5px);
}

.plan-card.featured {
    border: 2px solid var(--primary);
    position: relative;
}

.plan-card.featured::before {
    content: 'Most Popular';
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary);
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.plan-header {
    text-align: center;
    margin-bottom: 2rem;
}

.plan-price {
    margin-top: 1rem;
}

.price {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary);
}

.period {
    color: var(--gray);
    font-size: 0.9rem;
}

.plan-features {
    margin-bottom: 2rem;
}

.plan-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    margin: 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.plan-features i {
    color: var(--primary);
}

.plan-form {
    text-align: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    width: 100%;
    font-size: 1.1rem;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.alert {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .subscription-plans {
        grid-template-columns: 1fr;
    }
    
    .plan-card {
        max-width: 400px;
        margin: 0 auto;
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: white;
    margin: 5% auto;
    padding: 0;
    width: 95%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.2rem;
}

.close {
    font-size: 1.5rem;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    transition: color 0.3s;
}

.close:hover {
    color: var(--primary);
}

.modal-body {
    padding: 1.5rem;
}

.subscription-summary {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.subscription-summary h3 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
    font-size: 1rem;
}

.subscription-summary p {
    margin: 0.5rem 0;
    color: var(--gray);
}

.payment-form {
    display: grid;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

label {
    font-weight: 500;
    color: var(--dark);
    font-size: 0.9rem;
}

input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
    transition: border-color 0.3s;
}

input:focus {
    border-color: var(--primary);
    outline: none;
}

input::placeholder {
    color: #999;
    font-size: 0.85rem;
}

.btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .modal-content {
        margin: 10% auto;
        width: 90%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Success Message Animation */
@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.alert-success {
    animation: successPulse 0.5s ease-out;
}
</style>

<script>
// Format card number with spaces
document.getElementById('cardNumber')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = '';
    for(let i = 0; i < value.length; i++) {
        if(i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    e.target.value = formattedValue;
});

// Format expiry date
document.getElementById('expiryDate')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    if(value.length >= 2) {
        value = value.slice(0,2) + '/' + value.slice(2);
    }
    e.target.value = value;
});

// Format CVV (numbers only)
document.getElementById('cvv')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/gi, '');
});

// Show payment modal
function showPaymentModal(plan, price) {
    const modal = document.getElementById('paymentModal');
    const selectedPlan = document.getElementById('selectedPlan');
    const selectedPrice = document.getElementById('selectedPrice');
    
    selectedPlan.textContent = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
    selectedPrice.textContent = price.toFixed(2);
    
    modal.style.display = 'block';
    return false; // Prevent form submission
}

// Close modal when clicking the X
document.querySelector('.close')?.addEventListener('click', function() {
    document.getElementById('paymentModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('paymentModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// Handle payment form submission
document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Simulate payment processing
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    setTimeout(() => {
        // Submit the original subscription form
        const planName = document.querySelector('input[name="plan_name"]').value;
        const form = document.querySelector(`form[onsubmit*="${planName}"]`);
        form.submit();
    }, 2000);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?> 