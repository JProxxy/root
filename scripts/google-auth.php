<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com'); // Your client ID
$client->addScope("email");

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['token'])) {
        throw new Exception('Invalid request format');
    }

    $client = new Google_Client();
    $client->setClientId('460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com');
    
    $payload = $client->verifyIdToken($data['token']);
    if (!$payload) {
        throw new Exception('Invalid ID token');
    }

    // Add your user handling logic here
    error_log("Google authentication successful for: " . $payload['email']);
    
    echo json_encode([
        'success' => true,
        'email' => $payload['email'],
        'name' => $payload['name'] ?? '',
        'google_id' => $payload['sub']
    ]);
} catch (Exception $e) {
    error_log('Google Auth Error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>