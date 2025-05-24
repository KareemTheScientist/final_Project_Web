<?php
require_once __DIR__ . '/config/init.php';
require_auth();

$page_title = "Account Settings";
include __DIR__ . '/includes/navbar.php';

// Initialize variables
$user = null;
$success_message = '';
$error_message = '';

try {
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_profile'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Check if username or email already exists
            $check_stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE (username = ? OR email = ?) 
                AND id != ?
            ");
            $check_stmt->execute([$username, $email, $_SESSION['user_id']]);
            if ($check_stmt->rowCount() > 0) {
                throw new Exception("Username or email already exists");
            }
            
            // Update profile
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET username = ?, email = ? 
                WHERE id = ?
            ");
            $update_stmt->execute([$username, $email, $_SESSION['user_id']]);
            
            $success_message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
        
        if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Validate new password
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match");
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET password = ? 
                WHERE id = ?
            ");
            $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
            
            $success_message = "Password changed successfully!";
        }
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
</head>

<div class="account-container">
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

    <div class="account-header">
        <h1><i class="fas fa-user-cog"></i> Account Settings</h1>
        <p>Manage your account preferences and settings</p>
    </div>

    <div class="account-grid">
        <!-- Profile Section -->
        <div class="account-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="account-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Account Created</label>
                        <p class="form-text"><?= date('F d, Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Password Section -->
        <div class="account-card">
            <div class="card-header">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="account-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small class="form-text">Password must be at least 8 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Preferences Section -->
        <div class="account-card">
            <div class="card-header">
                <h2><i class="fas fa-cog"></i> Account Preferences</h2>
            </div>
            <div class="card-body">
                <div class="preferences-list">
                    <div class="preference-item">
                        <div class="preference-info">
                            <h3>Email Notifications</h3>
                            <p>Receive updates about your orders and promotions</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="preference-item">
                        <div class="preference-info">
                            <h3>Newsletter Subscription</h3>
                            <p>Get weekly plant care tips and gardening advice</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="account-card danger-zone">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
            </div>
            <div class="card-body">
                <p>Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.account-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.account-header {
    text-align: center;
    margin-bottom: 3rem;
}

.account-header h1 {
    color: var(--primary);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.account-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    background: var(--primary);
    color: white;
}

.card-header h2 {
    margin: 0;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

.account-form {
    display: grid;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

label {
    font-weight: 500;
    color: var(--dark);
}

input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

input:focus {
    border-color: var(--primary);
    outline: none;
}

.form-text {
    font-size: 0.875rem;
    color: var(--gray);
    margin-top: 0.25rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    width: 100%;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

/* Switch Toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Preferences List */
.preferences-list {
    display: grid;
    gap: 1.5rem;
}

.preference-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.preference-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.preference-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    color: var(--dark);
}

.preference-info p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray);
}

/* Danger Zone */
.danger-zone .card-header {
    background: #dc3545;
}

.danger-zone p {
    color: var(--gray);
    margin-bottom: 1rem;
}

/* Alert Messages */
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
    .account-grid {
        grid-template-columns: 1fr;
    }
    
    .preference-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .switch {
        align-self: flex-end;
    }
}
</style>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        // Add account deletion logic here
        alert('Account deletion functionality will be implemented soon.');
    }
}

// Password validation
document.getElementById('new_password')?.addEventListener('input', function() {
    const password = this.value;
    const confirmPassword = document.getElementById('confirm_password');
    
    if (password.length < 8) {
        this.setCustomValidity('Password must be at least 8 characters long');
    } else {
        this.setCustomValidity('');
    }
    
    if (confirmPassword.value) {
        if (password !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
});

document.getElementById('confirm_password')?.addEventListener('input', function() {
    const password = document.getElementById('new_password').value;
    
    if (this.value !== password) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?> 