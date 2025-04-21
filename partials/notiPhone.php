<?php
require_once '../app/config/connection.php';

header('Content-Type: application/json');

// Get the last known ID from the client
$lastKnownId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Fetch the latest log
$stmt = $conn->query("SELECT * FROM gateAccess_logs ORDER BY id DESC LIMIT 1");
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if ($log && $log['id'] > $lastKnownId) {
    // Optional: include user name if needed
    $userName = 'Unknown person';
    if ($log['user_id'] != 0) {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$log['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userName = $user['name'] . " (" . $log['user_id'] . ")";
        }
    }

    echo json_encode([
        'new' => true,
        'id' => $log['id'],
        'message' => $log['user_id'] == 0
            ? "Unknown person tried to access the gate using an unknown RFID at {$log['timestamp']}."
            : "$userName " . ($log['result'] == 'open' ? 'opened the gate' : 'was denied access') . " using {$log['method']} at {$log['timestamp']}.",
    ]);
} else {
    echo json_encode(['new' => false]);
}

