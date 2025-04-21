<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    $logs = [];

    // A small helper to normalize profile pictures
    function normalizePic(string $pic, string $username): string {
        $pic = trim($pic);
        if ($pic === '' || !filter_var($pic, FILTER_VALIDATE_URL)) {
            // fallback to ui‑avatars with initials
            return "https://ui-avatars.com/api/?name=" . urlencode($username) . "&size=40";
        }
        return $pic;
    }

    // 1. Fetch logs from acRemote
    $query1 = "SELECT 
                r.*, 
                u.user_id,
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
        // ... your updates logic ...

        // Normalize the picture:
        $pic = normalizePic($row['profile_picture'], $row['username']);

        $logs[] = [
            "user_id"  => $row['user_id'],
            "Role"     => $row['role_id'],
            "Username" => [
                "profile_picture" => $pic,
                "email"           => $row['email'],
                "name"            => $row['username']
            ],
            "action"    => $row['username'] . " - " . ($latestUpdate['message'] ?? "Temp set to 16°C"),
            "timestamp" => (new DateTime($row['timestamp']))->format('l, F jS, Y h:i:s A'),
            "status"    => "authorized"
        ];
    }

    // 2. Fetch logs from device_logs
    $query2 = "SELECT 
                 d.*, 
                 u.user_id,
                 u.username, 
                 u.profile_picture, 
                 u.email, 
                 u.role_id
               FROM rivan_iot.device_logs d
               JOIN users u ON d.user_id = u.user_id
               ORDER BY d.last_updated DESC";
    $stmt2 = $conn->prepare($query2);
    $stmt2->execute();

    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        // Normalize the picture:
        $pic = normalizePic($row['profile_picture'], $row['username']);

        $logs[] = [
            "user_id"  => $row['user_id'],
            "Role"     => $row['role_id'],
            "Username" => [
                "profile_picture" => $pic,
                "email"           => $row['email'],
                "name"            => $row['username']
            ],
            "action"    => $row['username'] . " - " . $row['device_name'] . " turned " . $row['status'],
            "timestamp" => (new DateTime($row['last_updated']))->format('l, F jS, Y h:i:s A'),
            "status"    => "authorized"
        ];
    }

    // Sort combined logs by timestamp descending
    usort($logs, function ($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    echo json_encode($logs);

} catch (PDOException $e) {
    echo json_encode([
        "error"   => true,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
