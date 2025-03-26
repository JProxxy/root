<?php
// Include the connection.php file to use the existing database connection
include __DIR__ . '/../../app/config/connection.php';

try {
    // Fetch the latest WaterPercentage from the water_tank table
    $stmt = $conn->query("SELECT WaterPercentage FROM water_tank ORDER BY timestamp DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the result is not empty and output the WaterPercentage
    if ($result) {
        echo json_encode(["WaterPercentage" => $result['WaterPercentage']]);
    } else {
        echo json_encode(["WaterPercentage" => 0]); // If no data found, return 0
    }
} catch (PDOException $e) {
    // Handle any errors that occur during the query execution
    error_log($e->getMessage()); // Log the error for debugging
    echo json_encode(["error" => "Database query failed: " . $e->getMessage()]);
}

// No need to close the connection explicitly with PDO, as it is automatically closed when the script ends
?>