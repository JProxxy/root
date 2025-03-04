<?php
require 'vendor/autoload.php'; // This loads the Google API Client

use Google\Client;

$postData = json_decode(file_get_contents("php://input"));

if (!isset($postData->token)) {
    echo json_encode(["error" => "Token missing"]);
    exit;
}

$client = new Client(['client_id' => "
460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"]);

$payload = $client->verifyIdToken($postData->token);

if ($payload) {
    echo json_encode(["success" => true, "user" => $payload]);
} else {
    echo json_encode(["error" => "Invalid token"]);
}
?>
