<?php
// Start the session
session_start();

// Database connection details
$host = '18.139.255.32';  // AWS DB IP (replace with your actual host if different)
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8"); // Set charset to utf8

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "Unauthorized access!";
        exit();
    }

    // Get device_name and status from POST
    $device_name = isset($_POST['device_name']) ? $_POST['device_name'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;

    // If device_name and status are provided
    if ($device_name && $status) {
        try {
            // Prepare the SQL query to update device status using device_name
            $stmt = $conn->prepare("UPDATE Devices SET status = :status WHERE device_name = :device_name");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':device_name', $device_name);

            // Execute the query
            if ($stmt->execute()) {
                echo "Device status updated successfully";
            } else {
                echo "Error updating device status.";
            }
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log($e->getMessage(), 3, '/var/log/php_errors.log'); // Log error
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid data provided.";
    }
} catch (PDOException $e) {
    // Log the error for debugging
    error_log($e->getMessage(), 3, '/var/log/php_errors.log'); // Log error
    echo "Connection failed: " . $e->getMessage();
    die(); // Stop execution if connection fails
}
?>
