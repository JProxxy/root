<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../app/config/connection.php';

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

// Initialize an array for error messages
$errors = [];

// Validate reCAPTCHA first
$recaptcha_secret = "6LcWnvEqAAAAAPPiyMaVPKIHb_DtNDdGUaSG_3fq"; // Replace with your reCAPTCHA Secret Key
$verify_url       = "https://www.google.com/recaptcha/api/siteverify";
$data             = [
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

$context         = stream_context_create($options);
$verify_response = file_get_contents($verify_url, false, $context);
$response_data   = json_decode($verify_response);

// Output the reCAPTCHA response to the browser console (for debugging)
echo "<script>console.log('reCAPTCHA response: " . json_encode($response_data) . "');</script>";

// If reCAPTCHA fails or score is too low, block registration
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
    // Map dropdown values to numeric role IDs
    $roleMap = [
        "admin"   => 1,
        "staff"   => 2,
        "student" => 3
    ];
    if (array_key_exists($roleSelect, $roleMap)) {
        $roleSelect = $roleMap[$roleSelect];
    } else {
        $errors[] = "Invalid role selection.";
    }
}

// If there are errors, output them via alert and stop processing
if (!empty($errors)) {
    $errorMsg = "Registration Errors:\n" . implode("\n", $errors);
    echo "<script>alert(" . json_encode($errorMsg) . ");</script>";
    exit;
}

// Check if a user with the same email already exists
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "<script>alert('Email already exists. Please try a different one.');</script>";
        exit;
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "<script>alert('System error. Please try again later.');</script>";
    exit;
}

// Hash the password using a secure algorithm
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database
try {
    $stmt = $conn->prepare("INSERT INTO users (email, password, role_id, created_at, updated_at) VALUES (:email, :password, :role_id, NOW(), NOW())");
    $stmt->execute([
        ':email'    => $email,
        ':password' => $hashedPassword,
        ':role_id'  => $roleSelect
    ]);

    // On success, output a JavaScript alert and then redirect to the login page when "OK" is clicked.
    echo "<script>
            alert('Signup successful!');
            window.location.href = '../templates/login.php';
          </script>";
    exit;
} catch (PDOException $e) {
    echo "<script>alert('Database Insert Error: " . addslashes($e->getMessage()) . "');</script>";
    exit;
}

?>
