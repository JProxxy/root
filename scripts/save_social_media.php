<?php
include '../app/config/connection.php';
session_start();

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$social_media = json_encode($data); // Convert to JSON format

try {
    $sql = "UPDATE users SET soc_med = :social_media WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":social_media", $social_media, PDO::PARAM_STR);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Social media links updated successfully"]);
    } else {
        echo json_encode(["error" => "Error updating links"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
