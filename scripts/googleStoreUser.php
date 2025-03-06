<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php");
    exit();
}
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Database connection
require_once __DIR__ . '/../app/config/connection.php';

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Get and validate token
    $token = $_POST['token'] ?? '';
    if (empty($token)) {
        throw new Exception("Missing authentication token");
    }

    // Verify Google token
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Validate required fields
    $required = ['google_id', 'email', 'first_name', 'profile_picture'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Data consistency check
    if ($_POST['email'] !== $payload['email'] || $_POST['google_id'] !== $payload['sub']) {
        throw new Exception("Data tampering detected");
    }

    // Sanitize inputs (PHP 8.1+ compatible)
    $clean = [
        'google_id' => filter_var($_POST['google_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'first_name' => substr(filter_var($_POST['first_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS), 0, 50),
        'last_name' => isset($_POST['last_name']) ? 
            substr(filter_var($_POST['last_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS), 0, 50) : '',
        'profile_picture' => filter_var($_POST['profile_picture'], FILTER_SANITIZE_URL),
        'username' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) // Username = Email
    ];

    // Database operations
    $conn->beginTransaction();
    
    try {
        // Check existing user
        $stmt = $conn->prepare("
            SELECT user_id, google_id 
            FROM users 
            WHERE google_id = :google_id OR email = :email
        ");
        $stmt->execute([
            ':google_id' => $clean['google_id'],
            ':email' => $clean['email']
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Insert new user
            $stmt = $conn->prepare("
                INSERT INTO users 
                (google_id, email, username, first_name, last_name, profile_picture, created_at)
                VALUES (:google_id, :email, :username, :first_name, :last_name, :profile_picture, NOW())
            ");
            
            $stmt->execute([
                ':google_id' => $clean['google_id'],
                ':email' => $clean['email'],
                ':username' => $clean['username'],
                ':first_name' => $clean['first_name'],
                ':last_name' => $clean['last_name'],
                ':profile_picture' => $clean['profile_picture']
            ]);
            $user_id = $conn->lastInsertId();
        } else {
            // Update existing user
            $user_id = $user['user_id'];
            if (empty($user['google_id'])) {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET google_id = :google_id 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':google_id' => $clean['google_id'],
                    ':user_id' => $user_id
                ]);
            }
        }

        $conn->commit();

        // Session management
        session_regenerate_id(true);
        $_SESSION = [
            'user_id' => $user_id,
            'email' => $clean['email'],
            'username' => $clean['username'],
            'auth_method' => 'google',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'created' => time()
        ];

        // Redirect to dashboard
        header("Location: ../templates/dashboard.php");
        exit();

    } catch(PDOException $e) {
        $conn->rollBack();
        throw new Exception("Database error: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    header("Location: ../templates/login.php?error=system_error");
    exit();
}