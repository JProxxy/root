<?php
// Database configuration
$host = '18.139.255.32';
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

// Create a PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8"); // Optional: set charset
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log the error for debugging
    die("Database connection failed."); // Generic error message for users
}
?>
