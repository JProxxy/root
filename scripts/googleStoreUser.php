<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/connection.php';

// Security headers
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

try {
    // Validate POST data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    if (!isset($_POST['token'])) {
        throw new Exception("Missing authentication token");
    }

    // Initialize Google Client
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $token = $_POST['token'];
    
    // Verify ID token
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Validate POST data matches token claims
    $requiredFields = ['google_id', 'email', 'first_name', 'last_name', 'profile_picture'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    if ($_POST['email'] !== $payload['email'] || $_POST['google_id'] !== $payload['sub']) {
        throw new Exception("Data tampering detected");
    }

    // Sanitize inputs
    $googleId = filter_var($_POST['google_id'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $firstName = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $lastName = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $profilePicture = filter_var($_POST['profile_picture'], FILTER_SANITIZE_URL);

    // Database operations
    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
        $stmt->execute([$googleId, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = $conn->prepare("INSERT INTO users 
                (google_id, email, username, first_name, last_name, profile_picture, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())");

            $username = substr(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]), 0, 20);
            $stmt->execute([$googleId, $email, $username, $firstName, $lastName, $profilePicture]);
            $userId = $conn->lastInsertId();
        } else {
            $userId = $user['user_id'];
            if (empty($user['google_id'])) {
                $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
                $stmt->execute([$googleId, $userId]);
            }
        }

        $conn->commit();

        // Session management
        session_regenerate_id(true);
        $_SESSION = [
            'user_id' => $userId,
            'email' => $email,
            'google_id' => $googleId,
            'auth_method' => 'google',
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => time()
        ];

        // Redirect to dashboard
        header('Location: ../templates/dashboard.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . 
             " - IP: " . $_SERVER['REMOTE_ADDR'] . 
             " - UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    
    header('Location: ../templates/login.php?error=' . urlencode('Authentication failed: System error'));
    exit();
}