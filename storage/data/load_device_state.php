<?php
// load_device_state.php

// Database connection details
$host = '18.139.255.32';  // AWS DB IP (replace with your actual host)
$dbname = 'rivan_iot';     // Database name
$username = 'root';        // Your DB username
$password = 'Pa$$word1';   // Your DB password

header('Content-Type: application/json'); // Tell the browser to expect JSON response

try {
    // Create a PDO connection to the MySQL database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8"); // Set character encoding to utf8

    // Fetch device states from the Devices table where device_name starts with 'FF'
    $stmt = $conn->prepare("SELECT device_name, status FROM Devices WHERE device_name LIKE 'FF%'");
    $stmt->execute();
    
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch the results into an associative array

    // Prepare an array to store the device states
    $deviceStates = [];
    foreach ($devices as $device) {
        // Set device state as true for ON and false for OFF
        $deviceStates[$device['device_name']] = ($device['status'] === 'ON');
    }

    // Return the device states as a JSON object
    echo json_encode($deviceStates);

} catch (PDOException $e) {
    // In case of an error, return a JSON response with the error message
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}
?>
