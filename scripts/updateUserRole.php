<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit();
}

// Retrieve and decode the JSON payload.
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON data."]);
    exit();
}

// Check for required parameters.
if (!isset($data['username']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing username or role parameter."]);
    exit();
}

$username  = $data['username'];
$roleString = $data['role'];

// Create a mapping of role names to role_id
$roleMapping = [
    "super_admin"        => 1,
    "first_floor_admin"  => 2,
    "second_floor_admin" => 3,
    "third_floor_admin"  => 4,
    "fourth_floor_admin" => 5,
    "fifth_floor_admin"  => 6,
    "general_user"       => 7,
    "guest_user"         => 8,
    "maintenance_staff"  => 9,
    "security_admin"     => 10,
    "iot_technician"     => 11,
    "pending_user"       => 12,
    "blocked_user"       => 13
];

// Check if the provided role exists in the mapping
if (!isset($roleMapping[$roleString])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid role provided."]);
    exit();
}

$newRoleId = $roleMapping[$roleString];

// Prepare the query to update the user's role_id using username
$sql = "UPDATE users SET role_id = :role_id WHERE username = :username";
$stmt = $conn->prepare($sql);

try {
    // Execute the query with new parameters
    $stmt->execute([
        'role_id'  => $newRoleId,
        'username' => $username
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "User role updated successfully."
            
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No update performed. Check if the username is valid or if the new role is different."
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to update role.",
        "debug" => $e->getMessage()
    ]);
}
?>
