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

if (isset($input['minWater']) && isset($input['maxWater'])) {
    $minWater = $input['minWater'];
    $maxWater = $input['maxWater'];
    
    try {
        // Check if a row already exists in the table.
        $stmt = $conn->prepare("SELECT COUNT(*) FROM customizeWater");
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        if ($rowCount > 0) {
            // Row exists, perform an UPDATE.
            $stmt = $conn->prepare("UPDATE customizeWater SET minWater = :minWater, maxWater = :maxWater, customizeTime = NOW(), user_id = :user_id");
            $stmt->execute([
                ':minWater' => $minWater,
                ':maxWater' => $maxWater,
                ':user_id'  => $user_id
            ]);
            echo "Update successful";
        } else {
            // No row exists, perform an INSERT.
            $stmt = $conn->prepare("INSERT INTO customizeWater (minWater, maxWater, customizeTime, user_id) VALUES (:minWater, :maxWater, NOW(), :user_id)");
            $stmt->execute([
                ':minWater' => $minWater,
                ':maxWater' => $maxWater,
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
