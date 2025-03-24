<?php
session_start();
include '../app/config/connection.php';  // Include database connection

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Unauthorized access']));
}

try {
    // Use the existing connection from db_config.php
    $stmt = $conn->prepare("SELECT email, isEmailVerified FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('HTTP/1.1 404 Not Found');
        exit(json_encode(['error' => 'User not found']));
    }

    // Return verification status and email
    header('Content-Type: application/json');
    echo json_encode([
        'email' => $user['email'],
        'isVerified' => $user['isEmailVerified'] === 'yes'
    ]);

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Database error occurred']));
}