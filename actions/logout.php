<?php
require_once '../config/init.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Clear all session data
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

// Broadcast logout to other tabs using localStorage
$logoutBroadcast = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out | Nabta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f6;
            color: #263238;
        }
        .logout-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        .spinner {
            border: 4px solid rgba(46, 125, 50, 0.2);
            border-radius: 50%;
            border-top: 4px solid #2e7d32;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        p {
            margin-bottom: 1.5rem;
            color: #607d8b;
        }
        a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        a:hover {
            color: #1b5e20;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h1>Logging Out</h1>
        <p>You are being securely logged out of all browser tabs...</p>
        <p><a href="../index.php">Return to Home Page</a></p>
    </div>

    <script>
        // Broadcast logout to all tabs
        function broadcastLogout() {
            localStorage.setItem('logout', Date.now());
        }

        // Redirect after logout is complete
        function redirectAfterLogout() {
            // Clear any remaining session data
            sessionStorage.clear();
            
            // Redirect to login page after a brief delay
            setTimeout(function() {
                window.location.href = '../login.php';
            }, 1000);
        }

        // Initialize the logout process
        broadcastLogout();
        redirectAfterLogout();

        // Listen for logout events from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout') {
                redirectAfterLogout();
            }
        });
    </script>
</body>
</html>
HTML;

echo $logoutBroadcast;
exit();