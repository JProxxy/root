<?php
session_start();
require_once '../app/config/connection.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.');</script>";
    exit;
}

// Retrieve and trim form fields
$username       = trim($_POST['username'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phoneNumber    = trim($_POST['phoneNumber'] ?? '');
$password       = trim($_POST['password'] ?? '');
$retypePassword = trim($_POST['retype_password'] ?? '');
$recaptchaResponse = $_POST['recaptcha_response'] ?? '';

// Initialize an array for error messages
$errors = [];

// Validate reCAPTCHA
$recaptcha_secret = "6LcWnvEqAAAAAPPiyMaVPKIHb_DtNDdGUaSG_3fq"; // Replace with your actual secret key
$verify_url = "https://www.google.com/recaptcha/api/siteverify";

$data = [
    'secret'   => $recaptcha_secret,
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

// Check reCAPTCHA success and score
if (!$response_data || !isset($response_data->success) || !$response_data->success || (isset($response_data->score) && $response_data->score < 0.5)) {
    $errors[] = "reCAPTCHA verification failed. Please try again.";
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

// Password strength check (at least 8 chars, 1 uppercase, 1 number, 1 special character)
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
    $errors[] = "Password must be at least 8 characters long, contain a capital letter, a number, and a special character.";
}

// If there are errors, alert them and stop processing
if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
    exit;
}

// Check if a user with the same username or email already exists
try {
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute([':username' => $username, ':email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "<script>alert('Username or Email already exists. Please try a different one.');</script>";
        exit;
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "<script>alert('System error. Please try again later.');</script>";
    exit;
}

// Hash the password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database
try {
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, phoneNumber, created_at, updated_at, role_id) 
                            VALUES (:username, :password, :email, :phoneNumber, NOW(), NOW(), 2)");
    $stmt->execute([
        ':username'   => $username,
        ':password'   => $hashedPassword,
        ':email'      => $email,
        ':phoneNumber'=> $phoneNumber
    ]);

    echo "<script>
            alert('Signup successful!');
            window.location.href = '../templates/login.php';
          </script>";
    exit;
} catch (PDOException $e) {
    error_log("Database Insert Error: " . $e->getMessage());
    echo "<script>alert('System error. Please try again later.');</script>";
    exit;
}
?>
