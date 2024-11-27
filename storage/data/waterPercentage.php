<?php
$host = '18.139.255.32';
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

// Create a new PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch the latest WaterPercentage from the water_tank table
    $stmt = $pdo->query("SELECT WaterPercentage FROM water_tank ORDER BY timestamp DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the result is not empty and output the WaterPercentage
    if ($result) {
        echo json_encode(["WaterPercentage" => $result['WaterPercentage']]);
    } else {
        echo json_encode(["WaterPercentage" => 0]); // If no data found, return 0
    }
    
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
}
?>
