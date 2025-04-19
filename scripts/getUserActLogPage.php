<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    // Query to get AC remote logs along with user details
    $query = "SELECT 
                r.*, 
                u.username, 
                u.profile_picture,
                u.email,
                u.role_id
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
        } else {
            $message = $row['username'] . " - Temp set to 16°C";
        }

        // Format the main timestamp for display (e.g., "03:45 PM")
        $dt = new DateTime($row['timestamp']);
        $formattedTime = $dt->format('l, F jS, Y h:i:s A');

        // Use a robust check to ensure the profile picture is valid:
        // If it's empty or not a valid URL, assign a dummy image.
        if (empty(trim($row['profile_picture'])) || !filter_var($row['profile_picture'], FILTER_VALIDATE_URL)) {
            $row['profile_picture'] = "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&size=40";
        }

        // Map the AC remote log data to your expected user activity log structure.
        $logs[] = [
            "Role" => $row['role_id'],  // Include Role information from users table
            "Username" => [
                "profile_picture" => $row['profile_picture'],
                "email" => $row['email']
            ],
            "action" => $message,
            "timestamp" => $formattedTime,
            "status" => "authorized"  // Set a default status; adjust if needed
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
