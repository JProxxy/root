<?php
session_start();

// Define route mappings with hashed folder names
$routes = [
    hash('sha256', 'login') => 'templates/login.php',
    hash('sha256', 'dashboard') => 'templates/dashboard.php',
];

// Get the requested page from URL, or default to an empty string
$page = $_GET['page'] ?? '';

// If user is not logged in, redirect to the login page only if not already there
if (!isset($_SESSION['user_id'])) {
    $loginHash = array_search('templates/login.php', $routes);
    if ($page !== $loginHash) {
        header("Location: index.php?page=" . $loginHash);
        exit();
    }
} else {
    // If logged in, redirect to the dashboard only if not already there
    $dashboardHash = array_search('templates/dashboard.php', $routes);
    if ($page === '' || $page !== $dashboardHash) {
        header("Location: index.php?page=" . $dashboardHash);
        exit();
    }
}

// Serve the requested page if it exists
if (isset($routes[$page])) {
    include $routes[$page];
} else {
    echo "404 Not Found";
}
?>
