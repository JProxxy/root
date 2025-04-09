<?php
// Include database connection
include '../app/config/connection.php';  

// Start the session
session_start();

// Check if user_id exists in session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Prepare the query to get the user's password from the database
$sql = "SELECT password FROM users WHERE user_id = :user_id"; // Change 'users' to your actual table name if needed
$stmt = $conn->prepare($sql);

// Bind the user_id parameter
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

// Execute the query
$stmt->execute();

// Fetch the result
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$adminPassword = $user['password']; // The actual password fetched from the database

// Get the input data (password and files)
$input = json_decode(file_get_contents('php://input'), true);

// Check if the entered password matches the stored password
if (!password_verify($input['password'], $adminPassword)) { // Use password_verify for hashed passwords
    echo json_encode(['success' => false, 'message' => 'Invalid password']);
    exit;
}

// Initialize file deletion
$deleted = false;
foreach ($input['files'] as $file) {
    $filePath = __DIR__ . '/../storage/user/deleted_userAccounts/' . $file;
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file
        $deleted = true;
    }
}

// Return the result of the deletion
echo json_encode(['success' => $deleted, 'message' => $deleted ? 'Files deleted successfully' : 'File(s) not found']);
?>
