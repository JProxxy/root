<?php
// 1) Authenticate session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Default to 0 if session user_id is not found

// 2) Read and parse incoming JSON (as before)
// Example: Assume incoming data is parsed into variables like $topic, $deviceName, $command

// 3) Handle topic check
if ($topic === '/building/1/status') {
    // Physical turn-off detection, set user_id to 0
    error_log('Physical turn-off detected, setting user_id to 0');
    $userId = 0;
}

// Log user_id before device status validation
error_log('User_id before device status update: ' . var_export($userId, true));

// 4) Device validation and database updates
try {
    require_once '../app/config/connection.php'; // Assumes $conn is defined here

    // Update the Devices table (same logic as before)
    $sql = "
        UPDATE Devices
           SET status = :status,
               last_updated = CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')  -- Set last_updated to PHT
         WHERE device_name = :deviceName
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([ 
        ':status' => $command,
        ':deviceName' => $deviceName
    ]);

    // Log the success of the device status update
    error_log('Device status update succeeded for ' . $deviceName . ' to ' . $command);

    // Ensure user_id is valid before updating logs
    if ($userId === null) {
        // If user_id is null (after all checks), set it to 0
        error_log('User_id is null after all checks, setting it to 0');
        $userId = 0;
    }

    // Update the device_logs table for the most recent entry
    $logSql = "
        UPDATE device_logs dl
           JOIN (
                SELECT MAX(last_updated) AS latest_time
                  FROM device_logs
                 WHERE device_name = :device_name
            ) AS latest_log
           ON dl.last_updated = latest_log.latest_time
          AND dl.device_name = :device_name
           SET dl.user_id = :user_id,
               dl.last_updated = CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')  -- Set last_updated to PHT
    ";

    // Log the final user_id used for the device_logs update
    error_log('Final user_id before device_logs update: ' . var_export($userId, true));

    $logStmt = $conn->prepare($logSql);
    $logStmt->execute([ 
        ':user_id' => $userId,  // Use the correct user_id (0 for physical turn-off, session value otherwise)
        ':device_name' => $deviceName
    ]);

    // Success log
    error_log('Device log update for ' . $deviceName . ' succeeded.');

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([ 
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
?>
