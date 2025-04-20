<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    $logs = [];

    // 1. Fetch logs from acRemote
    $query1 = "SELECT 
                r.*, 
                u.username, 
                u.profile_picture,
                u.email,
                u.role_id
              FROM rivan_iot.acRemote r
              JOIN users u ON r.user_id = u.user_id
              ORDER BY r.timestamp DESC";

    $stmt1 = $conn->prepare($query1);
    $stmt1->execute();

    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $updates = [];

        if (!empty($row['temp']) && !empty($row['temptime'])) {
            $updates['temp'] = [
                'message' => "Temp set to " . $row['temp'] . "°C",
                'time' => strtotime($row['temptime'])
            ];
        }
        if (!empty($row['mode']) && !empty($row['modetime'])) {
            $updates['mode'] = [
                'message' => "Mode: " . $row['mode'],
                'time' => strtotime($row['modetime'])
            ];
        }
        if (!empty($row['fan']) && !empty($row['fantime'])) {
            $updates['fan'] = [
                'message' => "Fan: " . $row['fan'],
                'time' => strtotime($row['fantime'])
            ];
        }
        if (!empty($row['swing']) && !empty($row['swingtime'])) {
            $updates['swing'] = [
                'message' => "Swing: " . $row['swing'],
                'time' => strtotime($row['swingtime'])
            ];
        }
        if (!empty($row['sleep']) && $row['sleep'] === 'on' && !empty($row['sleeptime'])) {
            $updates['sleep'] = [
                'message' => "Sleep mode enabled",
                'time' => strtotime($row['sleeptime'])
            ];
        }
        if (!empty($row['timer']) && !empty($row['timertime'])) {
            $updates['timer'] = [
                'message' => "Timer set to " . $row['timer'],
                'time' => strtotime($row['timertime'])
            ];
        }
        if (!empty($row['power']) && !empty($row['powertime'])) {
            $powerStatus = ($row['power'] === 'on') ? "Power ON" : "Power OFF";
            $updates['power'] = [
                'message' => $powerStatus,
                'time' => strtotime($row['powertime'])
            ];
        }

        $latestUpdate = null;
        foreach ($updates as $update) {
            if ($latestUpdate === null || $update['time'] > $latestUpdate['time']) {
                $latestUpdate = $update;
            }
        }

        $message = $row['username'] . " - " . ($latestUpdate['message'] ?? "Temp set to 16°C");

        $dt = new DateTime($row['timestamp']);
        $formattedTime = $dt->format('l, F jS, Y h:i:s A');

        if (empty(trim($row['profile_picture'])) || !filter_var($row['profile_picture'], FILTER_VALIDATE_URL)) {
            $row['profile_picture'] = "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&size=40";
        }

        $logs[] = [
            "user_id" => $row['user_id'],
            "Role" => $row['role_id'],
            "Username" => [
                "profile_picture" => $row['profile_picture'],
                "email" => $row['email'],
                "name" => $row['username']
            ],
            "action" => $message,
            "timestamp" => $formattedTime,
            "status" => "authorized"
        ];
    }

    // 2. Fetch logs from device_logs
    $query2 = "SELECT d.*, u.username, u.profile_picture, u.email, u.role_id
               FROM rivan_iot.device_logs d
               JOIN users u ON d.user_id = u.user_id
               ORDER BY d.last_updated DESC";

    $stmt2 = $conn->prepare($query2);
    $stmt2->execute();

    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $dt = new DateTime($row['last_updated']);
        $formattedTime = $dt->format('l, F jS, Y h:i:s A');

        if (empty(trim($row['profile_picture'])) || !filter_var($row['profile_picture'], FILTER_VALIDATE_URL)) {
            $row['profile_picture'] = "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&size=40";
        }

        $message = $row['username'] . " - " . $row['device_name'] . " turned " . $row['status'];

        $logs[] = [
            "user_id" => $row['user_id'],
            "Role" => $row['role_id'],
            "Username" => [
                "profile_picture" => $row['profile_picture'],
                "email" => $row['email'],
                "name" => $row['username']
            ],
            "action" => $message,
            "timestamp" => $formattedTime,
            "status" => "authorized"
        ];
    }

    // Sort combined logs by timestamp descending
    usort($logs, function ($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    echo json_encode($logs);

} catch (PDOException $e) {
    echo json_encode([
        "error" => true,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
