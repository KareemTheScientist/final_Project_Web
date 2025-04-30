<?php
include 'db.php';

// Start secure session
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Query using either username or email
            $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = :username OR email = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    // Set session with available data (using username as name if name column doesn't exist)
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['username'], // Using username as display name
                        'email' => $user['email']
                    ];
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set remember me cookie if checked
                    if (isset($_POST['remember'])) {
                        $cookieValue = base64_encode($user['id'] . ':' . hash('sha256', $user['password']));
                        setcookie('remember_me', $cookieValue, time() + 86400 * 30, '/', '', true, true);
                    }
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'Username/email not found.';
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Check for remember me cookie
if (empty($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    try {
        $cookieParts = explode(':', base64_decode($_COOKIE['remember_me']));
        if (count($cookieParts) === 2) {
            $userId = $cookieParts[0];
            $passwordHash = $cookieParts[1];
            
            $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (hash('sha256', $user['password']) === $passwordHash) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['username'],
                        'email' => $user['email']
                    ];
                    session_regenerate_id(true);
                    header('Location: dashboard.php');
                    exit();
                }
            }
        }
        // If invalid, clear the cookie
        setcookie('remember_me', '', time() - 3600, '/');
    } catch(PDOException $e) {
        error_log('Remember me error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nabta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --primary-dark: #1b5e20;
            --error: #d32f2f;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/login-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 20px;
        }
        
        .auth-container {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo img {
            height: 80px;
            transition: transform 0.3s;
        }
        
        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 38px;
            color: var(--gray);
            cursor: pointer;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
        }
        
        .error {
            color: var(--error);
            background: #fde8e8;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            border-left: 4px solid var(--error);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input {
            width: auto;
        }
        
        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .auth-container {
                padding: 1.5rem;
            }
            
            .logo img {
                height: 60px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <a href="index.php">
                <img src="./img/nabta.png" alt="Nabta">
            </a>
        </div>
        
        <h1>Welcome Back</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post" autocomplete="on">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required autofocus>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye input-icon" id="togglePassword"></i>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Add focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.querySelector('label').style.color = 'var(--primary)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.querySelector('label').style.color = 'var(--dark)';
            });
        });
    </script>
</body>
</html>