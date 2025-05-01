<?php
require_once __DIR__ . './config/init.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signing Out...</title>
    <meta http-equiv="refresh" content="3;url=dashboard.php"> <!-- Redirect after 3 seconds -->
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
            background-color: #f4f4f4;
        }
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="loader"></div>
    <p class="message">Signing you out... Redirecting to dashboard.</p>

</body>
</html>
