<?php
// Start the session
session_start();

// Check if user_id is set in the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Return error if user is not logged in
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Database connection
require_once '../app/config/connection.php';

// Sanitize input to prevent SQL injection
$first_name = htmlspecialchars($_POST['first_name']);
$last_name = htmlspecialchars($_POST['last_name']);
$email = htmlspecialchars($_POST['email']);
$phoneNumber = htmlspecialchars($_POST['phoneNumber']);
$role = htmlspecialchars($_POST['role']);
$gender = htmlspecialchars($_POST['gender']);

// Update query with placeholders
$sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phoneNumber = :phoneNumber, gender = :gender WHERE user_id = :user_id";

try {
    // Prepare the SQL query
    $stmt = $conn->prepare($sql);

    // Bind parameters using PDO's bindParam() method
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':user_id', $user_id);

    // Execute the query
    if ($stmt->execute()) {
        // Return a success response as JSON
        echo json_encode(['success' => true]);
    } else {
        // Return an error response as JSON
        echo json_encode(['success' => false]);
    }
} catch (PDOException $e) {
    // Return error message in JSON format
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
