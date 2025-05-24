<?php
// config/init.php

// Define base path constants
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('BASE_URL', 'FinalProject/final_Project_Web'); // Removed leading slash

// Ensure session starts before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Database connection setup
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nabta;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Generate full URL from a relative path
 */
function url($path = '') {
    return '/' . BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect to a given path
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

/**
 * Check if user is authenticated
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Force authentication for protected pages
 */
function require_auth() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}
