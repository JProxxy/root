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

    // 5) Get the latest log entry's time and user_id (if any)
    $logSql = "
        SELECT dl.user_id, dl.last_updated
          FROM device_logs dl
         WHERE dl.device_name = :device_name
      ORDER BY dl.last_updated DESC
         LIMIT 1
    ";
    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([ ':device_name' => $deviceName ]);
    $latestLog = $logStmt->fetch(PDO::FETCH_ASSOC);

    // If no user_id or it's NULL, wait 5 seconds and recheck
    $userId = isset($latestLog['user_id']) ? $latestLog['user_id'] : NULL;
    $lastUpdated = isset($latestLog['last_updated']) ? $latestLog['last_updated'] : null;

    // If user_id is NULL, wait for 5 seconds to check again
    if ($userId === NULL) {
        // Sleep for 5 seconds
        sleep(5);

        // Try fetching again after waiting
        $logStmt->execute([ ':device_name' => $deviceName ]);
        $latestLog = $logStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get the updated values
        $userId = isset($latestLog['user_id']) ? $latestLog['user_id'] : NULL;
        $lastUpdatedNew = isset($latestLog['last_updated']) ? $latestLog['last_updated'] : null;

        // If the last_updated timestamp has changed in those 5 seconds, recheck
        if ($lastUpdatedNew !== $lastUpdated) {
            $lastUpdated = $lastUpdatedNew;
            // Possibly log the timestamp change for debugging
        }
    }

    // If user_id is still NULL after the wait, set it to NULL in the log
    if ($userId === NULL) {
        $userId = NULL;
    }

    // Update the device_logs table with the user_id (or NULL if not found)
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
