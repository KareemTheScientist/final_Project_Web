<?php
require_once './config/init.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php'); // or 'dashboard.php' if that's your homepage
}

$error = '';
$username = '';
$remember = false;

// Handle "remember me" cookie
if (!is_logged_in() && isset($_COOKIE['remember_me'])) {
    try {
        $tokenParts = explode(':', $_COOKIE['remember_me']);
        if (count($tokenParts) === 2) {
            list($userId, $token) = $tokenParts;

            $stmt = $pdo->prepare("SELECT id, username, email FROM users 
                                   WHERE id = :id AND remember_token = :token 
                                   AND token_expires > NOW()");
            $stmt->execute([':id' => $userId, ':token' => $token]);

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch();

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];

                session_regenerate_id(true);
                redirect('dashboard.php'); // or 'dashboard.php'
            }
        }
        // Clear invalid cookie
        setcookie('remember_me', '', time() - 3600, '/', '', true, true);
    } catch (Exception $e) {
        error_log('Remember me error: ' . $e->getMessage());
        setcookie('remember_me', '', time() - 3600, '/', '', true, true);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, email FROM users 
                                   WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $username]);

            if ($user = $stmt->fetch()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ];

                    session_regenerate_id(true);

                    // Handle remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + 60 * 60 * 24 * 30;

                        $stmt = $pdo->prepare("UPDATE users 
                                               SET remember_token = :token, 
                                                   token_expires = FROM_UNIXTIME(:expires) 
                                               WHERE id = :id");
                        $stmt->execute([
                            ':token' => $token,
                            ':expires' => $expires,
                            ':id' => $user['id']
                        ]);

                        setcookie('remember_me', $user['id'] . ':' . $token, $expires, '/', '', true, true);
                    }

                    $redirect = $_SESSION['redirect_url'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_url']);
                    redirect($redirect);
                }
            }

            $error = 'Invalid username or password.';
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to your Nabta account">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/images/login-bg.jpg');
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
        
        .logo img:hover {
            transform: scale(1.05);
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
            transition: color 0.3s;
        }
        
        .input-icon:hover {
            color: var(--primary);
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
        
        .btn:active {
            transform: translateY(0);
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
        
        .remember-me label {
            margin-bottom: 0;
            cursor: pointer;
        }
        
        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
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
            <a href="dashboard.php">
                <img src="./img/NABTA.png" alt="Nabta">
            </a>
        </div>
        
        <h1>Welcome Back</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post" autocomplete="on" novalidate>
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($username); ?>" 
                       required autofocus autocomplete="username">
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       required autocomplete="current-password">
                <i class="fas fa-eye input-icon" id="togglePassword"></i>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" 
                          <?php echo $remember ? 'checked' : '' ?>>
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
            Don't have an account? <a href="../pages/register.php">Create one</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Add focus effects
        document.querySelectorAll('input').forEach(input => {
            const label = input.parentNode.querySelector('label');
            
            input.addEventListener('focus', () => {
                label.style.color = 'var(--primary)';
            });
            
            input.addEventListener('blur', () => {
                label.style.color = 'var(--dark)';
            });
        });
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>