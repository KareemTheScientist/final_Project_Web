<?php
include 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                
                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now login.';
                    $username = $email = $password = $confirm_password = '';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Click & Grow</title>
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
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
            background-image: url('https://images.unsplash.com/photo-1598880940080-ff9a29891b85');
            background-size: cover;
            background-position: center;
        }
        
        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(5px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo img {
            height: 50px;
        }
        
        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .error {
            color: #e74c3c;
            background: #fdecea;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .success {
            color: var(--primary);
            background: #e8f5e9;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <img src="images/logo.png" alt="Click & Grow">
        </div>
        
        <h1>Create Your Account</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <p class="password-hint">Minimum 8 characters</p>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>