<?php
session_start();
header('Content-Type: application/json'); // ✅ Ensure the response is JSON

include '../app/config/connection.php'; // Database connection

$response = array(); // Initialize response array

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($input['password']) || !isset($input['retype_password'])) {
        $response = array("success" => false, "message" => "Missing required fields.");
        echo json_encode($response);
        exit();
    }

    $password = $input['password'];
    $retypePassword = $input['retype_password'];

    // Ensure passwords match
    if ($password !== $retypePassword) {
        $response = array("success" => false, "message" => "Passwords do not match.");
        echo json_encode($response);
        exit();
    }

    // Encrypt password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Get email from session
    if (!isset($_SESSION['reset_email'])) {
        $response = array("success" => false, "message" => "Session expired. Try again.");
        echo json_encode($response);
        exit();
    }

    $email = $_SESSION['reset_email'];

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        $response = array("success" => true, "message" => "Password changed successfully!");
        unset($_SESSION['reset_email']); // Remove reset session
    } else {
        $response = array("success" => false, "message" => "Database error. Try again.");
    }

    $stmt->close();
} else {
    $response = array("success" => false, "message" => "Invalid request method.");
}

// Return JSON response
echo json_encode($response);
exit();
?>
