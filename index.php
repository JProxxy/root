<?php
// Start session to manage user login status
session_start();

// Function to generate a hashed URL
function hashed_url($file) {
    return $file . "?v=" . hash("sha256", $file);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page with a hashed URL
    header("Location: " . hashed_url("templates/login.php"));
    exit();
}

// If logged in, redirect to the dashboard (or main app page) with a hashed URL
header("Location: " . hashed_url("templates/dashboard.php"));
exit();
?>
