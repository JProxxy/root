<?php
// 1. Start Session & Setup:
session_start();
header("Content-Type: application/json");

// Suppress extra error messages
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include your database connection

// Ensure user is logged in before proceeding
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}

$currentUserId = $_SESSION['user_id'];
$email = isset($_SESSION['reset_email']) ? trim($_SESSION['reset_email']) : '';
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (empty($email) || empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "Email and OTP are required."]);
    exit();
}

// 2. Retrieve OTP Details:
// Query the database for the stored OTP and its expiry using the user_id.
// (Assumes you store OTP details in a separate table 'users' keyed by user_id.)
$stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE user_id = :user_id");
$stmt->bindParam(":user_id", $currentUserId, PDO::PARAM_INT);
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
// Compare the current time with the OTP expiry time.
if ($currentTime > $otpExpiry) {
    echo json_encode(["success" => false, "message" => "OTP has expired. Please request a new one."]);
    exit();
}

// 4. OTP Verification:
// Compare the entered OTP with the stored OTP.
if ($enteredOTP !== $storedOTP) {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
    exit();
}

// 5. User & Email Validation:
// Verify that no other user is already using this email.
$stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE email = :email AND user_id != :user_id");
$stmtCheck->bindParam(":email", $email, PDO::PARAM_STR);
$stmtCheck->bindParam(":user_id", $currentUserId, PDO::PARAM_INT);
$stmtCheck->execute();
$rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if ($rowCheck['cnt'] > 0) {
    echo json_encode(["success" => false, "message" => "Email already in use by another account."]);
    exit();
}

// 6. Update Email:
// Update the logged-in user’s email in the database.
$stmtUpdate = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :user_id");
$stmtUpdate->bindParam(":email", $email, PDO::PARAM_STR);
$stmtUpdate->bindParam(":user_id", $currentUserId, PDO::PARAM_INT);
$stmtUpdate->execute();

// Return a success message indicating the email update.
echo json_encode(["success" => true, "message" => "OTP verified successfully and email updated."]);

$stmt->closeCursor();
$conn = null;
exit();
?>
