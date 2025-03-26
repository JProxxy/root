<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    // Query to get AC remote logs with username
    $query = "SELECT 
                r.*, 
                u.username 
              FROM rivan_iot.acRemote r
              JOIN users u ON r.user_id = u.user_id
              ORDER BY r.timestamp DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $logs = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Build an associative array of update messages with their corresponding time (as Unix timestamp)
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

        // For Sleep (only output if sleep is on)
        if (!empty($row['sleep']) && $row['sleep'] === 'on' && !empty($row['sleeptime'])) {
            $updates['sleep'] = [
                'message' => "Sleep mode enabled",
                'time' => strtotime($row['sleeptime'])
            ];
        }

        // For Timer (if applicable)
        if (!empty($row['timer']) && !empty($row['timertime'])) {
            $updates['timer'] = [
                'message' => "Timer set to " . $row['timer'],
                'time' => strtotime($row['timertime'])
            ];
        }

        // For Power (if applicable)
        if (!empty($row['power']) && !empty($row['powertime'])) {
            $powerStatus = ($row['power'] === 'on') ? "Power ON" : "Power OFF";
            $updates['power'] = [
                'message' => $powerStatus,
                'time' => strtotime($row['powertime'])
            ];
        }

        // Determine the most recent update based on the associated timestamps
        $latestUpdate = null;
        foreach ($updates as $update) {
            if ($latestUpdate === null || $update['time'] > $latestUpdate['time']) {
                $latestUpdate = $update;
            }
        }

        // Build the final message: Only output the most recent update if available.
        if ($latestUpdate !== null) {
            $message = $row['username'] . " - " . $latestUpdate['message'];

        $logs[] = [
            "time" => date("h:i A", strtotime($row['timestamp'])),
            "message" => $message,
            "device" => "AC Remote",
            "full_data" => $row  // Optional: include all raw data for debugging
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