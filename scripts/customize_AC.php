<?php
include '../app/config/connection.php';
header('Content-Type: application/json');

$deviceName = 'ffRoom-temp'; // CHANGE THIS TO MATCH YOUR DEVICE NAME

try {
    // GET THE LATEST TEMPERATURE FROM THE `room_data` TABLE
    $stmt = $conn->prepare("SELECT temperature FROM room_data WHERE deviceName = :deviceName ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute([':deviceName' => $deviceName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && isset($row['temperature'])) {
        echo json_encode(["temperature" => $row['temperature']]);
    } else {
        // DEFAULT VALUE IF NO DATA IS FOUND
        echo json_encode(["temperature" => 25]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
