<?php
session_start();
header("Content-Type: application/json");

// Optionally disable error output for production
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Database connection

// Get the OTP from POST data
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

// Use the new email from session (the one that the OTP was sent to)
$email = isset($_SESSION['reset_email']) ? trim($_SESSION['reset_email']) : '';

if (empty($email) || empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "Email and OTP are required."]);
    exit();
}

// Ensure the current user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}
$currentUserId = $_SESSION['user_id'];

// Fetch the OTP details from the current user's record (using user_id)
$stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE user_id = :userId");
$stmt->bindParam(":userId", $currentUserId, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(["success" => false, "message" => "User record not found."]);
    exit();
}

$storedOTP = trim(strval($row['otp_code']));
$otpExpiry = strtotime($row['otp_expiry']);
$currentTime = time();

// Check if the OTP has expired
if ($currentTime > $otpExpiry) {
    echo json_encode(["success" => false, "message" => "OTP has expired. Please request a new one."]);
    exit();
}

// Compare the entered OTP with the stored OTP
if ($enteredOTP === $storedOTP) {
    // OTP is correct. Now, check if any other user (with a different user_id) already uses this new email.
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE email = :email AND user_id != :userId");
    $stmtCheck->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtCheck->bindParam(":userId", $currentUserId, PDO::PARAM_INT);
    $stmtCheck->execute();
    $rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($rowCheck['cnt'] > 0) {
        echo json_encode(["success" => false, "message" => "Cannot verify, Email already in use."]);
        exit();
    }
    
    // No duplicate found; update the current user's email with the new verified email
    $stmtUpdate = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :userId");
    $stmtUpdate->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtUpdate->bindParam(":userId", $currentUserId, PDO::PARAM_INT);
    $stmtUpdate->execute();
    
    echo json_encode(["success" => true, "message" => "OTP verified successfully and email updated."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
}

$stmt->closeCursor();
$conn = null;
exit();
?>
