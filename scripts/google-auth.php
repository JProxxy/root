<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: " . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header("Access-Control-Allow-Credentials: true");
header("Vary: Origin");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload
require_once '../app/config/connection.php';

$response = ['success' => false];
$errorCode = 400;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException("Invalid request method", 405);
    }

    if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
        throw new RuntimeException("Invalid content type", 415);
    }

    $rawInput = file_get_contents("php://input");
    if (empty($rawInput)) {
        throw new RuntimeException("Empty request body", 400);
    }
    $postData = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
    if (!isset($postData['token']) || !is_string($postData['token'])) {
        throw new RuntimeException("Missing or invalid token parameter", 400);
    }

    $client = new Google\Client([
        'client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com',
        'http_client' => [
            'verify' => '/etc/ssl/certs/ca-certificates.crt'
        ]
    ]);

    $payload = $client->verifyIdToken($postData['token']);
    if (!$payload || $payload['aud'] !== $client->getClientId()) {
        throw new RuntimeException("Invalid authentication token", 401);
    }

    // Extract user details from Google payload
    $googleId = $payload['sub'];
    $name = $payload['name'] ?? 'Google User';
    $email = $payload['email'];

    // Connect to MySQL
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new RuntimeException("Database connection failed", 500);
    }

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // User does not exist, insert new record
        $stmt = $conn->prepare("INSERT INTO users (google_id, name, email, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $googleId, $name, $email);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();

    // Start user session
    session_regenerate_id(true);
    $_SESSION = [
        'user_id' => $googleId,
        'username' => $name,
        'email' => $email,
        'auth_type' => 'google',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'created' => time()
    ];

    $response = [
        'success' => true,
        'redirect' => '/templates/dashboard.php',
        'session_id' => session_id()
    ];
    $errorCode = 200;

} catch (JsonException $e) {
    $response['error'] = "Invalid JSON format";
    $errorCode = 400;
} catch (RuntimeException $e) {
    $response['error'] = $e->getMessage();
    $errorCode = $e->getCode() ?: 400;
} catch (Throwable $e) {
    error_log("Critical Error: " . $e->getMessage());
    $response['error'] = "Internal server error";
    $errorCode = 500;
} finally {
    http_response_code($errorCode);
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
    exit;
}
