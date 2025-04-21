<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../app/config/connection.php';

$systemName = 'customizeWater';

try {
    // 1. Fetch the current customizeTime from the only row (assume ID = 1)
    $stmt = $conn->query("SELECT * FROM customizeWater WHERE id = 1 LIMIT 1");
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        echo json_encode(['new' => false]);
        exit;
    }

    // 2. Fetch the last known customizeTime from tracking
    $stmt = $conn->prepare("SELECT last_known_id FROM system_activity_log_tracking WHERE system_name = ?");
    $stmt->execute([$systemName]);
    $lastKnownTime = $stmt->fetchColumn();

    // 3. Compare customizeTime
    if ($log['customizeTime'] !== $lastKnownTime) {
        // 4. Get user name from user_id
        $user_id = $log['user_id'];
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $userName = $user && !empty($user['username'])
            ? $user['username']
            : ($user ? explode('@', $user['email'])[0] : 'Unknown user');

        // 5. Build message
        $message = sprintf(
            "%s set the minWater at %.2f and maxWater at %.2f at %s.",
            $userName,
            $log['minWater'],
            $log['maxWater'],
            $log['customizeTime']
        );

        // 6. Respond with log
        echo json_encode([
            'new' => true,
            'id' => $log['id'],
            'system_name' => $systemName,
            'message' => $message,
            'timestamp' => $log['customizeTime']
        ]);

        // 7. Update tracking table (store customizeTime instead of ID)
        $stmt = $conn->prepare("
            INSERT INTO system_activity_log_tracking (system_name, last_known_id, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_known_id = VALUES(last_known_id), updated_at = NOW()
        ");
        $stmt->execute([$systemName, $log['customizeTime']]);
    } else {
        echo json_encode(['new' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['new' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
