<?php
// Enable verbose error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

try {
    // Debug: Verify input is received
    $rawInput = file_get_contents("php://input");
    if (empty($rawInput)) {
        throw new Exception("No POST data received");
    }

    // Debug: Check JSON decoding
    $postData = json_decode($rawInput);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON parse error: " . json_last_error_msg());
    }

    // Debug: Verify token exists
    if (!isset($postData->token)) {
        throw new Exception("Token field missing in JSON");
    }

    // Debug: Check vendor autoload
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception("Composer dependencies not installed (vendor/autoload.php missing)");
    }
    require 'vendor/autoload.php';

    // Debug: Google Client initialization
    $client = new Google\Client([
        'client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com'
    ]);

    // Debug: Token verification
    $payload = $client->verifyIdToken($postData->token);
    if (!$payload) {
        throw new Exception("Token validation failed (invalid or expired token)");
    }

    echo json_encode(["success" => true, "user" => $payload]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => $e->getMessage(),
        "debug_info" => [
            "php_version" => phpversion(),
            "extensions" => get_loaded_extensions(),
            "include_path" => get_include_path()
        ]
    ]);
}
?>