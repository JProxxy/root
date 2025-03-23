<?php
session_start();
header("Content-Type: application/json");

// Disable error output
error_reporting(0);
ini_set('display_errors', 0);

include '../app/config/connection.php';

// Retrieve data from session and POST
$email = $_SESSION['reset_email'] ?? '';
$enteredOTP = trim($_POST['otp'] ?? '');

// Validation: Check OTP presence
if (empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "OTP is required."]);
    exit();
}

// Fetch stored OTP from database
$stmt = $conn->prepare("SELECT otp_code, otp_expiry, user_id FROM users WHERE email = :email");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->execute();
$userData = $stmt->fetch(PDO::PARAM_STR);

if (!$userData) {
    echo json_encode(["success" => false, "message" => "Account not found."]);
    exit();
}

// Validate OTP expiration
$currentTime = time();
if ($currentTime > strtotime($userData['otp_expiry'])) {
    echo json_encode(["success" => false, "message" => "Expired OTP. Request a new one."]);
    exit();
}

// Verify OTP match
if ($enteredOTP !== $userData['otp_code']) {
    echo json_encode(["success" => false, "message" => "Invalid OTP."]);
    exit();
}

// Verify user session exists
if (empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please login again."]);
    exit();
}

// Check email availability
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND user_id != :currentUserId");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->bindParam(":currentUserId", $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetchColumn() > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered."]);
    exit();
}

// Update user email
$stmt = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :userId");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->bindParam(":userId", $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Email updated successfully!"]);
exit();
?>