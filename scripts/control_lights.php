<?php
session_start();
header('Content-Type: application/json');

// 1) Authenticate session
// Use user_id from session, default to 0 if no session is found
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

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

// Check the topic to determine the correct user_id
$topic = isset($payload['topic']) ? $payload['topic'] : '';  // Assuming topic is in the payload
if ($topic === '/building/1/status') {
    // If the topic is '/building/1/status', it means the light was physically turned off
    // Therefore, set user_id to 0
    $userId = 0;
}

// Ensure that user_id is not NULL, use session value if NULL
if ($userId === null) {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;  // Default to session user_id or 0
}

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

    // 5) Update the user_id in the device_logs table for the most recent log entry
    // Convert the UTC time to PH time (UTC +8) using PHP DateTime class
    $utcDate = new DateTime('now', new DateTimeZone('UTC'));
    $utcDate->setTimezone(new DateTimeZone('Asia/Manila'));  // Convert to PH time

    // Now, we can use this PH time in the SQL query
    $ph_time = $utcDate->format('Y-m-d H:i:s'); // This will give you the time in PH format (YYYY-MM-DD HH:MM:SS)

    // Update device_logs table by joining it on the latest log entry
    $logSql = "
        UPDATE device_logs dl
           JOIN (
                SELECT MAX(last_updated) AS latest_time
                  FROM device_logs
                 WHERE device_name = :device_name
            ) AS latest_log
           ON dl.last_updated = latest_log.latest_time
          AND dl.device_name = :device_name
           SET dl.user_id = :user_id
    ";

    // Ensure user_id is not NULL
    if ($userId === null) {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;  // Default to session user_id or 0
    }

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([
        ':user_id' => $userId,  // Use the correct user_id (0 for physical turn-off, session value otherwise)
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
