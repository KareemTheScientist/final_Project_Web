<?php
// db.php - Database connection
$host = 'localhost';
$dbname = 'nabta';
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>