<?php
// send_to_iot.php

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the received data
if (isset($data['lightId']) && isset($data['status'])) {
    // Prepare data for the Lambda API call
    $lightId = $data['lightId'];
    $status = $data['status'];

    // AWS API Gateway endpoint for Lambda function
    $apiUrl = 'https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice';

    // Prepare data to send to the Lambda function via the API Gateway
    $postData = json_encode([
        'lightId' => $lightId,
        'status' => $status,
    ]);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData),
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        // Handle cURL errors
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        // Return Lambda function response
        echo $response;
    }

    // Close cURL session
    curl_close($ch);
} else {
    echo json_encode(['error' => 'Invalid input data']);
}
?>
