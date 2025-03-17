<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../templates/login.php");
    exit();
}

// Set response headers for JSON output
header("Content-Type: application/json");

// Static sample data (instead of fetching from a database)
$userActivityLogs = [
    [
        "id" => 1,
        "name" => "John Doe",
        "profile_picture" => "../assets/images/defaultProfile.png",
        "action" => "Logged In",
        "timestamp" => "2025-03-18 10:05 AM",
        "status" => "authorized"
    ],
    [
        "id" => 2,
        "name" => "Jane Smith",
        "profile_picture" => "../assets/images/defaultProfile.png",
        "action" => "Updated Profile",
        "timestamp" => "2025-03-18 10:15 AM",
        "status" => "authorized"
    ],
    [
        "id" => 3,
        "name" => "Michael Lee",
        "profile_picture" => "../assets/images/defaultProfile.png",
        "action" => "Logged Out",
        "timestamp" => "2025-03-18 10:20 AM",
        "status" => "unauthorized"
    ]
];

// Return JSON response
echo json_encode($userActivityLogs);
?>
