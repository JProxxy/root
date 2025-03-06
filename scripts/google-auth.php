<?php
session_start();

header("Content-Type: application/json");

// Allow requests from any origin (change to specific domain if needed)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Fix COOP issue by setting the correct policy
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: require-corp");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}



header("Access-Control-Allow-Origin: " . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));

header("Vary: Origin");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

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

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new RuntimeException("System configuration error", 500);
    }
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once '../app/config/connection.php'; // PDO Connection

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

    $userId = $payload['sub'];
    $username = $payload['name'] ?? 'Google User';
    $email = $payload['email'];

    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :google_id");
    $stmt->execute(['google_id' => $userId]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (google_id, name, email) VALUES (:google_id, :name, :email)");
        $stmt->execute([
            'google_id' => $userId,
            'name' => $username,
            'email' => $email
        ]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $existingUser['id'];
    }

    // Store user in session
    session_regenerate_id(true);
    $_SESSION = [
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'auth_type' => 'google',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'created' => time()
    ];

    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path' => $cookieParams['path'],
        'domain' => parse_url($_SERVER['HTTP_ORIGIN'] ?? '', PHP_URL_HOST),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

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
