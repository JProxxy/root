<?php
session_start();
header('Content-Type: application/json');

include '../app/config/connection.php'; // Ensure this uses PDO

$response = [];

try {
    // Check if request is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($input['password']) || !isset($input['retype_password'])) {
        throw new Exception("Missing required fields.");
    }

    $password = $input['password'];
    $retypePassword = $input['retype_password'];

    // Ensure passwords match
    if ($password !== $retypePassword) {
        throw new Exception("Passwords do not match.");
    }

    // Validate session
    if (!isset($_SESSION['reset_email'])) {
        throw new Exception("Session expired. Try again.");
    }

    $email = $_SESSION['reset_email'];

    // Encrypt password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the query using PDO
    $sql = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        // Clear session after password reset
        unset($_SESSION['reset_email']);
        $response = ["success" => true, "message" => "Password changed successfully!"];
    } else {
        throw new Exception("Failed to update password. Please try again.");
    }
} catch (Exception $e) {
    // Catch and return error messages
    $response = ["success" => false, "message" => $e->getMessage()];
}

// Return JSON response
echo json_encode($response);
exit();
?>
