<?php
session_start();

// Define route mappings with hashed folder names
$routes = [
    hash('sha256', 'login') => 'templates/login.php',
    hash('sha256', 'dashboard') => 'templates/dashboard.php',
];

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=" . array_search('templates/login.php', $routes));
    exit();
}

// If no specific page is requested, redirect to the dashboard
if (!isset($_GET['page'])) {
    header("Location: index.php?page=" . array_search('templates/dashboard.php', $routes));
    exit();
}

// Get the requested page and serve the correct file
$page = $_GET['page'] ?? '';

if (isset($routes[$page])) {
    include $routes[$page];
} else {
    echo "404 Not Found";
}
?>
