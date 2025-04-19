<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

// 2) Include your shared connection (must set up $conn as a PDO)
include '../app/config/connection.php';  // <-- this file should `return` or define $conn

// Optional: verify $conn is a valid PDO
if (!($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection not available']);
    exit;
}

try {
    // 3) Lookup RFID for this user (or NULL)
    $stmt = $conn->prepare("SELECT rfid FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $rfid = $stmt->fetchColumn() ?: null;

    // 4) Insert into gateAccess_logs
    $insert = $conn->prepare(
        "INSERT INTO gateAccess_logs
           (rfid, user_id, method, result, timestamp)
         VALUES
           (?,      ?,       'website', 'open',   NOW())"
    );
    $insert->execute([$rfid, $userId]);

    // 5) Return success
    echo json_encode([
        'success'    => true,
        'insertedId' => $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
