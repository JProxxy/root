<?php
session_start();

// Enable error display for debugging. Disable these in production.
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../app/config/connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Retrieve JSON data from the request body
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];

// Retrieve the user's current hashed password from the database using $conn
$stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Verify the current password matches
if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit;
}

// Hash the new password using PHP's built-in hashing
$newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password in the database using $conn
$updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
if ($updateStmt->execute([$newHashedPassword, $userId])) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating password']);
}
exit;
?>
