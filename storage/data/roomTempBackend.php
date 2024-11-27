<?php
$host = '18.139.255.32';
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest temperature for the device 'ffRoom-temp'
    $query = "SELECT temperature FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Get the result
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $temperature = $row['temperature'];
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
