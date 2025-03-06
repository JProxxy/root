<?php
session_start();
require_once '../app/config/connection.php';

try {
    if (!isset($conn)) {
        throw new Exception("Database connection error");
    }

    // Get form data
    $google_id = filter_var($_POST['google_id'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $first_name = filter_var($_POST['first_name'] ?? '', FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'] ?? '', FILTER_SANITIZE_STRING);
    $profile_picture = filter_var($_POST['profile_picture'] ?? '', FILTER_SANITIZE_URL);

    // Validate essential fields
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($google_id)) {
        throw new Exception("Invalid authentication data");
    }

    // Check existing user
    $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->execute([$google_id, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users 
            (google_id, email, username, first_name, last_name, profile_picture)
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $username = explode('@', $email)[0];  // Generate username from email
        $stmt->execute([
            $google_id,
            $email,
            $username,
            $first_name,
            $last_name,
            $profile_picture
        ]);
        $user_id = $conn->lastInsertId();
    } else {
        $user_id = $user['user_id'];
        // Update existing record if needed
        if (empty($user['google_id'])) {
            $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
            $stmt->execute([$google_id, $user_id]);
        }
    }

    // Set session data
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['google_id'] = $google_id;
    $_SESSION['logged_in'] = true;

    // Redirect to dashboard
    header('Location: ../templates/dashboard.php');
    exit();

} catch (Exception $e) {
    error_log("Google Auth Error: " . $e->getMessage());
    header('Location: ../templates/login.php?error=' . urlencode($e->getMessage()));
    exit();
}