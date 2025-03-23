<?php
// 1. Start Session & Setup:
session_start();
header("Content-Type: application/json");

// Suppress extra error messages
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}
$user_id = $_SESSION['user_id'];

// Retrieve OTP from POST data
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';
if (empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "OTP is required."]);
    exit();
}

// 2. Fetch Stored OTP Details using user_id from the users table
$stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE user_id = :user_id");
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(["success" => false, "message" => "OTP details not found."]);
    exit();
}

$storedOTP = trim(strval($row['otp_code']));
$otpExpiry = strtotime($row['otp_expiry']);
$currentTime = time();

// 3. OTP Expiry Check:
if ($currentTime > $otpExpiry) {
    echo json_encode(["success" => false, "message" => "OTP has expired. Please request a new one."]);
    exit();
}

// 4. OTP Verification:
if ($enteredOTP !== $storedOTP) {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
    exit();
}

// ***** OTP Verified Successfully *****
//
// At this point, the OTP is verified successfully.
// Now, proceed to email update if needed.
if (isset($_SESSION['reset_email']) && !empty($_SESSION['reset_email'])) {
    $email = trim($_SESSION['reset_email']);
    
    // Email Uniqueness Check: Ensure no other user already uses this email.
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE email = :email AND user_id != :user_id");
    $stmtCheck->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtCheck->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    $rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($rowCheck['cnt'] > 0) {
        echo json_encode(["success" => false, "message" => "Email already in use by another account."]);
        exit();
    }
    
    // Update Email:
    $stmtUpdate = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :user_id");
    $stmtUpdate->bindParam(":email", $email, PDO::PARAM_STR);
    $stmtUpdate->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmtUpdate->execute();
    
    echo json_encode(["success" => true, "message" => "OTP verified successfully and email updated."]);
} else {
    echo json_encode(["success" => true, "message" => "OTP verified successfully."]);
}

$stmt->closeCursor();
$conn = null;
exit();
?>
