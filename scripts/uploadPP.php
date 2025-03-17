<?php
include '../app/config/connection.php';

// Define the directory for storing profile pictures
$uploadDir = '../storage/user/profile_picture/';

// Allowed MIME types for images
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];

// Get the uploaded file details
$file = $_FILES['file'];

// Check if the file is an image by checking its MIME type
$fileMimeType = mime_content_type($file['tmp_name']);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    echo json_encode(['error' => 'Only JPG, PNG, and JPEG images are allowed.']);
    exit;
}

// Start session
session_start();

// Check if user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Retrieve the user's email from the session, or fetch it from the database if not set
if (!isset($_SESSION['email'])) {
    // Fetch email from the database using the user_id (using PDO)
    $stmtEmail = $conn->prepare("SELECT email FROM users WHERE user_id = :user_id");
    $stmtEmail->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmtEmail->execute();
    $rowEmail = $stmtEmail->fetch(PDO::FETCH_ASSOC);
    if ($rowEmail && isset($rowEmail['email'])) {
        $email = $rowEmail['email'];
        $_SESSION['email'] = $email; // store it in session for future use
    } else {
        echo json_encode(['error' => 'User email not found.']);
        exit;
    }
    $stmtEmail = null;
} else {
    $email = $_SESSION['email'];
}

// Get the first three letters of the email (lowercase)
$emailPrefix = strtolower(substr($email, 0, 3));

// Get the file extension (lowercase)
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Generate the new filename using user_id and first 3 letters of email
$newFileName = $userId . '-' . $emailPrefix . '.' . $fileExtension;

// Define the file path
$filePath = $uploadDir . $newFileName;

// Create the directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    error_log('Failed to move uploaded file: ' . $file['tmp_name'] . ' to ' . $filePath);
    echo json_encode(['error' => 'Error moving file.']);
    exit;
}

// Move the uploaded file
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Determine the URL for the new profile picture
    $profilePicUrl = '../storage/user/profile_picture/' . $newFileName;
    
    // Fetch the current profile picture URL from the database
    $stmtSelect = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = :user_id");
    $stmtSelect->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmtSelect->execute();
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);
    $stmtSelect = null;
    
    // Delete the old file only if it's different from the new file URL
    if ($row && !empty($row['profile_picture']) && $row['profile_picture'] !== $profilePicUrl) {
        $oldFilePath = $row['profile_picture'];
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }
    
    // Update the user's profile_picture column in the database using PDO
    $stmtUpdate = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id");
    $stmtUpdate->bindValue(':profile_picture', $profilePicUrl, PDO::PARAM_STR);
    $stmtUpdate->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmtUpdate->execute();
    
    echo json_encode(['url' => $profilePicUrl]);

} else {
    echo json_encode(['error' => 'Error uploading file.']);
}

$conn = null;
?>
