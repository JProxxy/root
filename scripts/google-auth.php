<?php
session_start();

// Set headers for CORS and JSON output
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

// Enable error reporting and logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

require_once __DIR__ . '/../vendor/autoload.php';

// Include PDO connection
require_once __DIR__ . '/../app/config/connection.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Retrieve POST data (using URL-encoded form data or JSON)
    // Here we expect JSON:
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!$data || !isset($data['token']) || !isset($data['google_id']) || !isset($data['email']) || !isset($data['profile_picture'])) {
        throw new Exception("Missing required fields");
    }

    // Extract data from the request
    $token = $data['token'];
    $google_id = $data['google_id'];
    $email = $data['email'];
    $first_name = $data['first_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $profile_picture = $data['profile_picture'];

    // Verify Google token using the Google API client
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
    $username = $email; // Using the email as username for simplicity

    if (empty($email) || empty($google_id) || empty($profile_picture)) {
        throw new Exception("Invalid input data");
    }

    // Begin a database transaction
    $conn->beginTransaction();

    try {
        // Check if the user already exists by Google ID or email
        $stmt = $conn->prepare("SELECT user_id, google_id FROM users WHERE google_id = :google_id OR email = :email");
        $stmt->execute([
            ':google_id' => $google_id,
            ':email' => $email
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Insert a new user if none exists
            $stmt = $conn->prepare("INSERT INTO users (google_id, email, username, first_name, last_name, profile_picture, created_at)
                VALUES (:google_id, :email, :username, :first_name, :last_name, :profile_picture, NOW())");
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
                throw new Exception("Failed to get user ID.");
            }
        } else {
            // If user exists but Google ID is missing, update it
            $user_id = $user['user_id'];
            if (empty($user['google_id'])) {
                $stmt = $conn->prepare("UPDATE users SET google_id = :google_id WHERE user_id = :user_id");
                $stmt->execute([
                    ':google_id' => $google_id,
                    ':user_id' => $user_id
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Regenerate session ID and set session variables
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => $user_id,
            'email' => $email,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'profile_picture' => $profile_picture,
            'auth_method' => 'google',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'created' => time()
        ];

        // Redirect to dashboard (or return JSON response)
        echo json_encode([
            'success' => true,
            'email' => $email,
            'name' => $first_name . ' ' . $last_name,
            'sub' => $google_id
        ]);
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        throw new Exception("Database error: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    // On error, redirect to login with an error parameter (or you could return JSON)
    header("Location: ../templates/login.php?error=system_error");
    exit();
}
?>
