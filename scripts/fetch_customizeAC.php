<?php
include '../app/config/connection.php';
header('Content-Type: application/json');

try {
    // Fetch the single row of AC settings.
    $stmt = $conn->prepare("SELECT minTemp, maxTemp FROM customizeAC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo json_encode($row);
    } else {
        // Return default values if no record is found.
        echo json_encode([
            "minTemp" => 28,  // Example default minimum temperature
            "maxTemp" => 35   // Example default maximum temperature
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
