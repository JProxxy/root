<?php
session_start();
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Start log
error_log("[" . date('Y-m-d H:i:s') . "] Google authentication process started.");

// Database connection
require_once __DIR__ . '/../app/config/connection.php';
error_log("[" . date('Y-m-d H:i:s') . "] Database connection initialized.");

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Invalid request method.");
        throw new Exception("Invalid request method");
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Request method validated.");

    // Get and validate token
    $token = $_POST['token'] ?? '';
    if (empty($token)) {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Missing authentication token.");
        throw new Exception("Missing authentication token");
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Google token received.");

    // Verify Google token
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Invalid Google token.");
        throw new Exception("Invalid Google token");
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Google token verified successfully.");

    // Validate required fields
    $required = ['google_id', 'email', 'first_name', 'profile_picture'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Missing required field: $field.");
            throw new Exception("Missing required field: $field");
        }
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Required fields validated.");

    // Data consistency check
    if ($_POST['email'] !== $payload['email'] || $_POST['google_id'] !== $payload['sub']) {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Data tampering detected.");
        throw new Exception("Data tampering detected");
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Data consistency check passed.");

    // Sanitize inputs
    $clean = [
        'google_id' => htmlspecialchars($_POST['google_id'], ENT_QUOTES, 'UTF-8'),
        'email' => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '',
        'first_name' => htmlspecialchars(substr($_POST['first_name'], 0, 50), ENT_QUOTES, 'UTF-8'),
        'last_name' => isset($_POST['last_name']) ?
            htmlspecialchars(substr($_POST['last_name'], 0, 50), ENT_QUOTES, 'UTF-8') : '',
        'profile_picture' => filter_var($_POST['profile_picture'], FILTER_VALIDATE_URL) ? $_POST['profile_picture'] : '',
        'username' => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : ''
    ];
    error_log("[" . date('Y-m-d H:i:s') . "] Input data sanitized.");

    // Ensure required fields are valid
    if (empty($clean['email']) || empty($clean['google_id']) || empty($clean['profile_picture'])) {
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Invalid input data.");
        throw new Exception("Invalid input data");
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Valid input data confirmed.");

    // Database operations
    $conn->beginTransaction();
    error_log("[" . date('Y-m-d H:i:s') . "] Database transaction started.");

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
            error_log("[" . date('Y-m-d H:i:s') . "] New user inserted with ID: $user_id");
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
                error_log("[" . date('Y-m-d H:i:s') . "] Existing user updated with Google ID.");
            }
        }

        $conn->commit();
        error_log("[" . date('Y-m-d H:i:s') . "] Database transaction committed.");

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
        error_log("[" . date('Y-m-d H:i:s') . "] Session initialized for user ID: $user_id");

        // Redirect to dashboard
        error_log("[" . date('Y-m-d H:i:s') . "] Redirecting user to dashboard.");
        header("Location: ../templates/dashboard.php");
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: Database error - " . $e->getMessage());
        throw new Exception("Database error: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    header("Location: ../templates/login.php?error=system_error");
    exit();
}
