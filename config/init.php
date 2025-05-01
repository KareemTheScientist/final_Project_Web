<?php
// Error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define base URL (add this)
$base_url = '/FinalProject/final_Project_Web/';

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 1 day
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_name('NABTA_SESSID');
    session_start();
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nabta;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed");
}

/**
 * Check login status without redirect
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin($redirectTo = 'login.php') {
    global $base_url;
    if (!isLoggedIn()) {
        // Store current URL for post-login redirect
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Determine if redirectTo is a relative path or already has base_url
        if (strpos($redirectTo, '/') !== 0) {
            $redirectTo = $base_url . 'pages/' . $redirectTo;
        }
        
        // Ensure we're not already on the login page
        if (basename($_SERVER['SCRIPT_NAME']) !== basename($redirectTo)) {
            header("Location: $redirectTo");
            exit();
        }
    }
}

/**
 * Get URL with base path (add this function)
 */
function url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}