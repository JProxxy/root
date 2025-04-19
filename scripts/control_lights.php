<?php
session_start();
header('Content-Type: application/json');

// 1) Authenticate session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// 2) Read and parse incoming JSON
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!isset($payload['body'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Decode nested body
$body = json_decode($payload['body'], true);

if (
    !isset($body['data']['deviceName']) ||
    !isset($body['data']['command'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing deviceName or command']);
    exit;
}

$deviceName = $body['data']['deviceName'];
$command    = strtoupper($body['data']['command']); // Normalize to ON/OFF

// 3) Validate command
if (!in_array($command, ['ON', 'OFF'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid command']);
    exit;
}

// 4) Update the Devices table
try {
    require_once '../app/config/connection.php'; // Assumes $conn is defined here

    // Update the Devices table
    $sql = "
        UPDATE Devices
           SET status = :status,
               last_updated = NOW()
         WHERE device_name = :deviceName
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $command,
        ':deviceName' => $deviceName
    ]);

    // 5) Find the latest entry in device_logs for this device and update the user_id
    $logSql = "
        UPDATE device_logs
           SET user_id = :user_id
         WHERE device_name = :device_name
           AND last_updated = (
               SELECT MAX(last_updated)
                 FROM device_logs
                WHERE device_name = :device_name
           )
    ";

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([
        ':user_id' => $_SESSION['user_id'], // Log the user ID from session
        ':device_name' => $deviceName
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
?>
