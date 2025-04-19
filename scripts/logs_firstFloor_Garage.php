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
    LEFT JOIN users u ON dl.user_id = u.user_id
    WHERE dl.floor_id = 1  
    ORDER BY dl.last_updated ASC
    ";

    $deviceStmt = $conn->prepare($deviceLogsQuery);
    $deviceStmt->execute();

    // Device name mapping
    $deviceNames = [
        'FFLightOne' => 'Front Gate Lights',
        'FFLightTwo' => 'Front Garage Lights',
        'FFLightThree' => 'Rear Garage Lights'
    ];

    // Process the device logs
    while ($deviceRow = $deviceStmt->fetch(PDO::FETCH_ASSOC)) {
        // Check if username is null (i.e., if there's no user associated with the log)
        $username = $deviceRow['username'] ? $deviceRow['username'] : "Unknown User";

        // Map device name to a friendly name
        $deviceName = isset($deviceNames[$deviceRow['device_name']]) ? $deviceNames[$deviceRow['device_name']] : ucfirst($deviceRow['device_name']);

        // Build message for device logs
        if ($deviceRow['username']) {
            // If there is a user associated, display the username
            $message = $username . " - " . $deviceName . " is now " . $deviceRow['status'];
        } else {
            // If no user, indicate it was a physical switch
            $message = "Physical switch in " . $deviceName . " is now " . $deviceRow['status'];
        }

        // Add a log for device status change
        $logs[] = [
            "time" => date("h:i A", strtotime($deviceRow['last_updated'])),
            "message" => $message,
            "device" => $deviceRow['device_name'],
            "full_data" => $deviceRow  // Optional: include all raw data for debugging
        ];
    }

    // Retrieve multiple gate access logs
    $gateLogsQuery = "
SELECT g.*, u.email 
FROM gateAccess_logs g
JOIN users u ON g.user_id = u.user_id
ORDER BY g.timestamp DESC
LIMIT 5
";

    $gateStmt = $conn->prepare($gateLogsQuery);
    $gateStmt->execute();

    while ($gateRow = $gateStmt->fetch(PDO::FETCH_ASSOC)) {
        $emailUsername = explode('@', $gateRow['email'])[0];
        $message = "" . $emailUsername . " has opened the gate";

        // Convert to Philippine Time
        $datetime = new DateTime($gateRow['timestamp'], new DateTimeZone('UTC'));
        $datetime->setTimezone(new DateTimeZone('Asia/Manila'));

        $logs[] = [
            "time" => $datetime->format("h:i A"),
            "message" => $message,
            "device" => "Access Gate",
            "full_data" => $gateRow
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