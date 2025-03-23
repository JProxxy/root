<?php
session_start();
header("Content-Type: application/json");

// Optionally disable error output to avoid extra text in response
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include your database connection

// Retrieve email and OTP from POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (empty($email) || empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "Email and OTP are required."]);
    exit();
}

// Fetch the OTP details from the database for the provided email
$stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = :email");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(["success" => false, "message" => "No account found with this email."]);
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

    // Ensure the user is logged in so we know which account to update
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "User not logged in."]);
        exit();
    }
    $currentUserId = $_SESSION['user_id'];

    // Check if any other user (different from the current user) already uses this email
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE email = :email AND user_id != :currentUserId");
    $stmtCheck->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtCheck->bindParam(":currentUserId", $currentUserId, PDO::PARAM_INT);
    $stmtCheck->execute();
    $rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($rowCheck['cnt'] > 0) {
        echo json_encode(["success" => false, "message" => "Cannot verify, Email already in use."]);
        exit();
    }

    // Update the current user's email column with the verified email
    $stmtUpdate = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :currentUserId");
    $stmtUpdate->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtUpdate->bindParam(":currentUserId", $currentUserId, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode(["success" => true, "message" => "OTP verified successfully and email updated."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
}

$stmt->closeCursor();
$conn = null;
exit();
?>
