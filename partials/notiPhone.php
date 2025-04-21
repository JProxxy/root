<?php
// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once '../app/config/connection.php';

// Get the last known ID from the client
$lastKnownId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

try {
    // Fetch the latest log
    $stmt = $conn->query("SELECT * FROM gateAccess_logs ORDER BY id DESC LIMIT 1");
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($log && $log['id'] > $lastKnownId) {
        // Default name
        $userName = 'Unknown person';

        // Lookup user if user_id is not 0
        if ($log['user_id'] != 0) {
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$log['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userName = $user['name'] . " (" . $log['user_id'] . ")";
            }
        }

        // Prepare the message
        $message = $log['user_id'] == 0
            ? "Unknown person tried to access the gate using an unknown RFID at {$log['timestamp']}."
            : "$userName " . ($log['result'] == 'open' ? 'opened the gate' : 'was denied access') . " using {$log['method']} at {$log['timestamp']}.";

        echo json_encode([
            'new' => true,
            'id' => $log['id'],
            'message' => $message,
        ]);
    } else {
        echo json_encode(['new' => false]);
    }
} catch (Exception $e) {
    echo json_encode([
        'new' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
