<?php
session_start();
header("Content-Type: application/json");

// error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
// ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include your database connection

// Retrieve email and OTP from POST data
// You can either pass email along with OTP or use a session variable
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
    echo json_encode(["success" => true, "message" => "OTP verified successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
}

$stmt->closeCursor();
$conn = null;
?>
<?php
session_start();
header("Content-Type: application/json");

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include your database connection

// Retrieve email and OTP from POST data
// You can either pass email along with OTP or use a session variable
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
    echo json_encode(["success" => true, "message" => "OTP verified successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP. Please try again."]);
}

$stmt->closeCursor();
$conn = null;

