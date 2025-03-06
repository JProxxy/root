<?php
/* [1] VERIFY REQUEST LEGITIMACY */
session_start();
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Validate POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit('Invalid request method');
}

/* [2] AUTHENTICATE WITH GOOGLE SERVERS */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/connection.php';

$client = new Google_Client(['client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com']);
$token = $_POST['token'] ?? '';

if (empty($token)) {
    header("HTTP/1.1 400 Bad Request");
    exit('Missing authentication token');
}

$payload = $client->verifyIdToken($token);
if (!$payload) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Invalid Google token');
}

/* [3] VALIDATE DATA INTEGRITY */
$required = ['google_id', 'email', 'first_name', 'last_name', 'profile_picture'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {  // Fixed missing parenthesis
        error_log("Missing field: $field");
        header("Location: ../templates/login.php?error=invalid_data");
        exit();
    }
}

if ($_POST['email'] !== $payload['email'] || $_POST['google_id'] !== $payload['sub']) {
    error_log("Data mismatch: {$_POST['email']} vs {$payload['email']}");
    header("Location: ../templates/login.php?error=data_tampered");
    exit();
}

/* [4] SANITIZE ALL INPUTS */
$clean = [
    'google_id' => filter_var($_POST['google_id'], FILTER_SANITIZE_STRING),
    'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
    'first_name' => substr(filter_var($_POST['first_name'], FILTER_SANITIZE_STRING), 0, 50),
    'last_name' => substr(filter_var($_POST['last_name'], FILTER_SANITIZE_STRING), 0, 50),
    'profile_picture' => filter_var($_POST['profile_picture'], FILTER_SANITIZE_URL)
];

/* [5] SECURE DATABASE OPERATIONS */
try {
    $conn->beginTransaction();
    
    // Check existing user
    $stmt = $conn->prepare("SELECT user_id, google_id FROM users WHERE google_id = ? OR email = ?");
    $stmt->execute([$clean['google_id'], $clean['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_id = null;
    if (!$user) {
        // Create new user
        $username = preg_replace('/[^a-z0-9_]/', '', strtolower(explode('@', $clean['email'])[0]));
        $stmt = $conn->prepare("INSERT INTO users (google_id, email, username, first_name, last_name, profile_picture) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $clean['google_id'],
            $clean['email'],
            substr($username, 0, 20),
            $clean['first_name'],
            $clean['last_name'],
            $clean['profile_picture']
        ]);
        $user_id = $conn->lastInsertId();
    } else {
        // Update existing user
        $user_id = $user['user_id'];
        if (empty($user['google_id'])) {
            $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
            $stmt->execute([$clean['google_id'], $user_id]);
        }
    }
    
    $conn->commit();

} catch(PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    header("Location: ../templates/login.php?error=db_error");
    exit();
}

/* [6] PROTECT SESSION CREATION */
session_regenerate_id(true);
$_SESSION = [
    'user_id' => $user_id,
    'email' => $clean['email'],
    'auth_method' => 'google',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'agent' => $_SERVER['HTTP_USER_AGENT'],
    'created' => time()
];

/* [7] SAFE ERROR HANDLING -> [8] GO TO DASHBOARD */
header("Location: ../templates/dashboard.php");
exit();