<?php
session_start(); // Ensure session starts before anything else

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: require-corp");

// Error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/google-auth-errors.log');

require_once __DIR__ . '/../vendor/autoload.php'; // Ensure correct path

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['token'])) {
        throw new Exception('Invalid request structure');
    }

    $client = new Google_Client(['client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com']);
    $payload = $client->verifyIdToken($data['token']);

    if (!$payload) {
        throw new Exception('Invalid token verification');
    }

    // ✅ **Set Session Variables**
    $_SESSION['user_email'] = $payload['email'];
    $_SESSION['user_name'] = $payload['name'] ?? '';
    $_SESSION['user_id'] = $payload['sub'];

    // ✅ **Check if session is properly set**
    if (!isset($_SESSION['user_email'])) {
        throw new Exception("Session not set properly!");
    }

    echo json_encode([
        'success' => true,
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'sub' => $_SESSION['user_id']
    ]);
    exit();

} catch (Exception $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>
