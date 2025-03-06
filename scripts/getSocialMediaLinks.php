<?php
header('Content-Type: application/json');
include '../app/config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated"]);
    exit;
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Prepare and execute the query using PDO syntax
$stmt = $conn->prepare("SELECT soc_med FROM users WHERE user_id = ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $soc_med = json_decode($row['soc_med'], true); // Decode JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format in database"]);
    } else {
        echo json_encode($soc_med); // Return decoded JSON as response
    }
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt = null;
$conn = null;
?>
