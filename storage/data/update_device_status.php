<?php
// Start the session
session_start();

// Include the database connection
include('../app/config/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access!";
    exit();
}

// Get device_name and status from POST
$device_name = isset($_POST['device_name']) ? $_POST['device_name'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

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
        error_log($e->getMessage());
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid data provided.";
}
?>
