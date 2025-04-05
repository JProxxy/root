<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  // Load PHPMailer
require_once '../app/config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminEmail = trim($_POST['adminEmailVer']);

    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Check if email exists in users table and has role_id = 1
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role_id = 1");
    $stmt->bindParam(':email', $adminEmail, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch one row

    if ($result) {
        // Send Email with Confirmation Link
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'jpenarubia.a0001@rivaniot.online'; // Your email
            $mail->Password = 'ExcelAltH0103!'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jpenarubia.a0001@rivaniot.online', 'Admin Verification');
            $mail->addAddress($adminEmail);

            $confirmationLink = "http://localhost/root/templates/login.php";
            $mail->isHTML(true);
            $mail->Subject = "Admin Verification";
            $mail->Body = "
            Thank you for requesting admin access! <br> 
            Please <a href='$confirmationLink'>click here</a> to go to admin access. <br><br>
            If you didn't request this verification, please disregard this email. If you encounter any issues, feel free to reach out to our support team for assistance. We take your security seriously and are here to help every step of the way.
        ";


            $mail->send();
            echo json_encode(["status" => "success", "message" => "We've sent a confirmation to your email. Please check your inbox to confirm."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Your email isn’t registered for admin access. Please use the correct admin email."]);
    }

    $stmt->closeCursor(); // Use closeCursor() instead of close() for PDO
    $conn = null; // Close connection
}
?>