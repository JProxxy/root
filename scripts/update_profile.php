<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

require_once '../app/config/connection.php';

$targetDir = "../uploads/"; // Change this to your actual upload directory
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0775, true); // Ensure directory exists
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png'];

    if (!in_array(strtolower($fileType), $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    $newFileName = uniqid('profile_', true) . '.' . $fileType;
    $filePath = $targetDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Save the new filename to the database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id");
        $stmt->execute([
            ':profile_picture' => $newFileName,
            ':user_id' => $_SESSION['user_id']
        ]);

        echo json_encode(['success' => true, 'url' => "/uploads/$newFileName"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error moving uploaded file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or file error']);
}
?>
