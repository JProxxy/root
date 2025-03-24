<?php
ini_set('display_errors', 1);
error_reporting(E_ALL); // Enable all errors for debugging

// Start the session
session_start();

// Ensure the user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

// Include the database connection
require_once '../app/config/connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Define the query to fetch user data where role equals 12
$sql = "SELECT username, email, phoneNumber, role_id FROM users WHERE role_id = 12";
$stmt = $conn->prepare($sql);

if (!$stmt->execute()) {
    // Capture PDO error info for debugging
    $errorInfo = $stmt->errorInfo();
    echo json_encode([
        "error" => "Database query failed.",
        "debug" => $errorInfo
    ]);
    exit();
}

// Fetch all results as an associative array
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle null values by replacing them with an empty string
foreach ($users as $i => $user) {
    foreach ($user as $key => $value) {
        if (is_null($value)) {
            $users[$i][$key] = "";
        }
    }
}

// Prepare debug info
$debugInfo = [
    "query" => $sql,
    "num_rows" => count($users),
    "data_sample" => count($users) > 0 ? $users[0] : "No data returned"
];

// Output the data as JSON with debug information
echo json_encode([
    "debug" => $debugInfo,
    "data"  => $users
]);
?>
