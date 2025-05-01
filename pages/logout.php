<?php
require_once __DIR__ . '/../config/paths.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to home page
redirect('index.php');