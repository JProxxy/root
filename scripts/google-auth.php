<?php

header("Content-Type: application/json"); // Ensure the response is JSON
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Resource-Policy: cross-origin");

require 'vendor/autoload.php'; // This loads the Google API Client

use Google\Client;

try {
    // Read and decode the incoming JSON data
    $postData = json_decode(file_get_contents("php://input"));

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input");
    }

    if (!isset($postData->token)) {
        throw new Exception("Token missing");
    }

    // Initialize the Google Client
    $client = new Client(['client_id' => "460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"]);

    // Verify the ID token
    $payload = $client->verifyIdToken($postData->token);

    if ($payload) {
        // Token is valid
        echo json_encode(["success" => true, "user" => $payload]);
    } else {
        // Token is invalid
        echo json_encode(["error" => "Invalid token"]);
    }
} catch (Exception $e) {
    // Handle exceptions and return a meaningful error message
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => $e->getMessage()]);
}
?>