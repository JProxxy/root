<?php
session_start();
header("Content-Security-Policy: default-src 'self' https://accounts.google.com; script-src 'self' https://accounts.google.com 'nonce-XYZ123';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

// Database connection
require_once __DIR__ . '/../app/config/connection.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Retrieve POST data from URL-encoded string
    $token = $_POST['token'] ?? '';
    $google_id = $_POST['google_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $profile_picture = $_POST['profile_picture'] ?? '';

    // Validate required fields
    if (empty($token) || empty($google_id) || empty($email) || empty($profile_picture)) {
        throw new Exception("Missing required fields");
    }

    // Verify Google token
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Ensure data consistency
    if ($email !== $payload['email'] || $google_id !== $payload['sub']) {
        throw new Exception("Data tampering detected");
    }

    // Sanitize inputs
    $google_id = htmlspecialchars($google_id, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    $first_name = htmlspecialchars(substr($first_name, 0, 50), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(substr($last_name, 0, 50), ENT_QUOTES, 'UTF-8');
    $profile_picture = filter_var($profile_picture, FILTER_VALIDATE_URL) ? $profile_picture : '';
    $username = $email; // Use email as username

    if (empty($email) || empty($google_id) || empty($profile_picture)) {
        throw new Exception("Invalid input data");
    }

    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Begin database transaction
    $conn->beginTransaction();

    try {
        // Check if the user already exists
        $stmt = $conn->prepare("
            SELECT user_id, google_id 
            FROM users 
            WHERE google_id = :google_id OR email = :email
        ");
        $stmt->execute([
            ':google_id' => $google_id,
            ':email' => $email
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
                ':google_id' => $google_id,
                ':email' => $email,
                ':username' => $username,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':profile_picture' => $profile_picture
            ]);
            $user_id = $conn->lastInsertId();
            if (!$user_id) {
                throw new Exception("Failed to get last inserted ID.");
            }
        } else {
            // Update existing user if necessary
            $user_id = $user['user_id'];
            if (empty($user['google_id'])) {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET google_id = :google_id 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':google_id' => $google_id,
                    ':user_id' => $user_id
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Session management
        session_regenerate_id(true);
        $_SESSION = [
            'user_id' => $user_id,
            'email' => $email,
            'username' => $username,
            'auth_method' => 'google',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'created' => time()
        ];

        // Redirect to dashboard
        $allowed_redirects = ['../templates/dashboard.php', '../templates/home.php'];
        $redirect = in_array($_GET['redirect'] ?? '', $allowed_redirects) ? $_GET['redirect'] : '../templates/dashboard.php';

        header("Location: $redirect");
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        throw new Exception("Database error: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    header("Location: ../templates/login.php?error=system_error");
    exit();
}
