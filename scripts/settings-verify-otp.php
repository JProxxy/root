<?php
// 1. Start Session & Setup:
session_start();
header("Content-Type: application/json");

// Suppress extra error messages
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include database connection

// Get email from session and OTP from POST data
$email = isset($_SESSION['reset_email']) ? trim($_SESSION['reset_email']) : '';
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

// Check for required inputs
if (empty($email) || empty($enteredOTP)) {
    echo json_encode(["success" => false, "message" => "Email and OTP are required."]);
    exit();
}

// 2. Retrieve OTP Details:
// Query the database for the stored OTP and its expiry for the provided email.
$stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = :email");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Return error if no record found for email
if (!$row) {
    echo json_encode(["success" => false, "message" => "No account found with this email."]);
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
// Ensure the user is logged in by checking the session for a user ID.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}
$currentUserId = $_SESSION['user_id'];

// Verify that no other user is already using this email.
$stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE email = :email AND user_id != :currentUserId");
$stmtCheck->bindParam(":email", $email, PDO::PARAM_STR);
$stmtCheck->bindParam(":currentUserId", $currentUserId, PDO::PARAM_INT);
$stmtCheck->execute();
$rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if ($rowCheck['cnt'] > 0) {
    echo json_encode(["success" => false, "message" => "Cannot verify, Email already in use."]);
    exit();
}

// 6. Update Email:
// If all checks pass, update the logged-in user’s email in the database.
$stmtUpdate = $conn->prepare("UPDATE users SET email = :email WHERE user_id = :currentUserId");
$stmtUpdate->bindParam(":email", $email, PDO::PARAM_STR);
$stmtUpdate->bindParam(":currentUserId", $currentUserId, PDO::PARAM_INT);
$stmtUpdate->execute();

// Return a success message indicating the email update.
echo json_encode(["success" => true, "message" => "OTP verified successfully and email updated."]);

$stmt->closeCursor();
$conn = null;
exit();
?>
