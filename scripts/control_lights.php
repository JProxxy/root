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

    // Get the current UTC time and convert it to PH time
    $utcDate = new DateTime('now', new DateTimeZone('UTC'));
    $utcDate->setTimezone(new DateTimeZone('Asia/Manila'));  // Convert to PH time
    $ph_time = $utcDate->format('Y-m-d H:i:s'); // This will give you the time in PH format (YYYY-MM-DD HH:MM:SS)

    // 5) Insert a new log entry into the device_logs table
    // Check if the user is logged in
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    // If no user is logged in, mark the action as "system" or "automated"
    if ($userId === NULL) {
        $userId = 0; // Special value to indicate automated action (like a physical switch)
    }

    $logSql = "
        INSERT INTO device_logs (device_name, user_id, status, last_updated)
        VALUES (:device_name, :user_id, :status, :last_updated)
    ";

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([
        ':device_name' => $deviceName,
        ':user_id' => $userId,  // Log the user ID (0 for automated actions)
        ':status' => $command,
        ':last_updated' => $ph_time
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
