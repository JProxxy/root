<?php
// Database configuration on AWS
// $host = '18.139.255.32';
// $dbname = 'rivan_iot';
// $username = 'root';
// $password = 'Pa$$word1';


// Local Host for testing
$host = 'localhost';   // or '127.0.0.1'
$dbname = 'rivan_iot';          // (You need to specify your database name)
$username = 'root';    // Default MySQL user
$password = '';        // Default password is empty


// Create a PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8"); // Optional: set charset
    // echo "Connected successfully"; // Temporary debugging message
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log the error for debugging
    echo "Connection failed: " . $e->getMessage(); // Display the detailed error message
    die(); // Terminate the script
}
?>
