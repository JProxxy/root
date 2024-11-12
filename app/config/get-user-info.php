<?php
// Include database connection
require('../app/config/connection.php');

// Start session to access user session data
session_start();

// Check if the user is logged in by verifying the session
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Query to fetch user information based on user ID
    try {
        $stmt = $conn->prepare("SELECT user_id, username, email FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch user information as an associative array
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user info is found, return it as a JSON response
        if ($userInfo) {
            echo json_encode($userInfo);
        } else {
            // If user info is not found, return an error message
            echo json_encode(['error' => 'User not found']);
        }
    } catch (PDOException $e) {
        // Log the error and return a JSON response with an error message
        error_log($e->getMessage());
        echo json_encode(['error' => 'Failed to retrieve user info']);
    }
} else {
    // User is not logged in, return an error message
    echo json_encode(['error' => 'User is not logged in']);
}
?>
