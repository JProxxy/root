<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please try again.']);
    exit();
}

$email = $_SESSION['reset_email']; // Get email from session

// Include database connection
require_once '../app/config/connection.php';

// Read raw JSON data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if JSON data was received properly
if (!$data || !isset($data['password']) || !isset($data['retype_password'])) {
    echo json_encode(['success' => false, 'message' => 'Password fields are missing.']);
    exit();
}

$password = $data['password'];
$retype_password = $data['retype_password'];

// Check if passwords match
if ($password !== $retype_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

// Hash the new password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Prepare SQL query to update the password for the user with the given email
    $sql = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $pdo->prepare($sql);

    // Bind the parameters to the SQL query
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

    // Execute the query
    if ($stmt->execute()) {
        // Password successfully updated
        echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
    } else {
        // If the query fails
        echo json_encode(['success' => false, 'message' => 'Error updating password. Please try again.']);
    }
} catch (PDOException $e) {
    // Handle any database connection errors
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
