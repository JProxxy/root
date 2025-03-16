<?php
session_start();

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/google-auth-errors.log');

const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/connection.php'; // Ensure PDO `$conn` is initialized

try {
    // Ensure the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Read JSON payload from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['token'])) {
        throw new Exception("Missing Google token");
    }

    $token = $data['token'];

    // Verify Google token
    $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
    $payload = $client->verifyIdToken($token);
    
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Extract user data from the token
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $first_name = $payload['given_name'] ?? '';
    $last_name = $payload['family_name'] ?? '';
    $profile_picture = $payload['picture'] ?? '';

    // Begin database transaction
    $conn->beginTransaction();

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT user_id, google_id FROM users WHERE google_id = :google_id OR email = :email");
    $stmt->execute([
        ':google_id' => $google_id,
        ':email' => $email
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Insert new user if they don't exist
        $stmt = $conn->prepare("INSERT INTO users (google_id, email, username, first_name, last_name, profile_picture, created_at)
            VALUES (:google_id, :email, :username, :first_name, :last_name, :profile_picture, NOW())");
        $stmt->execute([
            ':google_id' => $google_id,
            ':email' => $email,
            ':username' => $email, // Using email as username
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':profile_picture' => $profile_picture
        ]);
        $user_id = $conn->lastInsertId();
    } else {
        $user_id = $user['user_id'];

        // If user exists but doesn't have a Google ID, update it
        if (empty($user['google_id'])) {
            $stmt = $conn->prepare("UPDATE users SET google_id = :google_id WHERE user_id = :user_id");
            $stmt->execute([
                ':google_id' => $google_id,
                ':user_id' => $user_id
            ]);
        }
    }

    // Commit the transaction
    $conn->commit();

    // Set session variables
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'user_id' => $user_id,
        'email' => $email,
        'username' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'profile_picture' => $profile_picture,
        'auth_method' => 'google',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'agent' => $_SERVER['HTTP_USER_AGENT'],
        'created' => time()
    ];

    // Respond with success
    echo json_encode([
        'success' => true,
        'email' => $email,
        'name' => $first_name . ' ' . $last_name,
        'sub' => $google_id
    ]);
    exit();

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>
