<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../app/config/connection.php';

// Check if $conn is a valid PDO connection
if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

try {
    // Prepare and execute query
    $sql = "SELECT user_id, username, CONCAT(first_name, ' ', last_name) AS full_name, bio, phoneNumber, soc_med, profile_picture FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch members as an associative array
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure JSON response format
    header('Content-Type: application/json');
    echo json_encode($members);
} catch (PDOException $e) {
    // Handle query errors (Fixed the syntax error)
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}
?>
