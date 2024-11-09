<?php
// Start session to manage user login status
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: templates/login.php");
    exit();
}

// If logged in, redirect to the dashboard (or main app page)
header("Location: templates/dashboard.php");
exit();
?>
