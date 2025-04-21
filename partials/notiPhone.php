<?php
// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once '../app/config/connection.php';

$systemName = 'gateAccess_logs';

try {
    // STEP 1: Fetch the last known ID for this system from tracking table
    $stmt = $conn->prepare("SELECT last_known_id FROM system_activity_log_tracking WHERE system_name = ?");
    $stmt->execute([$systemName]);
    $trackRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $lastKnownId = $trackRow ? intval($trackRow['last_known_id']) : 0;

    // STEP 2: Check for new log
    $stmt = $conn->prepare("SELECT * FROM gateAccess_logs WHERE id > ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$lastKnownId]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($log) {
        $userName = 'Unknown person';

        if ($log['user_id'] != 0) {
            $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
            $stmt->execute([$log['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $cleanEmail = explode('@', $user['email'])[0];
                $userName = (!empty($user['username']) ? $user['username'] : $cleanEmail) . " ({$log['user_id']})";
            }
        }

        $message = $log['user_id'] == 0
            ? "Unknown person tried to access the gate using an unknown RFID at {$log['timestamp']}."
            : "$userName " . ($log['result'] == 'open' ? 'opened the gate' : 'was denied access') . " using {$log['method']} at {$log['timestamp']}.";

        // STEP 3: Prevent duplicates using sent_notifications
        $stmt = $conn->prepare("SELECT COUNT(*) FROM sent_notifications WHERE log_id = ? AND system_name = ?");
        $stmt->execute([$log['id'], $systemName]);
        $alreadySent = $stmt->fetchColumn() > 0;

        if (!$alreadySent) {
            // STEP 4: Update the tracking table
            $stmt = $conn->prepare("
                INSERT INTO system_activity_log_tracking (system_name, last_known_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE last_known_id = VALUES(last_known_id)
            ");
            $stmt->execute([$systemName, $log['id']]);

            // STEP 5: Store notification log
            $stmt = $conn->prepare("
                INSERT INTO sent_notifications (log_id, system_name, message)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$log['id'], $systemName, $message]);

            // STEP 6: Respond with notification
            echo json_encode([
                'new' => true,
                'id' => $log['id'],
                'message' => $message,
                'timestamp' => $log['timestamp']
            ]);
        } else {
            echo json_encode(['new' => false]);
        }
    } else {
        echo json_encode(['new' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['new' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
