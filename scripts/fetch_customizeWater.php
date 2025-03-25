<?php
include '../app/config/connection.php';
header('Content-Type: application/json');

try {
    // Fetch the single row of global settings.
    $stmt = $conn->prepare("SELECT minWater, maxWater FROM customizeWater LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo json_encode($row);
    } else {
        // Return default values if no record is found.
        echo json_encode([
            "minWater" => 50,
            "maxWater" => 50
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
