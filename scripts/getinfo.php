<?php
// Include your database connection file
require_once '../app/config/connection.php'; // Ensure this points to the correct path

// Start the session to retrieve user_id
session_start();

// Check if user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Define the query to fetch the user_id from the user table based on the session
$query = "SELECT user_id FROM users WHERE user_id = :user_id";  // Use 'user_id' as column name

try {
    // Prepare and execute the query using PDO
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user_id to the prepared statement
    $stmt->execute();

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Send the user_id to the console
        echo "<script>console.log('User ID: " . htmlspecialchars($result['user_id'], ENT_QUOTES, 'UTF-8') . "');</script>";
    } else {
        echo "<script>console.log('User not found.');</script>";
    }
} catch (PDOException $e) {
    // Handle any PDO exceptions
    echo "<script>console.log('Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');</script>";
}
?>
