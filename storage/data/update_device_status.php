<?php
// Start the session
session_start();

// Include the database connection file
include('../app/config/connection.php');

// Check if the user is logged in (optional security check)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the device_id and status from the POST request
$device_id = isset($_POST['device_id']) ? $_POST['device_id'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

// Ensure both device_id and status are provided
if ($device_id && $status) {
    try {
        // Prepare the SQL statement to update device status
        $stmt = $conn->prepare("UPDATE Devices SET status = :status WHERE device_id = :device_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':device_id', $device_id);

        // Execute the statement
        if ($stmt->execute()) {
            // Optionally log the action (inserting into the Logs table)
            $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
            $log_action = "Device status updated"; // Log description

            // Insert log into Logs table
            $stmt_log = $conn->prepare("INSERT INTO Logs (user_id, device_id, action) VALUES (:user_id, :device_id, :action)");
            $stmt_log->bindParam(':user_id', $user_id);
            $stmt_log->bindParam(':device_id', $device_id);
            $stmt_log->bindParam(':action', $log_action);

            // Execute the log insert query
            if ($stmt_log->execute()) {
                echo "Device status updated successfully.";
            } else {
                echo "Error logging the action.";
            }
        } else {
            echo "Error updating device status.";
        }
    } catch (PDOException $e) {
        // Log and display the error message
        error_log("Error: " . $e->getMessage());
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    echo "Invalid data provided. Please check your input.";
}
?>
