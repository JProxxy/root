<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include the connection.php file to use the existing database connection
include '../../app/config/connection.php';

try {
    // Query to fetch the latest humidity value
    $query = "SELECT humidity FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
    $stmt = $conn->query($query); // Execute the query

    // Check if there is any data returned
    if ($stmt->rowCount() > 0) {
        // Fetch the row data and get the humidity value
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $humidity = $row['humidity'];
        // Return the humidity value as a JSON response
        echo json_encode(["humidity" => $humidity]);
    } else {
        // If no data found, return a default value of 0
        echo json_encode(["humidity" => 0]);
    }
} catch (PDOException $e) {
    // Handle any errors that occur during the query execution
    error_log($e->getMessage()); // Log the error for debugging
    echo json_encode(["error" => "Database query failed: " . $e->getMessage()]);
}

// No need to close the connection explicitly with PDO, as it is automatically closed when the script ends
?>