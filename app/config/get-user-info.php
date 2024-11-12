<?php
// Enable error reporting for debugging (remove this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require('connection.php');

// Start session to access user session data
session_start();

// Log the session data for debugging
error_log("Session data: " . print_r($_SESSION, true));

// Check if the user is logged in by verifying the session
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    error_log("User ID from session: " . $userId);

    // Query to fetch user information based on user ID
    try {
        $stmt = $conn->prepare("SELECT user_id, username, email FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch user information as an associative array
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Log the fetched user information
        error_log("Fetched user info: " . print_r($userInfo, true));

        // If user info is found, return it as a JSON response
        if ($userInfo) {
            header('Content-Type: application/json');
            echo json_encode($userInfo);
        } else {
            // If user info is not found, return an error message
            error_log("User info not found for user ID: " . $userId);
            echo json_encode(['error' => 'User not found']);
        }
    } catch (PDOException $e) {
        // Log the error and return a JSON response with an error message
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to retrieve user info']);
    }
} else {
    // User is not logged in, return an error message
    error_log("User is not logged in");
    echo json_encode(['error' => 'User is not logged in']);
}
?>
