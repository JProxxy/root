<?php

header("Content-Type: application/json");
require 'vendor/autoload.php';

use Google\Client;

try {
    $postData = json_decode(file_get_contents("php://input"));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input");
    }

    if (!isset($postData->token)) {
        throw new Exception("Token missing");
    }

    $client = new Client([
        'client_id' => "460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"
    ]);

    $payload = $client->verifyIdToken($postData->token);
    
    if ($payload) {
        echo json_encode(["success" => true, "user" => $payload]);
    } else {
        echo json_encode(["error" => "Invalid token"]);
    }
} catch (Exception $e) {
    // Log the error to a file
    error_log($e->getMessage(), 3, "error.log");
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>