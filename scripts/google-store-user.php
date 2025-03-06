<?php
require '../app/config/connection.php';

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection is not established.");
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL); // Sanitize input

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 0) {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (email) VALUES (?)");
            $stmt->execute([$email]);

            echo json_encode(["success" => true, "message" => "User added"]);
        } else {
            echo json_encode(["success" => false, "message" => "User already exists"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
