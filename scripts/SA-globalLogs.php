<?php
header('Content-Type: application/json');

// Include your database connection file
include '../app/config/connection.php';

try {
    // Prepare the SQL statement to fetch logs
    // Adjust the table name and columns as needed. Here, we're assuming a table named "logs".
    $sql = "SELECT user_id, role_id, changed, description FROM logs ORDER BY user_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Fetch all log records as an associative array
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output the logs in JSON format
    echo json_encode([
        "data" => $logs
    ]);
} catch (PDOException $e) {
    // If an error occurs, output a JSON error message
    echo json_encode([
        "error" => "Database error: " . $e->getMessage()
    ]);
}
?>
