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
    // STEP 1: Get the most recent log ID from the gateAccess_logs table
    $stmt = $conn->prepare("SELECT MAX(id) AS most_recent_id FROM gateAccess_logs");
    $stmt->execute();
    $mostRecentLog = $stmt->fetch(PDO::FETCH_ASSOC);
    $mostRecentId = $mostRecentLog['most_recent_id'];

    // STEP 2: Fetch the last known ID for this system from the tracking table
    $stmt = $conn->prepare("SELECT last_known_id FROM system_activity_log_tracking WHERE system_name = ?");
    $stmt->execute([$systemName]);
    $trackRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $lastKnownId = $trackRow ? intval($trackRow['last_known_id']) : 0;

    // STEP 3: If the most recent ID is greater than the last known ID, proceed
    if ($mostRecentId > $lastKnownId) {
        // STEP 4: Fetch the new log entry since the last known ID
        $stmt = $conn->prepare("SELECT * FROM gateAccess_logs WHERE id > ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$lastKnownId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($log) {
            // STEP 4a: Determine userName
            if ($log['user_id'] != 0) {
                // Fetch the user info if a valid user_id exists
                $u = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
                $u->execute([$log['user_id']]);
                $user = $u->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // If the username is available, use it; otherwise, use the local part of the email
                    $userName = !empty($user['username'])
                        ? $user['username']
                        : explode('@', $user['email'])[0];
                } else {
                    // If no user data is found, you can also default to "Unknown person"
                    $userName = 'Unknown person';
                }
            } else {
                // If no valid user_id (user_id = 0), set the name to "Unknown person"
                $userName = 'Unknown person';
            }


            // STEP 4b: Build the action message
            if ($log['user_id'] == 0) {
                $message = "Unknown person tried to access the gate using an unknown RFID at {$log['timestamp']}.";
            } else {
                $action = $log['result'] === 'open'
                    ? 'opened the gate'
                    : 'was denied access';
                $message = "$userName $action using {$log['method']} at {$log['timestamp']}.";
            }

            // STEP 5: Respond with notification data to be handled by JavaScript (BGMain.php)
            echo json_encode([
                'new' => true,
                'id' => $log['id'],
                'system_name' => $systemName,
                'message' => $message,
                'timestamp' => $log['timestamp']
            ]);

            // STEP 6: Update the tracking table
            $upd = $conn->prepare("
                UPDATE system_activity_log_tracking 
                SET last_known_id = ?, updated_at = NOW() 
                WHERE system_name = ?
            ");
            if ($upd->execute([$mostRecentId, $systemName])) {
                file_put_contents('php://stderr', "Updated last_known_id for $systemName to $mostRecentId\n");
            } else {
                file_put_contents('php://stderr', "Failed to update last_known_id for $systemName\n");
            }
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