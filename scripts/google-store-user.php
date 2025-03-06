<?php
require_once '../app/config/connection.php';

try {
    if (!isset($conn)) {
        throw new Exception("Database connection is not established.");
    }

    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract and sanitize inputs
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $first_name = filter_var($data['first_name'] ?? '', FILTER_SANITIZE_STRING);
    $last_name = filter_var($data['last_name'] ?? '', FILTER_SANITIZE_STRING);
    $profile_picture = filter_var($data['profile_picture'] ?? '', FILTER_SANITIZE_URL);
    $sub = filter_var($data['sub'] ?? '', FILTER_SANITIZE_STRING);
    $locale = filter_var($data['locale'] ?? '', FILTER_SANITIZE_STRING);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 0) {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email,  first_name, last_name, profile_picture, google_id, locale) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt->execute([$email, $first_name, $last_name, $profile_picture, $sub, $locale])) {
                die(json_encode(["success" => false, "message" => "Insert failed", "error" => $stmt->errorInfo()]));
            }

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
