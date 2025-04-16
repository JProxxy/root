<?php
// Database configuration on AWS
// note: if you stop ec2, the $host ip will change same with the public address 
// $host = '52.221.180.50';
// $dbname = 'rivan_iot';
// $username = 'root';
// $password = 'Pa$$word1';


// Local Host for testing
$host = 'localhost';   // or '127.0.0.1'
$dbname = 'rivan_iot';          // (You need to specify your database name)
$username = 'root';    // Default MySQL user
$password = 'Pa$$word1';        // Default password is empty


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
