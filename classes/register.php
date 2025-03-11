<?php
session_start();
require_once '../app/config/connection.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Retrieve and trim form fields
$username       = trim($_POST['username'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phoneNumber    = trim($_POST['phoneNumber'] ?? '');
$password       = trim($_POST['password'] ?? '');
$retypePassword = trim($_POST['retype_password'] ?? '');
$recaptchaResponse = $_POST['recaptcha_response'] ?? ''; // Get reCAPTCHA token

// Initialize an array for error messages
$errors = [];

// 🔹 Validate reCAPTCHA first
$recaptcha_secret = "6LcWnvEqAAAAAPPiyMaVPKIHb_DtNDdGUaSG_3fq"; // Replace with your reCAPTCHA Secret Key
$verify_url = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptchaResponse
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$verify_response = file_get_contents($verify_url, false, $context);
$response_data = json_decode($verify_response);

// If reCAPTCHA fails or score is too low, block registration
if (!$response_data->success || $response_data->score < 0.5) {
    die("<p>reCAPTCHA verification failed. Please try again.</p>");
}

// Basic validations
if (empty($username)) {
    $errors[] = "Username is required.";
}
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}
if (empty($phoneNumber)) {
    $errors[] = "Phone number is required.";
}
if (empty($password)) {
    $errors[] = "Password is required.";
}
if ($password !== $retypePassword) {
    $errors[] = "Passwords do not match.";
}

// If there are errors, display them and stop processing
if (!empty($errors)) {
    echo "<h3>Registration Errors:</h3>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    exit;
}

// Check if a user with the same username or email already exists
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email
    ]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "<p>Username or Email already exists. Please try a different one.</p>";
        exit;
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "<p>System error. Please try again later.</p>";
    exit;
}

// Hash the password using a secure algorithm
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database
try {
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, phoneNumber, created_at, updated_at, role_id) VALUES (:username, :password, :email, :phoneNumber, NOW(), NOW(), 2)");
    $stmt->execute([
        ':username'    => $username,
        ':password'    => $hashedPassword,
        ':email'       => $email,
        ':phoneNumber' => $phoneNumber
    ]);

    // On success, redirect to the login page
    header("Location: ../templates/login.php");
    exit;
} catch (PDOException $e) {
    error_log("Database Insert Error: " . $e->getMessage());
    echo "<p>System error. Please try again later.</p>";
    exit;
}
?>
