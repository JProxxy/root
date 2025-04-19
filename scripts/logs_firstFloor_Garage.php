<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    // First, we query for the AC remote logs with username
    $query = "SELECT 
                r.*, 
                u.username 
              FROM rivan_iot.acRemote r
              JOIN users u ON r.user_id = u.user_id
              ORDER BY r.timestamp DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $logs = [];

    // Process the AC remote logs first
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $updates = [];

        // For Temperature
        if (!empty($row['temp']) && !empty($row['temptime'])) {
            $updates['temp'] = [
                'message' => "Temp set to " . $row['temp'] . "°C",
                'time' => strtotime($row['temptime'])
            ];
        }

        // For Mode
        if (!empty($row['mode']) && !empty($row['modetime'])) {
            $updates['mode'] = [
                'message' => "Mode: " . $row['mode'],
                'time' => strtotime($row['modetime'])
            ];
        }

        // For Fan
        if (!empty($row['fan']) && !empty($row['fantime'])) {
            $updates['fan'] = [
                'message' => "Fan: " . $row['fan'],
                'time' => strtotime($row['fantime'])
            ];
        }

        // For Swing
        if (!empty($row['swing']) && !empty($row['swingtime'])) {
            $updates['swing'] = [
                'message' => "Swing: " . $row['swing'],
                'time' => strtotime($row['swingtime'])
            ];
        }

        // For Sleep
        if (!empty($row['sleep']) && $row['sleep'] === 'on' && !empty($row['sleeptime'])) {
            $updates['sleep'] = [
                'message' => "Sleep mode enabled",
                'time' => strtotime($row['sleeptime'])
            ];
        }

        // For Timer
        if (!empty($row['timer']) && !empty($row['timertime'])) {
            $updates['timer'] = [
                'message' => "Timer set to " . $row['timer'],
                'time' => strtotime($row['timertime'])
            ];
        }

        // For Power
        if (!empty($row['power']) && !empty($row['powertime'])) {
            $powerStatus = ($row['power'] === 'on') ? "Power ON" : "Power OFF";
            $updates['power'] = [
                'message' => $powerStatus,
                'time' => strtotime($row['powertime'])
            ];
        }

        // Determine the most recent update
        $latestUpdate = null;
        foreach ($updates as $update) {
            if ($latestUpdate === null || $update['time'] > $latestUpdate['time']) {
                $latestUpdate = $update;
            }
        }

        // Build the final message
        if ($latestUpdate !== null) {
            $message = $row['username'] . " - " . $latestUpdate['message'];
        }

        $logs[] = [
            "time" => date("h:i A", strtotime($row['timestamp'])),
            "message" => $message,
            "device" => "AC Remote",
            "full_data" => $row  // Optional: include all raw data for debugging
        ];
    }

    // Now, let's retrieve logs from the device_logs table
    $deviceLogsQuery = "
        SELECT dl.*, u.username
        FROM device_logs dl
        JOIN users u ON dl.user_id = u.user_id
        WHERE dl.floor_id = 1  -- You can modify this condition as needed
        ORDER BY dl.last_updated DESC
    ";

    $deviceStmt = $conn->prepare($deviceLogsQuery);
    $deviceStmt->execute();

    // Process the device logs
    while ($deviceRow = $deviceStmt->fetch(PDO::FETCH_ASSOC)) {
        // Add a log for device status change
        $logs[] = [
            "time" => date("h:i A", strtotime($deviceRow['last_updated'])),
            "message" => $deviceRow['username'] . " - " . ucfirst($deviceRow['device_name']) . " is now " . $deviceRow['status'],
            "device" => $deviceRow['device_name'],
            "full_data" => $deviceRow  // Optional: include all raw data for debugging
        ];
    }

    echo json_encode($logs);

} catch (PDOException $e) {
    echo json_encode([
        "error" => true,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
