<?php
session_start();
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/connection.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate content type
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') === false) {
        throw new Exception("Invalid content type");
    }

    // Get and validate JSON input
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    if (!isset($data['token'])) {
        throw new Exception("Missing authentication token");
    }

    // Initialize Google Client
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $token = $data['token'];
    
    // Verify ID token
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Validate token payload structure
    $requiredClaims = ['sub', 'email', 'given_name', 'family_name', 'picture'];
    foreach ($requiredClaims as $claim) {
        if (!isset($payload[$claim])) {
            throw new Exception("Missing required token claim: $claim");
        }
    }

    // Sanitize and validate user data
    $googleId = filter_var($payload['sub'], FILTER_SANITIZE_STRING);
    $email = filter_var($payload['email'], FILTER_SANITIZE_EMAIL);
    $firstName = filter_var($payload['given_name'], FILTER_SANITIZE_STRING);
    $lastName = filter_var($payload['family_name'], FILTER_SANITIZE_STRING);
    $profilePicture = filter_var($payload['picture'], FILTER_SANITIZE_URL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Database transaction
    $conn->beginTransaction();

    try {
        // Check existing user
        $stmt = $conn->prepare("
            SELECT user_id, google_id, email 
            FROM users 
            WHERE google_id = :google_id OR email = :email
            FOR UPDATE
        ");
        $stmt->execute([
            ':google_id' => $googleId,
            ':email' => $email
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Create new user
            $stmt = $conn->prepare("
                INSERT INTO users 
                (google_id, email, username, first_name, last_name, profile_picture, created_at)
                VALUES (:google_id, :email, :username, :first_name, :last_name, :profile_picture, NOW())
            ");

            $username = explode('@', $email)[0];
            $username = substr(preg_replace('/[^a-zA-Z0-9_]/', '', $username), 0, 20);

            $stmt->execute([
                ':google_id' => $googleId,
                ':email' => $email,
                ':username' => $username,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':profile_picture' => $profilePicture
            ]);
            $userId = $conn->lastInsertId();
        } else {
            $userId = $user['user_id'];
            
            // Update existing user if needed
            if (empty($user['google_id'])) {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET google_id = :google_id 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':google_id' => $googleId,
                    ':user_id' => $userId
                ]);
            }
        }

        $conn->commit();

        // Regenerate session ID
        session_regenerate_id(true);

        // Set secure session parameters
        $_SESSION = [];
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['google_id'] = $googleId;
        $_SESSION['auth_method'] = 'google';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['created_at'] = time();

        // Set secure cookies
        setcookie(session_name(), session_id(), [
            'expires' => time() + 86400,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'redirect' => '../templates/dashboard.php'
        ]);
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Google Auth Error: " . $e->getMessage() . 
             " - IP: " . $_SERVER['REMOTE_ADDR'] . 
             " - User Agent: " . $_SERVER['HTTP_USER_AGENT']);

    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication failed: ' . $e->getMessage()
    ]);
    exit();
}