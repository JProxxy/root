<?php
session_start();
header("Content-Type: application/json");

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include database connection

// Get OTP from request
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (empty($enteredOTP) || strlen($enteredOTP) !== 5) {
    echo json_encode(["success" => false, "message" => "Invalid OTP format"]);
    exit();
}

// Retrieve stored email from session
if (!isset($_SESSION['reset_email'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please request a new OTP."]);
    exit();
}

$email = $_SESSION['reset_email'];

// Fetch OTP details from the database
$stmt = $conn->prepare("SELECT user_id, otp_code, otp_expiry FROM users WHERE email = :email");
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $userId = $row['user_id'];
    $storedOTP = trim(strval($row['otp_code']));
    $otpExpiry = strtotime($row['otp_expiry']);
    $currentTime = time();

    // Debugging logs
    error_log("Entered OTP: " . $enteredOTP);
    error_log("Stored OTP: " . $storedOTP);

    // Check if OTP is correct
    if ($enteredOTP === $storedOTP) {
        if ($currentTime > $otpExpiry) {
            echo json_encode(["success" => false, "message" => "OTP has expired. Request a new one."]);
        } else {
            $_SESSION['verified_user_id'] = $userId; // Store verified user ID
            echo json_encode(["success" => true, "message" => "OTP verified successfully."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect OTP. Please try again."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Email not found."]);
}

// Close database connection
$stmt->closeCursor();
$conn = null;
?>
