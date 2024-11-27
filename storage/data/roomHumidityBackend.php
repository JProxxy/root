<?php
// Database connection parameters
$host = '18.139.255.32';
$dbname = 'rivan_iot';
$username = 'root';
$password = 'Pa$$word1';

// Create MySQL connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection error
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Query to fetch the latest humidity value
$query = "SELECT humidity FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($query);

// Check if there is any data returned
if ($result->num_rows > 0) {
    // Fetch the row data and get the humidity value
    $row = $result->fetch_assoc();
    $humidity = $row['humidity'];
    // Return the humidity value as a JSON response
    echo json_encode(["humidity" => $humidity]);
} else {
    // If no data found, return a default value of 0
    echo json_encode(["humidity" => 0]);
}

// Close the database connection
$conn->close();
?>
