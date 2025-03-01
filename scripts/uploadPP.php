<?php
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

// Assuming you have a way to get the current logged-in user's ID, for example, using session
session_start();

// Check if user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// You can retrieve first name and last name from the session or any other way if required, but for now, using a placeholder
$firstName = "User";  // Replace with actual first name retrieval logic
$lastName = "Name";   // Replace with actual last name retrieval logic

// Get the file extension
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

// Generate the new filename using first name, last name, and profilePic suffix
$newFileName = strtolower($firstName . '_' . $lastName . '_profilePic.' . $fileExtension);

// Define the file path
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


