<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] ?? '*');
header("Access-Control-Allow-Credentials: true");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    // Validate input
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $rawInput = file_get_contents("php://input");
    if (empty($rawInput)) {
        throw new Exception("No authentication data received");
    }

    $postData = json_decode($rawInput);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid request format: " . json_last_error_msg());
    }

    if (!isset($postData->token) || !is_string($postData->token)) {
        throw new Exception("Invalid token format");
    }

    // Verify dependencies
    if (!file_exists(__DIR__.'/vendor/autoload.php')) {
        throw new Exception("System configuration error");
    }
    require_once 'vendor/autoload.php';

    // Configure Google Client
    $client = new Google\Client([
        'client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com',
        'http_client' => [
            'verify' => '/etc/ssl/certs/ca-certificates.crt' // SSL verification
        ]
    ]);

    // Validate token
    $payload = $client->verifyIdToken($postData->token);
    if (!$payload || $payload['aud'] !== $client->getClientId()) {
        throw new Exception("Invalid authentication token");
    }

    // Session configuration
    session_regenerate_id(true);
    $_SESSION = [
        'user_id'    => $payload['sub'],
        'username'   => $payload['name'] ?? 'Google User',
        'email'      => $payload['email'],
        'auth_type'  => 'google',
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];

    // Secure cookie parameters
    session_set_cookie_params([
        'lifetime'  => 3600 * 24 * 7, // 1 week
        'path'      => '/',
        'domain'    => parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST),
        'secure'    => true,
        'httponly'  => true,
        'samesite'  => 'Lax'
    ]);

    // Response
    echo json_encode([
        'success' => true,
        'redirect' => '/templates/dashboard.php',
        'session_id' => session_id()
    ]);

} catch (Throwable $e) {
    error_log('Authentication Error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'error' => 'Authentication Failed',
        'message' => $e->getMessage(),
        'error_code' => 'AUTH_ERR_001'
    ]);
    exit;
}