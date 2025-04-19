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

    // 5) Get the latest log entry for the device to check user_id
    $logSql = "
        SELECT dl.user_id
          FROM device_logs dl
         WHERE dl.device_name = :device_name
      ORDER BY dl.last_updated DESC
         LIMIT 1
    ";

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([ ':device_name' => $deviceName ]);
    $latestLog = $logStmt->fetch(PDO::FETCH_ASSOC);

    // 6) Use session user_id or NULL if no user found
    $userId = isset($latestLog['user_id']) ? $latestLog['user_id'] : NULL;

    // If user_id is still NULL after fetching, no need to delay, just set it as NULL
    if ($userId === NULL) {
        $userId = NULL;
    }

    // 7) Update the device_logs table with user_id or NULL
    $updateLogSql = "
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

    $updateLogStmt = $conn->prepare($updateLogSql);
    $updateLogStmt->execute([
        ':user_id' => $userId,
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
