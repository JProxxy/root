<?php
// update_rfid.php

require_once '../app/config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id'];
    $rfid = isset($data['rfid']) ? $data['rfid'] : null;

    try {
        // If RFID is provided, check if it's already used by another user
        if ($rfid !== null) {
            $checkSql = "SELECT user_id FROM users WHERE rfid = :rfid AND user_id != :user_id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
            $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'RFID is USED Already']);
                exit;
            }

            // If not used, update RFID
            $sql = "UPDATE users SET rfid = :rfid WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
        } else {
            // RFID is null – delete it
            $sql = "UPDATE users SET rfid = NULL WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
        }

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to update RFID']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
?>
