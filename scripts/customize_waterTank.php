<?php
header('Content-Type: text/plain');

include '../app/config/connection.php';

try {
    $stmt = $conn->query("SELECT WaterPercentage FROM water_tank ORDER BY timestamp DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo (int)($row['WaterPercentage'] ?? 0);
    exit;
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo 0;
    exit;
}
?>