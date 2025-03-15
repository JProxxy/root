<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    header("Location: ../templates/forgot-password.php"); // Redirect if email is missing
    exit();
}

$email = $_SESSION['reset_email']; // Get email



    $email = $_SESSION['reset_email']; // Get email from session

    // Include the database connection
    require_once '../app/config/connection.php';

    // Check if password and retype_password are set in the POST request
    if (isset($_POST['password']) && isset($_POST['retype_password'])) {
        $password = $_POST['password']; // New password
        $retype_password = $_POST['retype_password']; // Retyped password

        // Check if passwords match
        if ($password !== $retype_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit();
        }

        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare SQL query to update the password for the user with the given email
            $sql = "UPDATE users SET password = :password WHERE email = :email";
            $stmt = $pdo->prepare($sql);

            // Bind the parameters to the SQL query
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                // Password successfully updated
                echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
            } else {
                // If the query fails
                echo json_encode(['success' => false, 'message' => 'Error updating password. Please try again.']);
            }
        } catch (PDOException $e) {
            // Handle any database connection errors
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        // If password or retype_password is not set
        echo json_encode(['success' => false, 'message' => 'Password fields are missing.']);
    }
    ?>
