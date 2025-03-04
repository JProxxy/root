<?php
session_start();

// Get the current domain dynamically
$baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/templates/";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    header("Location: " . $baseURL . "login");
    exit();
}

// Redirect to dashboard if logged in
header("Location: " . $baseURL . "dashboard");
exit();
?>
