<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

try {
    // Query to get the latest 4 AC remote logs along with user details
    $query = "SELECT 
                r.timestamp, 
                u.username, 
                u.profile_picture 
              FROM rivan_iot.acRemote r
              JOIN users u ON r.user_id = u.user_id
              ORDER BY r.timestamp DESC
              LIMIT 4";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $logs = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format timestamp to show only time (e.g., "03:50 AM")
        $formattedTime = date('h:i A', strtotime($row['timestamp']));

        // Use a default profile picture if none is set or invalid
        if (empty(trim($row['profile_picture'])) || !filter_var($row['profile_picture'], FILTER_VALIDATE_URL)) {
            $row['profile_picture'] = "https://ui-avatars.com/api/?name=" . urlencode($row['username']) . "&size=40";
        }

        // Store each log entry
        $logs[] = [
            "username"        => $row['username'],
            "profile_picture" => $row['profile_picture'],
            "timestamp"       => $formattedTime
        ];
    }

    echo json_encode($logs);

} catch (PDOException $e) {
    echo json_encode([
        "error"   => true,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
