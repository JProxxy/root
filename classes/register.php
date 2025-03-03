<?php

// Include the database connection
require_once '../app/config/connection.php';

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
    exit; // Stop further execution
}

// Assuming you have a way to get the current logged-in user's ID, for example, using session or JWT
session_start();
$userId = $_SESSION['user_id']; // Replace with your method of getting the user ID

// Query to get the user's first name and last name
$query = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName);
$stmt->fetch();
$stmt->close();

// Check if the user data was found
if (!$firstName || !$lastName) {
    echo json_encode(['error' => 'User not found.']);
    exit;
}

// Get the file extension
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

// Generate the new filename using first name, last name, and profilePic suffix
$newFileName = strtolower($firstName . '_' . $lastName . '_profilePic.' . $fileExtension);
$filePath = $uploadDir . $newFileName; // Complete path for the file

// Check if the directory exists, create it if it doesn't
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if the file is uploaded successfully
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Send the file URL back to the front end
    echo json_encode(['url' => '/storage/user/profile_picture/' . $newFileName]);
} else {
    echo json_encode(['error' => 'Error uploading file.']);
}
?>
