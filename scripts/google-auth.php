<?php
session_start(); // Start session at the top

header("Content-Type: application/json");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: credentialless"); 
header("Cross-Origin-Resource-Policy: cross-origin");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read POST data from Google sign-in
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';

if (!$token) {
    echo json_encode(["success" => false, "message" => "No token received."]);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/connection.php'; // This should create a PDO instance in $conn

// Verify the Google token using Google_Client.
const GOOGLE_CLIENT_ID = '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com';
$client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);

try {
    // Ensure the request method is POST.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Verify the token.
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception("Invalid Google token");
    }

    // Extract user data from the token.
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $first_name = $payload['given_name'] ?? '';
    $last_name = $payload['family_name'] ?? '';
    $profile_picture = $payload['picture'] ?? '';
    $username = $email; // For simplicity, use email as username.

    // Validate required fields.
    if (empty($email) || empty($google_id) || empty($profile_picture)) {
        throw new Exception("Invalid input data");
    }

    // Begin a transaction.
    $conn->beginTransaction();

    // Check if the user exists (by Google ID or email).
    $stmt = $conn->prepare("SELECT user_id, google_id FROM users WHERE google_id = :google_id OR email = :email");
    $stmt->execute([
        ':google_id' => $google_id,
        ':email' => $email
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Insert a new user.
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
            throw new Exception("Failed to get user ID.");
        }
    } else {
        $user_id = $user['user_id'];
        // Update the record if google_id is missing.
        if (empty($user['google_id'])) {
            $stmt = $conn->prepare("UPDATE users SET google_id = :google_id WHERE user_id = :user_id");
            $stmt->execute([
                ':google_id' => $google_id,
                ':user_id' => $user_id
            ]);
        }
    }

    // Commit the transaction.
    $conn->commit();

    // Regenerate session ID for security and set session variables.
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

    // Return a single JSON success response.
    echo json_encode([
        'success' => true,
        'email' => $email,
        'name' => trim($first_name . ' ' . $last_name),
        'sub' => $google_id
    ]);
    exit();

} catch (Exception $e) {
    // Roll back the transaction if needed.
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>
