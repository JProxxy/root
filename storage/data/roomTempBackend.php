<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the connection.php file to use the existing database connection
require '../app/config/connection.php'; // Make sure the path to connection.php is correct

try {
    // Fetch the latest temperature for the device 'ffRoom-temp'
    $query = "SELECT temperature FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
    $stmt = $conn->prepare($query); // Use the $conn object from connection.php
    $stmt->execute();

    // Get the result
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $temperature = isset($row['temperature']) ? $row['temperature'] : null;

    // Return the temperature as a JSON response
    header('Content-Type: application/json');
    echo json_encode(['temperature' => $temperature]);

} catch (PDOException $e) {
    // Handle connection errors or query issues
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>