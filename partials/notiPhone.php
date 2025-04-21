<?php

// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../app/config/connection.php';

$response = [];

function checkNewLog($conn, $systemName, $tableName, $idField = 'id')
{
    // STEP 1: Get most recent ID
    $stmt = $conn->prepare("SELECT MAX($idField) AS most_recent_id FROM $tableName");
    $stmt->execute();
    $mostRecentId = $stmt->fetch(PDO::FETCH_ASSOC)['most_recent_id'];

    // STEP 2: Get last known
    $stmt = $conn->prepare("SELECT last_known_id FROM system_activity_log_tracking WHERE system_name = ?");
    $stmt->execute([$systemName]);
    $lastKnownId = $stmt->fetch(PDO::FETCH_ASSOC)['last_known_id'] ?? 0;

    // STEP 3: If new, fetch log
    if ($mostRecentId > $lastKnownId) {
        $stmt = $conn->prepare("SELECT * FROM $tableName WHERE $idField > ? ORDER BY $idField ASC LIMIT 1");
        $stmt->execute([$lastKnownId]);
        return [$stmt->fetch(PDO::FETCH_ASSOC), $mostRecentId];
    }

    return [null, $mostRecentId];
}

// GATE ACCESS LOGS
[$log, $latestId] = checkNewLog($conn, 'gateAccess_logs', 'gateAccess_logs');
if ($log) {
    if ($log['user_id'] != 0) {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
        $stmt->execute([$log['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $user && !empty($user['username']) ? $user['username'] : explode('@', $user['email'])[0];
    } else {
        $userName = "Unknown person";
    }

    $action = $log['user_id'] == 0
        ? "Unknown person tried to access the gate using an unknown RFID at {$log['timestamp']}."
        : "$userName " . ($log['result'] === 'open' ? 'opened the gate' : 'was denied access') . " using {$log['method']} at {$log['timestamp']}.";

    $response[] = [
        'new' => true,
        'id' => $log['id'],
        'system_name' => 'gateAccess_logs',
        'message' => $action,
        'timestamp' => $log['timestamp']
    ];

    $conn->prepare("UPDATE system_activity_log_tracking SET last_known_id = ?, updated_at = NOW() WHERE system_name = ?")
        ->execute([$latestId, 'gateAccess_logs']);
}
// DEVICE LOGS
[$log, $latestId] = checkNewLog($conn, 'device_logs', 'device_logs');
if ($log) {
    // Attempt to extract username directly from action message
    $userName = "Unknown person";  // Default to Unknown if not found

    // Check if username is embedded in the action, e.g., "jpenarubia.a0001"
    if (preg_match('/^([a-zA-Z0-9._-]+) turned/', $log['message'], $matches)) {
        $userName = $matches[1];  // Extract the username from the message
    }

    // Prepare the message
    $status = strtoupper($log['status']);
    $msg = "$userName turned $status {$log['device_name']} on Floor {$log['floor_id']} ({$log['where']}) at {$log['last_updated']}.";

    // Add the response to the array
    $response[] = [
        'new' => true,
        'id' => $log['id'],
        'system_name' => 'device_logs',
        'message' => $msg,
        'timestamp' => $log['last_updated']
    ];

    // Update the tracking table
    $conn->prepare("UPDATE system_activity_log_tracking SET last_known_id = ?, updated_at = NOW() WHERE system_name = ?")
        ->execute([$latestId, 'device_logs']);
}

// Ensure a valid response is returned
if (!empty($response)) {
    echo json_encode($response);
} else {
    // If no new logs, return a response indicating no new data
    echo json_encode(['new' => false]);
}

?>