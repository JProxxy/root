<?php
// Headers at the very top
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/google-auth-errors.log');

require_once __DIR__ . '/../vendor/autoload.php'; // Verify correct path

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

    // Successful authentication
    echo json_encode([
        'success' => true,
        'email' => $payload['email'],
        'name' => $payload['name'] ?? '',
        'sub' => $payload['sub']
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