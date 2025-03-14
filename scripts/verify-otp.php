<?php
session_start();
header("Content-Type: application/json");

include '../app/config/connection.php';  // Include database connection

// Get OTP from request
$enteredOTP = isset($_POST['otp']) ? trim($_POST['otp']) : '';

// Validate input
if (empty($enteredOTP) || strlen($enteredOTP) !== 5) {
    echo json_encode(["success" => false, "message" => "Invalid OTP format"]);
    exit();
}

// Retrieve stored OTP from the session or database
$email = $_SESSION['reset_email'] ?? null;  // Email should be stored when OTP is sent

if (!$email) {
    echo json_encode(["success" => false, "message" => "Session expired. Please request a new OTP."]);
    exit();
}

// Fetch OTP details from the database
$stmt = $conn->prepare("SELECT user_id, email, otp_code, otp_expiry FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $userId = $row['user_id'];
    $storedOTP = $row['otp_code'];
    $otpExpiry = strtotime($row['otp_expiry']);
    $currentTime = time();

    // Check if OTP is correct
    if ($enteredOTP === $storedOTP) {
        // Check if OTP has expired
        if ($currentTime > $otpExpiry) {
            echo json_encode(["success" => false, "message" => "OTP has expired. Request a new one."]);
        } else {
            // OTP is valid, allow password reset
            $_SESSION['verified_user_id'] = $userId;  // Store user_id for reset-password process
            echo json_encode(["success" => true, "message" => "OTP verified successfully."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect OTP. Please try again."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Email not found."]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
