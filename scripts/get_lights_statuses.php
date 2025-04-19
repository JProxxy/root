<?php
require_once '../app/config/connection.php';

try {
    $stmt = $conn->prepare("SELECT device_name, status FROM Devices");
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'devices' => $devices
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
