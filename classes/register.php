<?php
// Include the database connection
require_once '../app/config/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];

    // Check if passwords match
    if ($password !== $retype_password) {
        $errorMessage = "Passwords do not match.";
    } else {
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->fetch()) {
                $errorMessage = "Username already taken.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Define a default role ID (e.g., 12 = pending_user)
                $defaultRoleId = 12;

                // Insert new user into the database with the correct column name and default role ID
                $stmt = $conn->prepare("INSERT INTO users (username, email, phoneNumber, password, role_id) VALUES (:username, :email, :phoneNumber, :password, :role_id)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phoneNumber', $phoneNumber); // Corrected column name
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role_id', $defaultRoleId);

                if ($stmt->execute()) {
                    // Display success message and redirect
                    echo "<p>Sign up is good! Redirecting to login...</p>";
                    header("refresh:2; url=../templates/login.php");
                    exit();
                } else {
                    $errorMessage = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            // For debugging: display the actual error message
            $errorMessage = "Database error: " . $e->getMessage();
        }
    }
}

if (isset($errorMessage)) {
    echo "<p style='color: red;'>$errorMessage</p>";
}
?>
