<?php
require_once 'vendor/autoload.php';  // Adjust path to your Composer autoload

$client = new Google_Client(['client_id' => '460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com']);
$client->setAuthConfig('../scripts/client_secret.json'); 
$client->addScope("email");

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'];

try {
    // Verify the token
    $payload = $client->verifyIdToken($token);

    if ($payload) {
        // User is authenticated, you can now create a session or handle the user
        // Extract user info if needed
        $email = $payload['email'];
        $name = $payload['name'];
        $sub = $payload['sub'];

        // Here you could insert the user into the database or do other operations

        echo json_encode(['success' => true, 'message' => 'User authenticated successfully']);
    } else {
        // Invalid token
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()]);
}
?>
