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
    // 1) Lookup friendly device name
    $deviceNames = [
        'FFLightOne'   => 'Front Gate Lights',
        'FFLightTwo'   => 'Front Garage Lights',
        'FFLightThree' => 'Rear Garage Lights',
    ];
    $friendlyName = $deviceNames[$log['device_name']] ?? $log['device_name'];

    // 2) Lookup source from 'where'
    $sourceMap = [
        '/building/1/lights' => 'website',
        '/building/1/status' => 'switch',
    ];
    $source = $sourceMap[$log['where']] ?? $log['where'];

    // 3) Determine username (falling back to email local part)
    if (!empty($log['user_id']) && $log['user_id'] != 0) {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
        $stmt->execute([$log['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $user && !empty($user['username'])
            ? $user['username']
            : explode('@', $user['email'])[0];
    } else {
        $userName = 'superadmin'; // or whatever default you prefer
    }

    // 4) Build your final message
    $status = strtoupper($log['status']);
    $msg = sprintf(
        "%s turned %s %s on Floor %d using %s at %s.",
        $userName,
        $status,
        $friendlyName,
        $log['floor_id'],
        $source,
        $log['last_updated']
    );

    // 5) Push into response
    $response[] = [
        'new'         => true,
        'id'          => $log['id'],
        'system_name' => 'device_logs',
        'message'     => $msg,
        'timestamp'   => $log['last_updated']
    ];

    // 6) Update tracking
    $conn->prepare("
        UPDATE system_activity_log_tracking 
           SET last_known_id = ?, updated_at = NOW() 
         WHERE system_name = ?
    ")->execute([$latestId, 'device_logs']);
}

// ... rest of your echo json_encode($response) logic

?>