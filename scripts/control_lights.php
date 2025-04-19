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
               last_updated = NOW(3)  -- Use NOW() with precision up to milliseconds
         WHERE device_name = :deviceName
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $command,
        ':deviceName' => $deviceName
    ]);

    // 5) Get the latest log entry that does not have NULL user_id
    $logSql = "
        SELECT dl.user_id, dl.last_updated
          FROM device_logs dl
         WHERE dl.device_name = :device_name
           AND dl.user_id IS NOT NULL
      ORDER BY dl.last_updated DESC
         LIMIT 1
    ";

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([ ':device_name' => $deviceName ]);
    $latestLog = $logStmt->fetch(PDO::FETCH_ASSOC);

    if ($latestLog) {
        // We found the latest valid log entry
        $latestTimestamp = $latestLog['last_updated'];

        // Now update the device_logs with the user_id for the most recent valid log entry
        $logUpdateSql = "
            UPDATE device_logs dl
               SET dl.user_id = :user_id
             WHERE dl.device_name = :device_name
               AND dl.last_updated = :last_updated
        ";

        $logUpdateStmt = $conn->prepare($logUpdateSql);
        $logUpdateStmt->execute([
            ':user_id' => $_SESSION['user_id'],  // Log the user ID from session
            ':device_name' => $deviceName,
            ':last_updated' => $latestTimestamp   // Update the log entry that matches the precise timestamp
        ]);
    } else {
        // If no valid log entry was found, handle this case
        echo json_encode(['error' => 'No valid log entry found']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
?>
