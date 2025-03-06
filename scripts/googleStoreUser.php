<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once '../app/config/connection.php';

try {
    if (!isset($conn)) {
        throw new Exception("Database connection is not established.");
    }

    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract and sanitize inputs
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $first_name = filter_var($data['first_name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $last_name = filter_var($data['last_name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $profile_picture = filter_var($data['profile_picture'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 0) {
            // Get the highest user_id
            $stmt = $conn->query("SELECT MAX(user_id) AS max_user_id FROM users");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_user_id = ($row['max_user_id'] ?? 0) + 1; // If NULL, start from 1
            $next_google_id = $next_user_id; // Make google_id the same as user_id
            $username = $email; // Set username as email
            $password = ""; // Empty password

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (user_id, google_id, email, username, password, first_name, last_name, profile_picture) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt->execute([$next_user_id, $next_google_id, $email, $username, $password, $first_name, $last_name, $profile_picture])) {
                throw new Exception("Insert failed: " . json_encode($stmt->errorInfo()));
            }

            echo json_encode(["success" => true, "message" => "User added", "user_id" => $next_user_id, "google_id" => $next_google_id]);
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