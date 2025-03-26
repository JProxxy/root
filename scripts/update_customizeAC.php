<?php
session_start();
include '../app/config/connection.php';
header('Content-Type: text/plain');

// Ensure a user is logged in.
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the JSON payload from the request body.
$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['minTemp']) && isset($input['maxTemp'])) {
    $minTemp = $input['minTemp'];
    $maxTemp = $input['maxTemp'];
    
    try {
        // Check if a row already exists in the table.
        $stmt = $conn->prepare("SELECT COUNT(*) FROM customizeAC");
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        if ($rowCount > 0) {
            // Row exists, perform an UPDATE.
            $stmt = $conn->prepare("UPDATE customizeAC SET minTemp = :minTemp, maxTemp = :maxTemp, customizeTime = NOW(), user_id = :user_id");
            $stmt->execute([
                ':minTemp' => $minTemp,
                ':maxTemp' => $maxTemp,
                ':user_id'  => $user_id
            ]);
            echo "Update successful";
        } else {
            // No row exists, perform an INSERT.
            $stmt = $conn->prepare("INSERT INTO customizeAC (minTemp, maxTemp, customizeTime, user_id) VALUES (:minTemp, :maxTemp, NOW(), :user_id)");
            $stmt->execute([
                ':minTemp' => $minTemp,
                ':maxTemp' => $maxTemp,
                ':user_id'  => $user_id
            ]);
            echo "Insert successful";
        }
    } catch (PDOException $e) {
        echo "Operation failed: " . $e->getMessage();
    }
} else {
    echo "Invalid input";
}
?>
