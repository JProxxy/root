<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../app/config/connection.php';
// PHPMailer imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.');</script>";
    exit;
}

// Retrieve and trim form fields
$email             = trim($_POST['email'] ?? '');
$password          = trim($_POST['password'] ?? '');
$retypePassword    = trim($_POST['retype_password'] ?? '');
$roleSelect        = trim($_POST['roleSelect'] ?? '');
$recaptchaResponse = $_POST['recaptcha_response'] ?? '';

// Extract username from email (remove the "@rivaniot.online" portion)
$username = strstr($email, '@', true);

// Initialize an array for error messages
$errors = [];

// Validate reCAPTCHA
$recaptcha_secret = "6LcWnvEqAAAAAPPiyMaVPKIHb_DtNDdGUaSG_3fq";
$verify_url       = "https://www.google.com/recaptcha/api/siteverify";
$data             = ['secret' => $recaptcha_secret, 'response' => $recaptchaResponse];
$options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded", 'method' => 'POST', 'content' => http_build_query($data)]];
$context         = stream_context_create($options);
$verify_response = file_get_contents($verify_url, false, $context);
$response_data   = json_decode($verify_response);

if (!$response_data->success || $response_data->score < 0.5) {
    $errors[] = "reCAPTCHA verification failed. Please try again.";
}

// Basic validations
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
}

if ($password !== $retypePassword) {
    $errors[] = "Passwords do not match.";
}

if (empty($roleSelect)) {
    $errors[] = "Role selection is required.";
} else {
    $roleMap = ["admin" => 2, "staff" => 3, "student" => 4];
    if (isset($roleMap[$roleSelect])) {
        $roleSelect = $roleMap[$roleSelect];
    } else {
        $errors[] = "Invalid role selection.";
    }
}

if (!empty($errors)) {
    $errorMsg = "Registration Errors:\n" . implode("\n", $errors);
    echo "<script>alert(" . json_encode($errorMsg) . ");</script>";
    exit;
}

// Check existing user
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1");
    $stmt->execute([':email' => $email, ':username' => $username]);
    if ($stmt->fetch()) {
        echo "<script>alert('Email or Username already exists.');</script>";
        exit;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "<script>alert('System error.');</script>";
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, created_at, updated_at) VALUES (:username, :email, :password, :role_id, NOW(), NOW())");
    $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hashedPassword, ':role_id' => $roleSelect]);

    // Send confirmation email with credentials
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'superadmin@rivaniot.online';
        $mail->Password   = 'superAdmin0507!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('superadmin@rivaniot.online', 'Rivan IoT');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Your Rivan IoT Account Details';
        $mail->Body    = "<p>Dear {$username},</p>" .
                         "<p>Your account has been created successfully. Here are your login details:</p>" .
                         "<p><strong>Email:</strong> {$email}<br>" .
                         "<strong>Password:</strong> {$password}</p>" .
                         "<p>Please keep this information secure.</p>";
        $mail->send();
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }

    echo "<script>alert('Signup successful! Check your email.'); window.location.href = '../templates/login.php';</script>";
    exit;
} catch (PDOException $e) {
    echo "<script>alert('Database Insert Error: " . addslashes($e->getMessage()) . "');</script>";
    exit;
}
?>
